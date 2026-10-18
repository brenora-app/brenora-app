<?php

namespace App\Http\Controllers;

use App\Models\ContactSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContactSubmissionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:254'],
            'company' => ['nullable', 'string', 'max:160'],
            'topic' => ['required', 'in:product,pricing,partnership,enterprise,other'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        ContactSubmission::create($validated);

        return back()->with('contact_submitted', true);
    }
}
