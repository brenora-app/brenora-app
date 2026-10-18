@extends('layouts.console', ['title' => 'Overview'])

@section('content')
    @php
        $avgHealth = (int) round($avgHealth ?? 0);
        $atRisk = $atRiskCount;
    @endphp

    <section class="dashboard-welcome">
        <div>
            <p class="console-eyebrow">{{ now()->format('l, F j, Y') }} <span>·</span> {{ ucfirst(auth()->user()->role) }} workspace</p>
            <h1>{{ auth()->user()->isAdmin() ? 'Your portfolio, at a glance.' : (auth()->user()->role === 'analyst' ? 'Turn signals into action.' : 'Welcome back, '.strtok(auth()->user()->name, ' ').'.') }}</h1>
            <p class="dashboard-intro">A live view of customer health, emerging risk and the work that moves relationships forward.</p>
        </div>
        <div class="dashboard-actions">
            <a class="console-button console-button-secondary" href="{{ route('accounts.index') }}">Browse accounts <span aria-hidden="true">↗</span></a>
            @can('create', App\Models\Account::class)
                <a class="console-button console-button-primary" href="{{ route('accounts.create') }}"><span aria-hidden="true">+</span> Add account</a>
            @endcan
        </div>
    </section>

    @if ($atRisk > 0 || $overdueActions > 0 || $missingContext->isNotEmpty())
        <a href="{{ $atRisk > 0 ? route('accounts.index', ['status' => 'at_risk']) : ($overdueActions > 0 ? route('admin.index') : route('accounts.index')) }}" class="dashboard-alert" role="status">
            <span class="dashboard-alert-mark" aria-hidden="true">!</span>
            <span><strong>
                @if ($atRisk > 0){{ $atRisk }} account{{ $atRisk === 1 ? '' : 's' }} need{{ $atRisk === 1 ? 's' : '' }} attention
                @elseif ($overdueActions > 0){{ $overdueActions }} commercial action{{ $overdueActions === 1 ? '' : 's' }} overdue
                @else Account information needs completing
                @endif
            </strong><small>
                @if ($atRisk > 0)At-risk portfolio value: ${{ number_format((float) $atRiskRevenue, 0) }}. Review the recorded signals.
                @elseif ($overdueActions > 0)Review action owners, dates and escalation history.
                @else {{ $missingContext->count() }} account{{ $missingContext->count() === 1 ? '' : 's' }} may be missing contacts, plan or signal context.
                @endif
            </small></span>
            <span class="dashboard-alert-link">Review accounts <span aria-hidden="true">→</span></span>
        </a>
    @endif

    @if ($sections['metrics'])
        <section class="dashboard-metrics" aria-label="Portfolio metrics">
            <article class="metric-card metric-card-featured">
                <div class="metric-top"><span>Recorded annual revenue</span><span class="metric-icon" aria-hidden="true">$</span></div>
                <p class="metric-value">${{ number_format((float) $totalRevenue, 0) }}</p>
                <div class="metric-foot"><span>{{ $totalAccounts }} accounts · excludes unrecorded values</span><span class="metric-arrow" aria-hidden="true">↗</span></div>
            </article>
            <article class="metric-card">
                <div class="metric-top"><span>Average account health</span><span class="metric-icon metric-icon-green" aria-hidden="true">↗</span></div>
                <p class="metric-value">{{ $assessedAccounts > 0 ? $avgHealth.'%' : '—' }}</p>
                @if ($assessedAccounts > 0)
                    <div class="metric-progress" role="progressbar" aria-label="Average account health" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $avgHealth }}"><span style="--progress-width: {{ $avgHealth }}%"></span></div>
                @endif
                <div class="metric-foot"><span>{{ $assessedAccounts > 0 ? 'Assessed accounts across the portfolio' : 'No account health scores recorded' }}</span><strong>{{ $assessedAccounts > 0 ? $avgHealth.'/100' : 'Not assessed' }}</strong></div>
            </article>
            <article class="metric-card">
                <div class="metric-top"><span>Expansion opportunity</span><span class="metric-icon metric-icon-green" aria-hidden="true">↗</span></div>
                <p class="metric-value">${{ number_format((float) $expansionValue, 0) }}</p>
                <div class="metric-foot"><span>Recorded across expanding accounts</span><strong>{{ $expandingCount }} candidates</strong></div>
            </article>
            <article class="metric-card">
                <div class="metric-top"><span>At-risk value</span><span class="metric-icon metric-icon-warning" aria-hidden="true">!</span></div>
                <p class="metric-value">${{ number_format((float) $atRiskRevenue, 0) }}</p>
                <div class="metric-foot"><span>{{ $atRisk }} at-risk accounts</span><span class="metric-arrow" aria-hidden="true">↗</span></div>
            </article>
        </section>
    @endif

    <div class="dashboard-grid">
        @if ($sections['accounts'])
            <section class="console-panel dashboard-accounts">
                <div class="console-panel-heading">
                    <div><p class="console-eyebrow">CUSTOMER PORTFOLIO</p><h2>Recently active accounts</h2></div>
                    <a class="console-text-link" href="{{ route('accounts.index') }}">All accounts <span aria-hidden="true">↗</span></a>
                </div>
                <div class="dashboard-account-list">
                    @forelse ($accounts as $account)
                        <a href="{{ route('accounts.show', $account) }}" class="dashboard-account">
                            <span class="account-monogram">{{ mb_substr($account->name, 0, 1) }}</span>
                            <span class="dashboard-account-main">
                                <span class="dashboard-account-title">{{ $account->name }} <span class="account-status account-status-{{ $account->status }}">{{ str_replace('_', ' ', $account->status) }}</span></span>
                                <span class="dashboard-account-meta">{{ $account->industry ?: 'Industry not recorded' }} <i>·</i> {{ $account->owner ?: 'Owner not recorded' }} <i>·</i> {{ $account->segment }}</span>
                                @if ($account->health_score !== null)
                                    <span class="dashboard-health"><span>Health</span><span class="dashboard-health-track" role="progressbar" aria-label="{{ $account->name }} health" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $account->health_score }}"><i class="health-fill-{{ $account->status }}" style="--progress-width: {{ $account->health_score }}%"></i></span><strong>{{ $account->health_score }}</strong></span>
                                @else
                                    <span class="dashboard-health"><span>Health</span><strong>Not assessed</strong></span>
                                @endif
                            </span>
                            <span class="dashboard-account-value">{{ $account->annual_revenue !== null ? '$'.number_format((float) $account->annual_revenue, 0) : 'Not recorded' }}<small>annual revenue</small></span>
                            <span class="dashboard-chevron" aria-hidden="true">↗</span>
                        </a>
                    @empty
                        <div class="dashboard-empty"><span class="dashboard-empty-mark">+</span><strong>Your portfolio starts here.</strong><p>Add your first account to begin tracking customer signals and health.</p>@can('create', App\Models\Account::class)<a href="{{ route('accounts.create') }}">Create an account <span aria-hidden="true">→</span></a>@endcan</div>
                    @endforelse
                </div>
            </section>
        @endif

        <div class="dashboard-side">
            @if ($sections['insights'])
                <section class="console-panel">
                    <div class="console-panel-heading">
                        <div><p class="console-eyebrow">EVIDENCE-BASED</p><h2>Latest insights</h2></div>
                        <span class="panel-count">{{ $insights->count() }}</span>
                    </div>
                    <div class="dashboard-compact-list">
                        @forelse ($insights as $insight)
                            <a href="{{ route('accounts.show', $insight->account) }}" class="dashboard-insight">
                                <span class="insight-indicator insight-{{ $insight->severity }}"></span>
                                <span><strong>{{ $insight->title }}</strong><small>{{ $insight->account->name }} · {{ ucfirst($insight->severity) }} priority</small><span>{{ $insight->summary }}</span></span>
                                <span class="dashboard-chevron" aria-hidden="true">↗</span>
                            </a>
                        @empty
                            <p class="console-empty-copy">No insights have been generated yet. Add customer signals to start a grounded review.</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($sections['actions'])
                <section class="console-panel">
                    <div class="console-panel-heading">
                        <div><p class="console-eyebrow">FOLLOW-THROUGH</p><h2>Commercial actions</h2></div>
                        @if (auth()->user()->isAdmin())<a class="console-text-link" href="{{ route('admin.index') }}">Manage <span aria-hidden="true">↗</span></a>@endif
                    </div>
                    <div class="dashboard-compact-list dashboard-actions-list">
                        @forelse ($actions as $action)
                            <a href="{{ route('accounts.show', $action->account) }}" class="dashboard-action-item">
                                <span class="action-check" aria-hidden="true">{{ $action->status === 'completed' ? '✓' : '' }}</span>
                                <span><strong>{{ $action->title }}</strong><small>{{ $action->account->name }} <i>·</i> {{ $action->assignee?->name ?? $action->owner }} @if ($action->playbook)<i>·</i> {{ $action->playbook }}@endif</small></span>
                                <span class="action-date">{{ $action->due_date?->format('M j') ?? str_replace('_', ' ', $action->status) }}</span>
                            </a>
                        @empty
                            <p class="console-empty-copy">No commercial actions are recorded for this workspace.</p>
                        @endforelse
                    </div>
                </section>
            @endif

            @if (auth()->user()->canManageAccounts())
                <a href="{{ route('custom-fields.index') }}" class="dashboard-custom-fields">
                    <span class="custom-fields-mark" aria-hidden="true">+</span>
                    <span><strong>Make account records your own</strong><small>Add custom fields that match the way your team works.</small></span>
                    <span aria-hidden="true">→</span>
                </a>
            @endif
        </div>
    </div>
@endsection
