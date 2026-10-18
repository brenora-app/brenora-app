@extends('layouts.console', ['title' => 'Activity log'])

@section('content')
    <section class="console-page-heading">
        <div><p class="console-eyebrow">AUDIT TRAIL</p><h1>Workspace activity</h1><p>Review recorded account, consent, forecast, AI review, integration, and action changes. Events are shown with their actor and account context where available.</p></div>
        <a href="{{ route('admin.index') }}" class="console-button console-button-secondary">Administration <span aria-hidden="true">↗</span></a>
    </section>

    <form class="console-panel mb-5 grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4" method="GET" action="{{ route('activity.index') }}">
        <label class="sr-only" for="activity-q">Search activity</label>
        <input id="activity-q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search summary, account or user" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
        <label class="sr-only" for="activity-event">Filter by event</label>
        <select id="activity-event" name="event" class="border border-white/15 bg-slate-900 px-4 py-3 text-white">
            <option value="">All event types</option>
            @foreach ($eventTypes as $eventType)
                <option value="{{ $eventType }}" @selected(($filters['event'] ?? '') === $eventType)>{{ ucfirst(str_replace('_', ' ', $eventType)) }}</option>
            @endforeach
        </select>
        <button class="console-button console-button-primary" type="submit">Filter activity</button>
        <a href="{{ route('activity.index') }}" class="px-4 py-3 text-sm text-slate-600">Clear filters</a>
    </form>

    <section class="console-panel activity-log" aria-label="Workspace audit events">
        @forelse ($activities as $activity)
            @php
                $deletedAccountId = $activity->details['deleted_account_id'] ?? null;
                $accountLabel = $activity->account?->name ?? ($deletedAccountId ? 'Deleted account #'.$deletedAccountId : 'Workspace');
            @endphp
            <article class="activity-log-row">
                <span class="activity-log-mark" aria-hidden="true"></span>
                <div class="activity-log-main">
                    <div class="activity-log-heading">
                        <strong>{{ $activity->summary }}</strong>
                        <span>{{ ucfirst(str_replace('_', ' ', $activity->event_type)) }}</span>
                    </div>
                    <p>{{ $accountLabel }} · {{ $activity->user?->name ?? 'System' }} · {{ $activity->created_at->format('M j, Y g:i A') }}</p>
                    @if ($activity->details)
                        <details class="activity-log-details">
                            <summary>View recorded details</summary>
                            <pre>{{ json_encode($activity->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                    @endif
                </div>
            </article>
        @empty
            <p class="console-empty-copy">No activity matches these filters.</p>
        @endforelse
    </section>
    <div class="mt-5">{{ $activities->links() }}</div>
@endsection
