@extends('layouts.console', ['title' => 'Revenue'])

@section('content')
    <div class="revenue-page">
    @php
        $stageLabels = ['discovery' => 'Discovery', 'validation' => 'Validation', 'proposal' => 'Proposal', 'commit' => 'Commit', 'won' => 'Won', 'lost' => 'Lost', 'unassigned' => 'Not recorded'];
        $stages = collect($stageLabels)->mapWithKeys(fn ($label, $stage) => [
            $stage => $stage === 'unassigned'
                ? $accounts->getCollection()->filter(fn ($account) => $account->opportunity_stage === null)
                : $accounts->getCollection()->where('opportunity_stage', $stage),
        ]);
    @endphp
    <section class="console-page-heading">
        <div><p class="console-eyebrow">REVENUE INTELLIGENCE</p><h1>Renewals and pipeline</h1><p>Review renewal timing, expansion potential and forecast movement using the customer context in your workspace.</p></div>
        <a href="{{ route('accounts.index') }}" class="console-button console-button-secondary">Account directory <span aria-hidden="true">↗</span></a>
    </section>

    <section class="revenue-summary" aria-label="Revenue overview">
        <article class="revenue-summary-card revenue-summary-risk"><span>Revenue in at-risk accounts</span><strong>${{ number_format((float) $atRiskValue, 0) }}</strong><small>Annual account value, not a churn prediction</small></article>
        <article class="revenue-summary-card"><span>Expansion opportunity</span><strong>${{ number_format((float) $expansionValue, 0) }}</strong><small>Entered opportunity amounts on expanding accounts</small></article>
        <article class="revenue-summary-card"><span>Current forecast</span><strong>${{ number_format((float) $forecastValue, 0) }}</strong><small>Sum of account forecast values entered</small></article>
        <article class="revenue-summary-card"><span>Renewals due in 90 days</span><strong>{{ \App\Models\Account::query()->whereBetween('renewal_date', [today(), today()->addDays(90)])->count() }}</strong><small>Based on recorded renewal dates</small></article>
    </section>

    <section class="console-panel revenue-filters">
        <form method="GET" action="{{ route('revenue.index') }}" class="revenue-filter-form">
            <label class="console-field"><span>Search</span><input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Account or industry"></label>
            <label class="console-field"><span>Segment</span><select name="segment"><option value="">All segments</option>@foreach ($segments as $segment)<option value="{{ $segment }}" @selected(($filters['segment'] ?? '') === $segment)>{{ ucfirst($segment) }}</option>@endforeach</select></label>
            <label class="console-field"><span>Pipeline stage</span><select name="stage"><option value="">All stages</option>@foreach ($stageLabels as $stage => $label)<option value="{{ $stage }}" @selected(($filters['stage'] ?? '') === $stage)>{{ $label }}</option>@endforeach</select></label>
            <label class="console-field"><span>Renewal window</span><select name="renewals"><option value="">Any renewal</option>@foreach (['90' => 'Next 90 days', '180' => 'Next 180 days', '365' => 'Next 12 months'] as $days => $label)<option value="{{ $days }}" @selected(($filters['renewals'] ?? '') === $days)>{{ $label }}</option>@endforeach</select></label>
            <div class="revenue-filter-actions"><button class="console-button console-button-primary" type="submit">Apply filters</button><a class="console-button console-button-secondary" href="{{ route('revenue.index') }}">Clear</a></div>
        </form>
    </section>

    <div class="revenue-layout">
        <section class="console-panel revenue-pipeline">
            <div class="console-panel-heading"><div><p class="console-eyebrow">OPPORTUNITY STAGES</p><h2>Current pipeline</h2></div><span class="panel-count">{{ $accounts->total() }}</span></div>
            @forelse ($stages as $stage => $stageAccounts)
                @if ($stageAccounts->isNotEmpty())
                    <section class="pipeline-stage">
                        <div class="pipeline-stage-head"><h3>{{ $stageLabels[$stage] }}</h3><span>{{ $stageAccounts->count() }} account{{ $stageAccounts->count() === 1 ? '' : 's' }}</span></div>
                        @foreach ($stageAccounts as $account)
                            <a class="pipeline-account" href="{{ route('accounts.show', $account) }}">
                                <span><strong>{{ $account->name }}</strong><small>{{ $account->segment }} · renewal {{ $account->renewal_date?->format('M j, Y') ?? 'Not recorded' }}</small></span>
                                <span class="pipeline-values"><strong>{{ ($account->forecast_revenue ?? $account->annual_revenue) !== null ? '$'.number_format((float) ($account->forecast_revenue ?? $account->annual_revenue), 0) : 'Not recorded' }}</strong><small>forecast</small></span>
                                <span class="pipeline-expand">{{ (float) $account->expansion_value > 0 ? '+$'.number_format((float) $account->expansion_value, 0) : '—' }}</span>
                                <span class="dashboard-chevron" aria-hidden="true">↗</span>
                            </a>
                        @endforeach
                    </section>
                @endif
            @empty
                <p class="console-empty-copy">No matching accounts. Adjust the filters or add account pipeline context.</p>
            @endforelse
            <div class="mt-5">{{ $accounts->links() }}</div>
        </section>

        <aside class="revenue-aside">
            <section class="console-panel">
                <div class="console-panel-heading"><div><p class="console-eyebrow">FORECAST CONTEXT</p><h2>Recent changes</h2></div></div>
                <div class="forecast-change-list">
                    @forelse ($forecastChanges as $change)
                        <a class="forecast-change" href="{{ route('accounts.show', $change->account) }}">
                            <span class="forecast-change-mark" aria-hidden="true">↗</span>
                            <span><strong>{{ $change->account->name }}</strong><small>{{ $change->summary }}</small><small>{{ $change->user?->name ?? 'System' }} · {{ $change->created_at->diffForHumans() }}</small></span>
                        </a>
                    @empty
                        <p class="console-empty-copy">Forecast changes will appear here when account values or stages are edited.</p>
                    @endforelse
                </div>
            </section>
            <section class="console-panel">
                <div class="console-panel-heading"><div><p class="console-eyebrow">NEXT 90 DAYS</p><h2>Upcoming renewals</h2></div></div>
                <div class="renewal-list">
                    @forelse (\App\Models\Account::query()->whereBetween('renewal_date', [today(), today()->addDays(90)])->orderBy('renewal_date')->limit(8)->get() as $renewal)
                        <a href="{{ route('accounts.show', $renewal) }}"><span><strong>{{ $renewal->name }}</strong><small>{{ $renewal->owner }}</small></span><span>{{ $renewal->renewal_date->format('M j') }}</span></a>
                    @empty
                        <p class="console-empty-copy">No renewal dates are due in this period.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
    </div>
@endsection
