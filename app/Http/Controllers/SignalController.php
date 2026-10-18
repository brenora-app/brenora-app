<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Signal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SignalController extends Controller
{
    public function create(Account $account): View
    {
        $this->authorize('update', $account);

        return view('signals.form', ['account' => $account, 'signal' => new Signal]);
    }

    public function store(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $signal = $account->signals()->create($this->validatedData($request));
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'signal_added',
            'summary' => 'Customer signal added: '.$signal->type.' from '.$signal->source.'.',
            'details' => ['signal_id' => $signal->id, 'recorded_at' => $signal->recorded_at->toIso8601String()],
        ]);

        return redirect()->route('accounts.show', $account)->with('status', 'Signal added.');
    }

    public function edit(Account $account, Signal $signal): View
    {
        $this->authorizeSignal($account, $signal);

        return view('signals.form', compact('account', 'signal'));
    }

    public function update(Request $request, Account $account, Signal $signal): RedirectResponse
    {
        $this->authorizeSignal($account, $signal, true);
        $signal->update($this->validatedData($request));
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'signal_updated',
            'summary' => 'Customer signal updated: '.$signal->type.' from '.$signal->source.'.',
            'details' => ['signal_id' => $signal->id],
        ]);

        return redirect()->route('accounts.show', $account)->with('status', 'Signal updated.');
    }

    public function destroy(Account $account, Signal $signal): RedirectResponse
    {
        $this->authorizeSignal($account, $signal, true);
        $account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'signal_removed',
            'summary' => 'Customer signal removed: '.$signal->type.' from '.$signal->source.'.',
            'details' => ['signal_id' => $signal->id],
        ]);
        $signal->delete();

        return redirect()->route('accounts.show', $account)->with('status', 'Signal deleted.');
    }

    private function authorizeSignal(Account $account, Signal $signal, bool $write = false): void
    {
        abort_unless($signal->account_id === $account->id, 404);
        $this->authorize($write ? 'update' : 'view', $account);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'max:80'],
            'source' => ['required', 'string', 'max:160'],
            'score' => ['required', 'integer', 'between:0,100'],
            'recorded_at' => ['required', 'date'],
            'summary' => ['required', 'string', 'max:5000'],
        ]);
    }
}
