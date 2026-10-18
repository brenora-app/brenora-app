<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CommercialAction;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('admin.index', [
            'users' => User::query()->orderBy('name')->get(),
            'actions' => CommercialAction::with(['account', 'assignee', 'events.user'])->latest()->limit(30)->get(),
            'actionAssignees' => User::query()->orderBy('name')->get(['id', 'name', 'email']),
            'accounts' => Account::query()->orderBy('name')->get(['id', 'name']),
            'counts' => [
                'accounts' => Account::count(),
                'insights' => Insight::count(),
                'users' => User::count(),
            ],
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        abort_if($request->user()->is($user), 422, 'You cannot change your own role.');

        $validated = $request->validate([
            'role' => ['required', 'in:admin,analyst,contributor,viewer'],
        ]);
        $user->role = $validated['role'];
        $user->save();

        return back()->with('status', 'User role updated.');
    }

    public function storeAction(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'owner' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'playbook' => ['nullable', 'string', 'max:120'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'approval_required' => ['sometimes', 'boolean'],
        ]);
        $data['created_by'] = $request->user()->id;
        $data['status'] = 'open';

        DB::transaction(function () use ($data, $request): void {
            $action = CommercialAction::create($data);
            $action->events()->create([
                'user_id' => $request->user()->id,
                'to_status' => 'open',
                'note' => 'Action created.',
            ]);
            $action->account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => 'action_created',
                'summary' => 'Commercial action created: '.$action->title.'.',
                'details' => ['action_id' => $action->id, 'playbook' => $action->playbook],
            ]);
        });

        return back()->with('status', 'Commercial action created.');
    }

    public function updateAction(Request $request, CommercialAction $action): RedirectResponse
    {
        abort_unless($request->user()->isAdmin(), 403);
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,pending_approval,completed,blocked,cancelled'],
            'note' => ['nullable', 'string', 'max:2000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'owner' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $transitions = [
            'open' => ['in_progress', 'pending_approval', 'blocked', 'cancelled'],
            'in_progress' => ['pending_approval', 'completed', 'blocked', 'cancelled'],
            'pending_approval' => ['in_progress', 'completed', 'cancelled'],
            'blocked' => ['open', 'in_progress', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];

        if ($data['status'] !== $action->status && ! in_array($data['status'], $transitions[$action->status] ?? [], true)) {
            return back()->withErrors(['status' => 'That action status transition is not allowed.']);
        }

        if ($action->approval_required && $data['status'] === 'completed' && $action->status !== 'pending_approval') {
            return back()->withErrors(['status' => 'This action must enter approval review before completion.']);
        }
        if ($data['status'] === 'completed' && trim($data['outcome'] ?? $action->outcome ?? '') === '') {
            return back()->withErrors(['outcome' => 'Record the action outcome before marking this action complete.']);
        }

        DB::transaction(function () use ($action, $data, $request): void {
            $previousStatus = $action->status;
            $update = [
                'status' => $data['status'],
                'outcome' => $data['outcome'] ?? $action->outcome,
                'completed_at' => $data['status'] === 'completed' ? ($action->completed_at ?? now()) : null,
            ];
            foreach (['assigned_to', 'owner', 'due_date'] as $field) {
                if (array_key_exists($field, $data)) {
                    $update[$field] = $data[$field];
                }
            }
            $action->update($update);
            $action->events()->create([
                'user_id' => $request->user()->id,
                'from_status' => $previousStatus,
                'to_status' => $data['status'],
                'note' => $data['note'] ?? null,
            ]);
            $action->account->activity()->create([
                'user_id' => $request->user()->id,
                'event_type' => in_array($data['status'], ['blocked', 'pending_approval'], true) ? 'action_escalated' : 'action_status_changed',
                'summary' => 'Action “'.$action->title.'” moved from '.str_replace('_', ' ', $previousStatus).' to '.str_replace('_', ' ', $data['status']).'.',
                'details' => ['action_id' => $action->id, 'note' => $data['note'] ?? null],
            ]);
        });

        return back()->with('status', 'Action status updated.');
    }

    public function reviewInsight(Request $request, Insight $insight): RedirectResponse
    {
        abort_unless($request->user()->canReviewInsights(), 403);
        $validated = $request->validate([
            'status' => ['required', 'in:reviewed,rejected'],
            'reviewer_correction' => ['nullable', 'string', 'max:5000'],
        ]);
        $insight->update($validated + [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        $insight->account->activity()->create([
            'user_id' => $request->user()->id,
            'event_type' => 'insight_reviewed',
            'summary' => 'Insight “'.$insight->title.'” was '.$insight->status.'.',
            'details' => ['insight_id' => $insight->id, 'has_correction' => filled($validated['reviewer_correction'] ?? null)],
        ]);

        return back()->with('status', 'Insight review saved.');
    }
}
