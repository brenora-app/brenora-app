@extends('layouts.console', ['title' => 'Administration'])

@section('content')
    <div class="mb-8">
        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Workspace management</p>
        <h1 class="mt-2 text-3xl font-bold text-white">Administration</h1>
    </div>
    <div class="mb-8 grid gap-4 md:grid-cols-3">
        @foreach (['users' => 'Users', 'accounts' => 'Accounts', 'insights' => 'Insight records'] as $key => $label)
            <div class="rounded-2xl border border-white/10 bg-slate-900 p-5"><p class="text-sm text-slate-400">{{ $label }}</p><p class="mt-2 text-3xl font-bold text-white">{{ $counts[$key] }}</p></div>
        @endforeach
    </div>

    <section class="mb-8 rounded-2xl border border-white/10 bg-slate-900 p-6">
        <h2 class="mb-5 text-xl font-semibold text-white">User roles</h2>
        <div class="divide-y divide-white/10">
            @foreach ($users as $user)
                <div class="flex flex-wrap items-center justify-between gap-4 py-4">
                    <div><p class="font-medium text-white">{{ $user->name }}</p><p class="text-sm text-slate-400">{{ $user->email }}</p></div>
                    @if (auth()->id() !== $user->id)
                        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex gap-2">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="role-{{ $user->id }}">Role for {{ $user->name }}</label>
                            <select id="role-{{ $user->id }}" name="role" class="rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-white">
                                @foreach (['admin', 'analyst', 'contributor', 'viewer'] as $role)
                                    <option value="{{ $role }}" @selected($user->role === $role)>{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-xl border border-white/15 px-4 py-2 text-sm text-white hover:border-cyan-400/50">Save role</button>
                        </form>
                    @else
                        <span class="text-sm capitalize text-cyan-200">{{ $user->role }} (you)</span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section class="mb-8 rounded-2xl border border-white/10 bg-slate-900 p-6">
        <h2 class="mb-5 text-xl font-semibold text-white">Create commercial action</h2>
        <form method="POST" action="{{ route('admin.actions.store') }}" class="grid gap-4 md:grid-cols-2">
            @csrf
            <div><label for="account_id" class="mb-2 block text-sm text-slate-200">Account</label><select name="account_id" id="account_id" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white">@foreach ($accounts as $account)<option value="{{ $account->id }}">{{ $account->name }}</option>@endforeach</select></div>
            <div><label for="title" class="mb-2 block text-sm text-slate-200">Action title</label><input name="title" id="title" required maxlength="255" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div><label for="owner" class="mb-2 block text-sm text-slate-200">Owner</label><input name="owner" id="owner" required maxlength="255" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div><label for="assigned_to" class="mb-2 block text-sm text-slate-200">Assign to team member</label><select name="assigned_to" id="assigned_to" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white"><option value="">No linked user</option>@foreach ($actionAssignees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->name }} · {{ $assignee->email }}</option>@endforeach</select></div>
            <div><label for="playbook" class="mb-2 block text-sm text-slate-200">Playbook</label><select name="playbook" id="playbook" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white"><option value="">General follow-up</option>@foreach (['Renewal readiness', 'At-risk recovery', 'Expansion discovery', 'Customer onboarding', 'Executive relationship review'] as $playbook)<option value="{{ $playbook }}">{{ $playbook }}</option>@endforeach</select></div>
            <div><label for="due_date" class="mb-2 block text-sm text-slate-200">Due date</label><input name="due_date" id="due_date" type="date" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div class="md:col-span-2"><label for="description" class="mb-2 block text-sm text-slate-200">Description</label><textarea name="description" id="description" rows="3" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></textarea></div>
            <label class="flex items-center gap-3 text-sm text-slate-300"><input type="checkbox" name="approval_required" value="1" class="rounded border-white/20 bg-slate-950 text-cyan-400"> Require approval before completion</label>
            <div class="md:col-span-2"><button class="rounded-full bg-cyan-400 px-5 py-3 font-semibold text-slate-950 hover:bg-cyan-300">Create action</button></div>
        </form>
    </section>

    <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
        <h2 class="mb-5 text-xl font-semibold text-white">Commercial action queue</h2>
        <div class="space-y-4">
            @forelse ($actions as $action)
                <article class="rounded-xl border border-white/10 bg-slate-950 p-4">
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <div><h3 class="font-semibold text-white">{{ $action->title }}</h3><p class="text-sm text-slate-400">{{ $action->account->name }} · {{ $action->owner }}</p></div>
                        <span class="rounded-full bg-slate-800 px-3 py-1 text-xs capitalize text-slate-200">{{ str_replace('_', ' ', $action->status) }}</span>
                    </div>
                    @if ($action->description)<p class="mb-3 text-sm text-slate-300">{{ $action->description }}</p>@endif
                    <form method="POST" action="{{ route('admin.actions.update', $action) }}" class="flex flex-wrap gap-3">
                        @csrf @method('PATCH')
                        <label class="sr-only" for="action-status-{{ $action->id }}">Action status</label>
                        <select id="action-status-{{ $action->id }}" name="status" class="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-white">
                            @foreach (['open', 'in_progress', 'pending_approval', 'completed', 'blocked', 'cancelled'] as $status)
                                <option value="{{ $status }}" @selected($action->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="action-assignee-{{ $action->id }}">Assigned team member</label>
                        <select id="action-assignee-{{ $action->id }}" name="assigned_to" class="border border-white/10 bg-slate-900 px-3 py-2 text-white"><option value="">No linked user</option>@foreach ($actionAssignees as $assignee)<option value="{{ $assignee->id }}" @selected($action->assigned_to === $assignee->id)>{{ $assignee->name }}</option>@endforeach</select>
                        <label class="sr-only" for="action-owner-{{ $action->id }}">Action owner</label>
                        <input id="action-owner-{{ $action->id }}" name="owner" value="{{ $action->owner }}" maxlength="255" class="min-w-32 border border-white/10 bg-slate-900 px-3 py-2 text-white">
                        <label class="sr-only" for="action-due-{{ $action->id }}">Action due date</label>
                        <input id="action-due-{{ $action->id }}" name="due_date" type="date" value="{{ $action->due_date?->format('Y-m-d') }}" class="border border-white/10 bg-slate-900 px-3 py-2 text-white">
                        <input name="note" placeholder="Update note (optional)" maxlength="2000" class="min-w-48 flex-1 rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-white placeholder:text-slate-500">
                        @if ($action->status === 'completed' || in_array(old('status'), ['completed'], true))
                            <label class="sr-only" for="action-outcome-{{ $action->id }}">Action outcome</label>
                            <textarea id="action-outcome-{{ $action->id }}" name="outcome" rows="2" maxlength="5000" placeholder="Record customer response or business outcome" class="min-w-full border border-white/10 bg-slate-900 px-3 py-2 text-white">{{ $action->outcome }}</textarea>
                        @else
                            <label class="sr-only" for="action-outcome-{{ $action->id }}">Action outcome (required when completing)</label>
                            <textarea id="action-outcome-{{ $action->id }}" name="outcome" rows="2" maxlength="5000" placeholder="Enter outcome before selecting Completed" class="min-w-full border border-white/10 bg-slate-900 px-3 py-2 text-white">{{ $action->outcome }}</textarea>
                        @endif
                        <button class="rounded-xl border border-white/15 px-4 py-2 text-white hover:border-cyan-400/50">Update</button>
                    </form>
                    @if ($action->outcome)<p class="mt-3 border-l-2 border-emerald-500 pl-3 text-sm text-slate-300"><strong>Recorded outcome:</strong> {{ $action->outcome }}</p>@endif
                    @if ($action->events->isNotEmpty())
                        <details class="mt-3">
                            <summary class="cursor-pointer text-xs font-semibold text-cyan-200">Escalation and status history ({{ $action->events->count() }})</summary>
                            <ol class="mt-3 space-y-2 border-l border-white/10 pl-4">
                                @foreach ($action->events->sortByDesc('created_at') as $event)
                                    <li class="text-xs text-slate-300"><strong>{{ ucfirst(str_replace('_', ' ', $event->to_status)) }}</strong> · {{ $event->user?->name ?? 'System' }} · {{ $event->created_at->format('M j, Y g:i A') }} @if ($event->note)<span class="block text-slate-500">{{ $event->note }}</span>@endif</li>
                                @endforeach
                            </ol>
                        </details>
                    @endif
                </article>
            @empty
                <p class="text-sm text-slate-400">No actions in the workspace yet.</p>
            @endforelse
        </div>
    </section>
@endsection
