<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountContactController extends Controller
{
    public function store(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);
        $data = $this->validatedData($request, $account);

        DB::transaction(function () use ($account, $data, $request): void {
            if ($data['is_primary']) {
                $account->contacts()->update(['is_primary' => false]);
            }
            $contact = $account->contacts()->create($data + [
                'consent_updated_at' => $data['consent_status'] === 'pending' ? null : now(),
            ]);
            $account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'contact_added',
                'summary' => 'Contact added: '.$contact->name.'.',
                'details' => ['contact_id' => $contact->id, 'consent_status' => $contact->consent_status],
            ]);
        });

        return back()->with('status', 'Contact added to the account.');
    }

    public function update(Request $request, Account $account, AccountContact $contact): RedirectResponse
    {
        $this->authorizeContact($account, $contact);
        $data = $this->validatedData($request, $account);

        DB::transaction(function () use ($account, $contact, $data, $request): void {
            if ($data['is_primary']) {
                $account->contacts()->whereKeyNot($contact->id)->update(['is_primary' => false]);
            }
            $oldConsent = $contact->consent_status;
            $data['consent_updated_at'] = $oldConsent === $data['consent_status'] ? $contact->consent_updated_at : now();
            $contact->update($data);
            $account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => $oldConsent === $data['consent_status'] ? 'contact_updated' : 'consent_changed',
                'summary' => $oldConsent === $data['consent_status']
                    ? 'Contact details updated: '.$contact->name.'.'
                    : 'Contact consent changed to '.$data['consent_status'].' for '.$contact->name.'.',
                'details' => ['contact_id' => $contact->id, 'consent_status' => $data['consent_status']],
            ]);
        });

        return back()->with('status', 'Contact details saved.');
    }

    public function destroy(Request $request, Account $account, AccountContact $contact): RedirectResponse
    {
        $this->authorizeContact($account, $contact);

        DB::transaction(function () use ($account, $contact, $request): void {
            $account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'contact_removed',
                'summary' => 'Contact removed: '.$contact->name.'.',
                'details' => ['contact_id' => $contact->id],
            ]);
            $contact->delete();
        });

        return back()->with('status', 'Contact removed from the account.');
    }

    private function authorizeContact(Account $account, AccountContact $contact): void
    {
        abort_unless($contact->account_id === $account->id, 404);
        $this->authorize('update', $account);
    }

    private function validatedData(Request $request, Account $account): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'title' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:254', Rule::unique('account_contacts', 'email')->ignore($request->route('contact')?->id)->where('account_id', $account->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
            'consent_status' => ['required', Rule::in(['pending', 'granted', 'revoked'])],
        ]);

        $validated['is_primary'] = $request->boolean('is_primary');

        return $validated;
    }
}
