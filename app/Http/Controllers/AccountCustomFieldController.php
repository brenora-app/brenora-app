<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountCustomField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountCustomFieldController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManagement($request);

        return view('custom-fields.index', [
            'fields' => AccountCustomField::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManagement($request);
        $data = $this->validatedData($request);
        $key = Str::limit(Str::slug($data['name'], '_'), 80, '');

        if ($key === '') {
            return back()->withErrors(['name' => 'Use at least one letter or number in the field name.'])->withInput();
        }

        validator(['key' => $key], [
            'key' => ['required', Rule::unique('account_custom_fields', 'key')],
        ], ['key.unique' => 'A field with this name already exists.'])->validate();

        AccountCustomField::create([
            ...$data,
            'key' => $key,
            'sort_order' => (int) AccountCustomField::max('sort_order') + 1,
        ]);

        return back()->with('status', 'Custom field added to account records.');
    }

    public function update(Request $request, AccountCustomField $customField): RedirectResponse
    {
        $this->authorizeManagement($request);
        $customField->update($this->validatedData($request, $customField));

        return back()->with('status', 'Custom field updated.');
    }

    public function destroy(Request $request, AccountCustomField $customField): RedirectResponse
    {
        $this->authorizeManagement($request);

        DB::transaction(function () use ($customField): void {
            Account::query()
                ->whereNotNull('custom_fields')
                ->orderBy('id')
                ->chunkById(100, function ($accounts) use ($customField): void {
                    foreach ($accounts as $account) {
                        $values = $account->custom_fields ?? [];
                        unset($values[$customField->key]);
                        $account->custom_fields = $values;
                        $account->save();
                    }
                });

            $customField->delete();
        });

        return back()->with('status', 'Custom field and its saved account values were removed.');
    }

    private function authorizeManagement(Request $request): void
    {
        abort_unless($request->user()->canManageAccounts(), 403);
    }

    private function validatedData(Request $request, ?AccountCustomField $customField = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'type' => ['required', Rule::in(['text', 'number', 'date', 'select', 'checkbox'])],
            'options_text' => ['nullable', 'string', 'max:2000', Rule::requiredIf($request->input('type') === 'select')],
            'required' => ['nullable', 'boolean'],
        ]);

        $options = collect(preg_split('/\r\n|\r|\n/', $validated['options_text'] ?? '') ?: [])
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($validated['type'] === 'select' && count($options) < 2) {
            throw ValidationException::withMessages([
                'options_text' => 'A dropdown needs at least two distinct options.',
            ]);
        }

        return [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'options' => $validated['type'] === 'select' ? $options : null,
            'required' => (bool) ($validated['required'] ?? false),
        ];
    }
}
