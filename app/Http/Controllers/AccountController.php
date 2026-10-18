<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountCustomField;
use App\Models\CommercialAction;
use App\Models\Insight;
use App\Models\Signal;
use App\Services\Ai\OllamaInsightGenerator;
use App\Services\Ai\OllamaAccountBriefing;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use JsonException;
use UnexpectedValueException;

class AccountController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Account::class);

        $filters = request()->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['needs_review', 'healthy', 'expanding', 'at_risk'])],
            'segment' => ['nullable', 'string', 'max:80'],
            'stage' => ['nullable', Rule::in(['discovery', 'validation', 'proposal', 'commit', 'won', 'lost'])],
            'engagement' => ['nullable', Rule::in(['unknown', 'active', 'low_engagement', 'disengaged'])],
            'health' => ['nullable', Rule::in(['assessed', 'unassessed'])],
            'health_min' => ['nullable', 'integer', 'between:0,100'],
            'health_max' => ['nullable', 'integer', 'between:0,100'],
            'signal_type' => ['nullable', 'string', 'max:48'],
            'playbook' => ['nullable', 'string', 'max:120'],
            'sort' => ['nullable', Rule::in(['name', 'renewal_date', 'annual_revenue', 'health_score', 'updated_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ]);
        $accounts = Account::query()
            ->withCount(['signals', 'contacts'])
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('industry', 'like', "%{$search}%")
                ->orWhere('owner', 'like', "%{$search}%")
                ->orWhereHas('contacts', fn ($related) => $related->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('signals', fn ($related) => $related->where('summary', 'like', "%{$search}%")->orWhere('source', 'like', "%{$search}%"))
                ->orWhereHas('actions', fn ($related) => $related->where('title', 'like', "%{$search}%")->orWhere('playbook', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['segment'] ?? null, fn ($query, $segment) => $query->where('segment', $segment))
            ->when($filters['stage'] ?? null, fn ($query, $stage) => $query->where('opportunity_stage', $stage))
            ->when($filters['engagement'] ?? null, fn ($query, $engagement) => $query->where('engagement_status', $engagement))
            ->when($filters['health'] ?? null, fn ($query, $health) => $health === 'unassessed'
                ? $query->whereNull('health_score')
                : $query->whereNotNull('health_score'))
            ->when($filters['health_min'] ?? null, fn ($query, $score) => $query->where('health_score', '>=', $score))
            ->when($filters['health_max'] ?? null, fn ($query, $score) => $query->where('health_score', '<=', $score))
            ->when($filters['signal_type'] ?? null, fn ($query, $type) => $query->whereHas('signals', fn ($related) => $related->where('type', $type)))
            ->when($filters['playbook'] ?? null, fn ($query, $playbook) => $query->whereHas('actions', fn ($related) => $related->where('playbook', $playbook)))
            ->orderBy($filters['sort'] ?? 'name', $filters['direction'] ?? 'asc')
            ->paginate(12)
            ->withQueryString();
        $segments = Account::query()->select('segment')->distinct()->orderBy('segment')->pluck('segment');
        $signalTypes = Signal::query()->select('type')->distinct()->orderBy('type')->pluck('type');
        $playbooks = CommercialAction::query()->whereNotNull('playbook')->select('playbook')->distinct()->orderBy('playbook')->pluck('playbook');

        return view('accounts.index', compact('accounts', 'segments', 'signalTypes', 'playbooks', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Account::class);

        return view('accounts.form', [
            'account' => new Account,
            'customFields' => AccountCustomField::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Account::class);
        $data = $this->validatedData($request);
        $data['consent_updated_at'] = $data['consent_status'] === 'pending' ? null : now();
        $account = Account::create($data);
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'account_created',
            'summary' => 'Account record created.',
        ]);

        return redirect()->route('accounts.show', $account)->with('status', 'Account created.');
    }

    public function show(Account $account): View
    {
        $this->authorize('view', $account);
        $account->load([
            'contacts' => fn ($query) => $query->orderByDesc('is_primary')->orderBy('name'),
            'activity.user',
            'signals' => fn ($query) => $query->latest('recorded_at'),
            'insights' => fn ($query) => $query->with('reviewer')->latest(),
            'actions' => fn ($query) => $query->with(['events.user', 'assignee'])->latest(),
        ]);
        $customFieldLabels = AccountCustomField::query()->pluck('name', 'key');

        return view('accounts.show', compact('account', 'customFieldLabels'));
    }

    public function edit(Account $account): View
    {
        $this->authorize('update', $account);

        return view('accounts.form', [
            'account' => $account,
            'customFields' => AccountCustomField::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $before = $account->only(['forecast_revenue', 'expansion_value', 'opportunity_stage', 'status', 'renewal_date']);
        $data = $this->validatedData($request, $account);
        $data['consent_updated_at'] = ($data['consent_status'] ?? null) !== $account->consent_status
            ? now()
            : $account->consent_updated_at;
        $account->update($data);
        $after = $account->only(array_keys($before));
        $changes = [];
        foreach ($before as $key => $value) {
            $oldValue = $value instanceof \DateTimeInterface ? $value->format('Y-m-d') : $value;
            $newValue = $after[$key] instanceof \DateTimeInterface ? $after[$key]->format('Y-m-d') : $after[$key];
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$key] = ['from' => $oldValue, 'to' => $newValue];
            }
        }
        if ($changes !== []) {
            $account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'forecast_changed',
                'summary' => 'Commercial forecast or account status changed.',
                'details' => $changes,
            ]);
        }
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'account_updated',
            'summary' => 'Account context updated.',
        ]);

        return redirect()->route('accounts.show', $account)->with('status', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);
        $account->activity()->create([
            'user_id' => request()->user()->id,
            'event_type' => 'account_deleted',
            'summary' => 'An account record was deleted.',
            'details' => ['deleted_account_id' => $account->id],
        ]);
        $account->delete();

        return redirect()->route('accounts.index')->with('status', 'Account deleted.');
    }

    public function generateInsight(Request $request, Account $account, OllamaInsightGenerator $generator): RedirectResponse
    {
        abort_unless($request->user()->canReviewInsights(), 403);
        if ($account->consent_status !== 'granted') {
            return back()->withErrors(['ai' => 'Confirm account data processing consent before using AI features.']);
        }

        if (! $account->signals()->exists()) {
            return back()->withErrors(['ai' => 'Add at least one customer signal before generating an insight.']);
        }

        try {
            $result = $generator->generate($account);
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Local Ollama insight generation failed to connect', [
                'account_id' => $account->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['ai' => 'Could not reach the configured local Ollama service. Check that it is running and the selected model is available.']);
        } catch (JsonException|UnexpectedValueException $exception) {
            Log::warning('Local Ollama returned an invalid insight response', [
                'account_id' => $account->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['ai' => 'The local model response was invalid or lacked valid evidence citations. No insight was saved.']);
        }

        $insight = Insight::create([
            'account_id' => $account->id,
            'title' => $result['title'],
            'summary' => $result['summary'],
            'severity' => $account->status === 'at_risk' ? 'attention' : 'information',
            'status' => 'draft',
            'model_name' => (string) config('services.ollama.model'),
            'evidence' => array_map(fn (int $id) => 'Signal #'.$id, $result['evidence_ids']),
        ]);
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'insight_generated',
            'summary' => 'Evidence-linked insight draft generated: '.$insight->title.'.',
            'details' => ['insight_id' => $insight->id],
        ]);

        return redirect()->route('accounts.show', $account)->with('status', 'Evidence-linked insight draft generated. Review it before taking action.');
    }

    public function brief(Request $request, Account $account, OllamaAccountBriefing $briefing): RedirectResponse
    {
        abort_unless($request->user()->canReviewInsights(), 403);
        $validated = $request->validate([
            'question' => ['required', 'string', 'min:5', 'max:500'],
        ]);
        if ($account->consent_status !== 'granted') {
            return back()->withErrors(['briefing' => 'Confirm account data processing consent before using AI features.']);
        }

        try {
            $result = $briefing->answer($account, $validated['question']);
        } catch (ConnectionException|RequestException $exception) {
            Log::warning('Local account briefing failed to connect', [
                'account_id' => $account->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['briefing' => 'Could not reach the configured local Ollama service. Check that it is running and the selected model is available.']);
        } catch (JsonException|UnexpectedValueException $exception) {
            Log::warning('Local account briefing returned an invalid response', [
                'account_id' => $account->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['briefing' => 'The local model could not produce a valid answer with signal citations. No briefing was saved.']);
        }

        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'briefing_generated',
            'summary' => 'An evidence-linked account briefing was generated.',
            'details' => [
                'question' => $validated['question'],
                'answer' => $result['answer'],
                'signal_ids' => $result['signal_ids'],
            ],
        ]);

        return redirect()->route('accounts.show', $account)->with('briefing', [
            'question' => $validated['question'],
            'answer' => $result['answer'],
            'signal_ids' => $result['signal_ids'],
        ]);
    }

    private function validatedData(Request $request, ?Account $account = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:120'],
            'owner' => ['nullable', 'string', 'max:255'],
            'annual_revenue' => [Rule::requiredIf(fn () => $request->input('status') !== 'needs_review'), 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'health_score' => [Rule::requiredIf(fn () => $request->input('status') !== 'needs_review'), 'nullable', 'integer', 'between:0,100'],
            'status' => ['required', 'in:needs_review,healthy,expanding,at_risk'],
            'renewal_date' => ['nullable', 'date'],
            'subscription_plan' => ['nullable', 'string', 'max:120'],
            'contract_length_months' => ['nullable', 'integer', 'between:1,120'],
            'engagement_status' => ['sometimes', Rule::in(['active', 'low_engagement', 'disengaged', 'unknown'])],
            'segment' => ['sometimes', 'string', 'max:80'],
            'expansion_value' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'forecast_revenue' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'opportunity_stage' => ['sometimes', 'nullable', Rule::in(['discovery', 'validation', 'proposal', 'commit', 'won', 'lost'])],
            'consent_status' => ['sometimes', Rule::in(['pending', 'granted', 'revoked'])],
        ]);
        $data += [
            'engagement_status' => $account?->engagement_status ?? 'active',
            'segment' => $account?->segment ?? 'unclassified',
            'expansion_value' => $account?->expansion_value ?? 0,
            'opportunity_stage' => $account?->opportunity_stage ?? 'discovery',
            'consent_status' => $account?->consent_status ?? 'pending',
        ];

        $fields = AccountCustomField::query()->orderBy('sort_order')->get();
        $customRules = [
            'custom_fields' => ['nullable', 'array:'.$fields->pluck('key')->implode(',')],
        ];

        foreach ($fields as $field) {
            $required = $field->required ? 'required' : 'nullable';
            $customRules['custom_fields.'.$field->key] = match ($field->type) {
                'number' => [$required, 'numeric', 'min:-9999999999', 'max:9999999999'],
                'date' => [$required, 'date'],
                'select' => [$required, Rule::in($field->options ?? [])],
                'checkbox' => $field->required ? ['accepted'] : ['sometimes', 'boolean'],
                default => [$required, 'string', 'max:2000'],
            };
        }

        $customValues = $fields->isEmpty()
            ? []
            : ($request->validate($customRules)['custom_fields'] ?? []);

        foreach ($fields->where('type', 'checkbox') as $field) {
            $customValues[$field->key] = (bool) ($customValues[$field->key] ?? false);
        }

        $data['custom_fields'] = array_replace($account?->custom_fields ?? [], $customValues);

        return $data;
    }
}
