@extends('layouts.console', ['title' => 'Accounts'])

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-cyan-300">Customer records</p>
            <h1 class="mt-2 text-3xl font-bold text-white">Accounts</h1>
        </div>
        @can('create', App\Models\Account::class)
            <a href="{{ route('accounts.create') }}" class="rounded-full bg-cyan-400 px-5 py-3 font-semibold text-slate-950 hover:bg-cyan-300">Add account</a>
        @endcan
    </div>

    <form class="console-panel mb-6 grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4" method="GET" action="{{ route('accounts.index') }}">
        <label class="sr-only" for="q">Search accounts</label>
        <input id="q" name="q" value="{{ request('q') }}" placeholder="Search accounts, contacts, signals, actions" class="w-full max-w-md border border-white/10 bg-slate-900 px-4 py-3 text-white placeholder:text-slate-500 focus:border-cyan-400 focus:outline-none">
        <label class="sr-only" for="status">Filter by account status</label>
        <select id="status" name="status" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All statuses</option>
            @foreach (['needs_review' => 'Needs review', 'healthy' => 'Healthy', 'expanding' => 'Expanding', 'at_risk' => 'At risk'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="segment">Filter by segment</label>
        <select id="segment" name="segment" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All segments</option>
            @foreach ($segments as $segment)
                <option value="{{ $segment }}" @selected(($filters['segment'] ?? '') === $segment)>{{ ucfirst($segment) }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="stage">Filter by pipeline stage</label>
        <select id="stage" name="stage" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All stages</option>
            @foreach (['discovery' => 'Discovery', 'validation' => 'Validation', 'proposal' => 'Proposal', 'commit' => 'Commit', 'won' => 'Won', 'lost' => 'Lost'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['stage'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="engagement">Filter by engagement</label>
        <select id="engagement" name="engagement" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All engagement</option>
            @foreach (['unknown' => 'Not yet assessed', 'active' => 'Active', 'low_engagement' => 'Low engagement', 'disengaged' => 'Disengaged'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['engagement'] ?? '') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="health">Filter by health assessment</label>
        <select id="health" name="health" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All health data</option>
            <option value="assessed" @selected(($filters['health'] ?? '') === 'assessed')>Health assessed</option>
            <option value="unassessed" @selected(($filters['health'] ?? '') === 'unassessed')>Health not assessed</option>
        </select>
        <label class="sr-only" for="health_min">Minimum health score</label>
        <input id="health_min" name="health_min" type="number" min="0" max="100" value="{{ $filters['health_min'] ?? '' }}" placeholder="Health score from" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
        <label class="sr-only" for="health_max">Maximum health score</label>
        <input id="health_max" name="health_max" type="number" min="0" max="100" value="{{ $filters['health_max'] ?? '' }}" placeholder="Health score to" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
        <label class="sr-only" for="signal_type">Filter by signal type</label>
        <select id="signal_type" name="signal_type" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All signal types</option>
            @foreach ($signalTypes as $type)
                <option value="{{ $type }}" @selected(($filters['signal_type'] ?? '') === $type)>{{ ucfirst($type) }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="playbook">Filter by playbook</label>
        <select id="playbook" name="playbook" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All playbooks</option>
            @foreach ($playbooks as $playbook)
                <option value="{{ $playbook }}" @selected(($filters['playbook'] ?? '') === $playbook)>{{ $playbook }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="sort">Sort accounts</label>
        <select id="sort" name="sort" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            @foreach (['name' => 'Name', 'renewal_date' => 'Renewal date', 'annual_revenue' => 'Annual revenue', 'health_score' => 'Health score', 'updated_at' => 'Recently updated'] as $value => $label)
                <option value="{{ $value }}" @selected(($filters['sort'] ?? 'name') === $value)>Sort: {{ $label }}</option>
            @endforeach
        </select>
        <label class="sr-only" for="direction">Sort direction</label>
        <select id="direction" name="direction" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="asc" @selected(($filters['direction'] ?? 'asc') === 'asc')>Ascending</option>
            <option value="desc" @selected(($filters['direction'] ?? 'asc') === 'desc')>Descending</option>
        </select>
        <button class="rounded-xl border border-white/15 px-5 text-white hover:border-cyan-400/50" type="submit">Search</button>
        <a href="{{ route('accounts.index') }}" class="px-4 py-3 text-sm text-slate-300 hover:text-white">Clear filters</a>
    </form>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
        <div class="divide-y divide-white/10">
            @forelse ($accounts as $account)
                <article class="flex flex-wrap items-center justify-between gap-4 p-5">
                    <div>
                        <a href="{{ route('accounts.show', $account) }}" class="text-lg font-semibold text-white hover:text-cyan-200">{{ $account->name }}</a>
                        <p class="mt-1 text-sm text-slate-400">{{ $account->industry ?: 'Industry not recorded' }} · {{ $account->owner ?: 'Owner not recorded' }} · {{ $account->signals_count }} signals · {{ $account->contacts_count }} contacts</p>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="rounded-full bg-slate-800 px-3 py-1 capitalize text-slate-200">{{ str_replace('_', ' ', $account->status) }}</span>
                        <span class="font-semibold text-white">{{ $account->health_score !== null ? $account->health_score.' health' : 'Health not assessed' }}</span>
                        <span class="text-xs text-slate-400">Renewal {{ $account->renewal_date?->format('M Y') ?? 'Not recorded' }}</span>
                    </div>
                </article>
            @empty
                <p class="p-8 text-center text-slate-400">No accounts found. Add one to begin tracking customer context.</p>
            @endforelse
        </div>
    </div>
    <div class="mt-6">{{ $accounts->links() }}</div>
@endsection
