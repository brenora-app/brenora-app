@extends('layouts.console', ['title' => $account->name])

@section('content')
    <div class="account-detail-page">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('accounts.index') }}" class="text-sm text-cyan-200 hover:text-white">← Accounts</a>
            <h1 class="mt-3 text-3xl font-bold text-white">{{ $account->name }}</h1>
            <p class="mt-2 text-slate-400">{{ $account->industry ?: 'Industry not recorded' }} · Owner: {{ $account->owner ?: 'Not recorded' }}</p>
        </div>
        <div class="flex gap-3">
            @can('update', $account)
                <a href="{{ route('accounts.edit', $account) }}" class="rounded-full border border-white/15 px-5 py-2.5 text-white hover:border-cyan-400/50">Edit</a>
            @endcan
            @can('delete', $account)
                <form method="POST" action="{{ route('accounts.destroy', $account) }}" onsubmit="return confirm('Delete this account and its related records?')">
                    @csrf @method('DELETE')
                    <button class="rounded-full border border-rose-400/30 px-5 py-2.5 text-rose-200 hover:bg-rose-500/10" type="submit">Delete</button>
                </form>
            @endcan
        </div>
    </div>

    <div class="account-stat-grid">
        <div class="account-stat-card"><p>Annual revenue</p><strong>{{ $account->annual_revenue !== null ? '$'.number_format((float) $account->annual_revenue, 0) : 'Not recorded' }}</strong></div>
        <div class="account-stat-card"><p>Health score</p><strong>{{ $account->health_score !== null ? $account->health_score.'/100' : 'Not assessed' }}</strong></div>
        <div class="account-stat-card"><p>Renewal date</p><strong>{{ $account->renewal_date?->format('M j, Y') ?? 'Not recorded' }}</strong></div>
    </div>

    <section class="console-panel account-subscription-panel">
        <div class="console-panel-heading">
            <div><p class="console-eyebrow">SUBSCRIPTION CONTEXT</p><h2>Plan, renewal and relationship</h2></div>
            <span class="account-status account-status-{{ $account->engagement_status === 'active' ? 'healthy' : ($account->engagement_status === 'unknown' ? 'needs_review' : 'at_risk') }}">{{ str_replace('_', ' ', $account->engagement_status) }}</span>
        </div>
        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="custom-value-item"><dt>Subscription plan</dt><dd>{{ $account->subscription_plan ?: 'Not provided' }}</dd></div>
            <div class="custom-value-item"><dt>Contract term</dt><dd>{{ $account->contract_length_months ? $account->contract_length_months.' months' : 'Not provided' }}</dd></div>
            <div class="custom-value-item"><dt>Customer segment</dt><dd>{{ $account->segment }}</dd></div>
            <div class="custom-value-item"><dt>Pipeline stage</dt><dd>{{ $account->opportunity_stage ? ucfirst($account->opportunity_stage) : 'Not recorded' }}@if ((float) $account->expansion_value > 0) · ${{ number_format((float) $account->expansion_value, 0) }} expansion @endif</dd></div>
        </dl>
    </section>

    <div class="account-content-grid">
        <section class="console-panel p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">CUSTOMER RELATIONSHIPS</p><h2>Key contacts</h2></div><span class="panel-count">{{ $account->contacts->count() }}</span></div>
            <div class="dashboard-compact-list">
                @forelse ($account->contacts as $contact)
                    <article class="contact-record">
                        <div class="contact-record-header">
                            <span class="account-monogram">{{ mb_substr($contact->name, 0, 1) }}</span>
                            <span><strong>{{ $contact->name }}</strong><small>{{ $contact->title ?: 'Role not provided' }} @if ($contact->is_primary) · Primary @endif</small></span>
                            <span class="consent-pill consent-{{ $contact->consent_status }}">{{ ucfirst($contact->consent_status) }}</span>
                        </div>
                        <div class="contact-record-details">
                            @if ($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@endif
                            @if ($contact->phone)<a href="tel:{{ $contact->phone }}">{{ $contact->phone }}</a>@endif
                        </div>
                        @can('update', $account)
                            <details class="contact-edit">
                                <summary>Edit contact and consent</summary>
                                <form method="POST" action="{{ route('accounts.contacts.update', [$account, $contact]) }}" class="console-form contact-form">
                                    @csrf
                                    @method('PUT')
                                    <div class="contact-form-grid">
                                        <label class="console-field"><span>Name</span><input name="name" value="{{ $contact->name }}" maxlength="160" required></label>
                                        <label class="console-field"><span>Role / title</span><input name="title" value="{{ $contact->title }}" maxlength="120"></label>
                                        <label class="console-field"><span>Email</span><input name="email" type="email" value="{{ $contact->email }}" maxlength="254"></label>
                                        <label class="console-field"><span>Phone</span><input name="phone" type="tel" value="{{ $contact->phone }}" maxlength="40"></label>
                                        <label class="console-field"><span>Consent</span><select name="consent_status" required>@foreach (['pending' => 'Pending', 'granted' => 'Granted', 'revoked' => 'Revoked'] as $value => $label)<option value="{{ $value }}" @selected($contact->consent_status === $value)>{{ $label }}</option>@endforeach</select></label>
                                        <label class="console-switch-row"><input type="checkbox" name="is_primary" value="1" @checked($contact->is_primary)><span><strong>Primary contact</strong><small>Only one primary contact per account.</small></span></label>
                                    </div>
                                    <button class="console-button console-button-secondary" type="submit">Save contact</button>
                                </form>
                                <form method="POST" action="{{ route('accounts.contacts.destroy', [$account, $contact]) }}" onsubmit="return confirm('Remove this contact from the account?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="contact-remove" type="submit">Remove contact</button>
                                </form>
                            </details>
                        @endcan
                    </article>
                @empty
                    <p class="console-empty-copy">No contacts are on this account yet.</p>
                @endforelse
            </div>
            @can('update', $account)
                <details class="contact-add">
                    <summary>Add a key contact</summary>
                    <form method="POST" action="{{ route('accounts.contacts.store', $account) }}" class="console-form contact-form">
                        @csrf
                        <div class="contact-form-grid">
                            <label class="console-field"><span>Name</span><input name="name" maxlength="160" required placeholder="Contact name"></label>
                            <label class="console-field"><span>Role / title</span><input name="title" maxlength="120" placeholder="e.g. VP Operations"></label>
                            <label class="console-field"><span>Email</span><input name="email" type="email" maxlength="254"></label>
                            <label class="console-field"><span>Phone</span><input name="phone" type="tel" maxlength="40"></label>
                            <label class="console-field"><span>Consent</span><select name="consent_status" required><option value="pending">Pending verification</option><option value="granted">Granted</option><option value="revoked">Revoked</option></select></label>
                            <label class="console-switch-row"><input type="checkbox" name="is_primary" value="1"><span><strong>Primary contact</strong><small>Mark as the key relationship owner.</small></span></label>
                        </div>
                        <button class="console-button console-button-primary" type="submit">Add contact <span aria-hidden="true">→</span></button>
                    </form>
                </details>
            @endcan
        </section>

        <section class="console-panel p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">SOURCE FRESHNESS</p><h2>Signal sources</h2></div><span class="panel-count">{{ $account->signals->groupBy('source')->count() }}</span></div>
            <div class="dashboard-compact-list">
                @forelse ($account->signals->groupBy('source') as $source => $sourceSignals)
                    @php($latestSourceSignal = $sourceSignals->first())
                    @php($sourceIsStale = $latestSourceSignal->recorded_at->lt(now()->subDays(30)))
                    <div class="source-status-row">
                        <span class="source-status-mark {{ $sourceIsStale ? 'source-is-stale' : 'source-is-fresh' }}" aria-hidden="true"></span>
                        <span><strong>{{ $source }}</strong><small>Latest recorded signal {{ $latestSourceSignal->recorded_at->diffForHumans() }}</small></span>
                        <span class="source-status-label">{{ $sourceIsStale ? 'Stale' : 'Recent' }}</span>
                    </div>
                @empty
                    <p class="console-empty-copy">No signal sources have been recorded for this account.</p>
                @endforelse
            </div>
            <p class="source-honesty-note">Freshness is based on recorded signals. Source connections are not synced automatically.</p>
            <div class="consent-summary">
                <span><strong>Data-processing consent</strong><small>Updated {{ $account->consent_updated_at?->diffForHumans() ?? 'not yet recorded' }}</small></span>
                <span class="consent-pill consent-{{ $account->consent_status }}">{{ ucfirst($account->consent_status) }}</span>
            </div>
            @if ($account->consent_status !== 'granted')
                <p class="consent-warning">AI processing is disabled until account consent is granted.</p>
            @endif
        </section>
    </div>

    @if (auth()->user()->canReviewInsights())
        <section class="console-panel mt-6 p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">GROUNDED ASSISTANCE</p><h2>Ask about this account</h2></div><span class="ai-local-label">LOCAL MODEL · EVIDENCE REQUIRED</span></div>
            <p class="console-muted">Ask a question or use the theme prompt. Answers are generated from recorded account signals, cite signal IDs, and are saved in the activity record.</p>
            <form method="POST" action="{{ route('accounts.brief', $account) }}" class="briefing-form" x-data="{ question: '' }">
                @csrf
                <label class="sr-only" for="briefing-question">Account question</label>
                <textarea id="briefing-question" name="question" rows="3" maxlength="500" minlength="5" required x-model="question" placeholder="e.g. What changed in usage ahead of renewal?">{{ old('question') }}</textarea>
                <div class="briefing-actions">
                    <div class="briefing-prompts">
                        <button type="button" @click="question = 'Summarize the recurring themes across the recorded customer signals and cite the supporting signals.'">Find conversation themes</button>
                        <button type="button" @click="question = 'What important account information is missing or out of date?'">Find missing context</button>
                    </div>
                    <button class="console-button console-button-primary" type="submit" @disabled($account->consent_status !== 'granted' || $account->signals->isEmpty())>Generate briefing <span aria-hidden="true">→</span></button>
                </div>
                @error('question')<span class="console-field-error">{{ $message }}</span>@enderror
                @error('briefing')<span class="console-field-error">{{ $message }}</span>@enderror
                @error('ai')<span class="console-field-error">{{ $message }}</span>@enderror
            </form>
            @if (session('briefing'))
                <article class="briefing-result" role="status">
                    <p class="console-eyebrow">EVIDENCE-LINKED RESPONSE</p>
                    <strong>{{ session('briefing.question') }}</strong>
                    <p>{{ session('briefing.answer') }}</p>
                    <small>Supporting signals: {{ collect(session('briefing.signal_ids'))->map(fn ($id) => '#'.$id)->implode(', ') }}</small>
                </article>
            @endif
        </section>
    @endif

    @if (!empty($account->custom_fields))
        <section class="console-panel mb-8 p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">TEAM-DEFINED DETAILS</p><h2>Custom account fields</h2></div></div>
            <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($account->custom_fields as $key => $value)
                    @if ($customFieldLabels->has($key))
                        <div class="custom-value-item"><dt>{{ $customFieldLabels[$key] }}</dt><dd>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?: '—') }}</dd></div>
                    @endif
                @endforeach
            </dl>
        </section>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="text-xl font-semibold text-white">Customer signals</h2>
                @can('update', $account)
                    <a href="{{ route('accounts.signals.create', $account) }}" class="text-sm font-semibold text-cyan-200 hover:text-white">Add signal</a>
                @endcan
            </div>
            <div class="space-y-3">
                @forelse ($account->signals as $signal)
                    <article class="rounded-xl border border-white/10 bg-slate-950 p-4">
                        <div class="flex items-center justify-between gap-3"><h3 class="font-semibold capitalize text-white">{{ $signal->type }}</h3><span class="text-xs text-slate-400">{{ $signal->recorded_at->format('M j, Y g:i A') }}</span></div>
                        <p class="mt-1 text-xs uppercase tracking-wider text-cyan-200">{{ $signal->source }} · score {{ $signal->score }}</p>
                        <p class="mt-3 text-sm text-slate-300">{{ $signal->summary }}</p>
                        @can('update', $account)
                            <div class="mt-3 flex gap-4 text-sm">
                                <a href="{{ route('accounts.signals.edit', [$account, $signal]) }}" class="text-cyan-200 hover:text-white">Edit</a>
                                <form method="POST" action="{{ route('accounts.signals.destroy', [$account, $signal]) }}" onsubmit="return confirm('Delete this signal?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-200 hover:text-rose-100" type="submit">Delete</button>
                                </form>
                            </div>
                        @endcan
                    </article>
                @empty
                    <p class="text-sm text-slate-400">No signals have been recorded for this account.</p>
                @endforelse
            </div>
        </section>

        <div class="space-y-6">
            <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-semibold text-white">Insight cards</h2>
                    @if (auth()->user()->canReviewInsights())
                        <form method="POST" action="{{ route('accounts.insights.generate', $account) }}">
                            @csrf
                            <button type="submit" class="rounded-full border border-violet-300/30 px-4 py-2 text-sm font-medium text-violet-100 hover:border-violet-200/60">Generate with local model</button>
                        </form>
                    @endif
                </div>
                <div class="space-y-3">
                    @forelse ($account->insights as $insight)
                        <article class="rounded-xl border border-white/10 bg-slate-950 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-semibold text-white">{{ $insight->title }}</h3>
                                <span class="rounded-full bg-slate-800 px-3 py-1 text-xs capitalize text-slate-200">{{ $insight->status }}</span>
                            </div>
                            <p class="mt-3 text-sm text-slate-300">{{ $insight->summary }}</p>
                            <p class="mt-2 text-xs text-slate-500">Source: {{ $insight->model_name ?? 'Unspecified' }} · {{ ucfirst($insight->severity) }}</p>
                            @if ($insight->reviewed_at)
                                <p class="mt-1 text-xs text-slate-500">Reviewed {{ $insight->reviewed_at->format('M j, Y g:i A') }} by {{ $insight->reviewer?->name ?? 'former user' }}</p>
                            @endif
                            @if ($insight->evidence)
                                <p class="mt-2 text-xs text-slate-400">Evidence: {{ implode(', ', $insight->evidence) }}</p>
                            @endif
                            @if (auth()->user()->canReviewInsights())
                                @if ($insight->reviewer_correction)
                                    <div class="reviewer-correction"><strong>Reviewer correction</strong><p>{{ $insight->reviewer_correction }}</p></div>
                                @endif
                                <form method="POST" action="{{ route('insights.review', $insight) }}" class="insight-review-form">
                                    @csrf
                                    <label for="correction-{{ $insight->id }}">Reviewer correction / note</label>
                                    <textarea id="correction-{{ $insight->id }}" name="reviewer_correction" rows="2" maxlength="5000" placeholder="Capture a correction or explain the review decision">{{ $insight->reviewer_correction }}</textarea>
                                    <div class="insight-review-actions">
                                        @if ($insight->status === 'draft')
                                            <button type="submit" name="status" value="reviewed" class="console-button console-button-primary">Save and approve</button>
                                            <button type="submit" name="status" value="rejected" class="console-button console-button-danger">Reject draft</button>
                                        @else
                                            <input type="hidden" name="status" value="{{ $insight->status }}">
                                            <button type="submit" class="console-button console-button-secondary">Save correction</button>
                                        @endif
                                    </div>
                                </form>
                            @endif
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">No insight cards yet.</p>
                    @endforelse
                </div>
            </section>

            <section class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                <h2 class="mb-5 text-xl font-semibold text-white">Commercial actions</h2>
                <div class="space-y-3">
                    @forelse ($account->actions as $action)
                        <article class="rounded-xl border border-white/10 bg-slate-950 p-4">
                            <h3 class="font-semibold text-white">{{ $action->title }}</h3>
                            <p class="mt-2 text-sm text-slate-300">{{ $action->description }}</p>
                            <p class="mt-2 text-xs capitalize text-slate-400">{{ str_replace('_', ' ', $action->status) }} · Owner {{ $action->owner }} @if ($action->due_date) · Due {{ $action->due_date->format('M j, Y') }} @endif</p>
                        </article>
                    @empty
                        <p class="text-sm text-slate-400">No actions have been created for this account.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="console-panel mt-6 p-6">
        <div class="console-panel-heading"><div><p class="console-eyebrow">TRACEABILITY</p><h2>Account activity and forecast changes</h2></div><span class="panel-count">{{ $account->activity->count() }}</span></div>
        <div class="activity-timeline">
            @forelse ($account->activity->take(20) as $event)
                <article class="activity-event">
                    <span class="activity-event-mark" aria-hidden="true"></span>
                    <div><p><strong>{{ $event->summary }}</strong><small>{{ $event->user?->name ?? 'System' }} · {{ $event->created_at->format('M j, Y g:i A') }}</small></p>
                        @if ($event->event_type === 'forecast_changed' && $event->details)
                            <dl class="forecast-diff">
                                @foreach ($event->details as $field => $change)
                                    <div><dt>{{ str_replace('_', ' ', ucfirst($field)) }}</dt><dd>{{ $change['from'] ?? '—' }} <span aria-hidden="true">→</span> {{ $change['to'] ?? '—' }}</dd></div>
                                @endforeach
                            </dl>
                        @elseif ($event->event_type === 'briefing_generated' && $event->details)
                            <p class="activity-briefing"><strong>Question:</strong> {{ $event->details['question'] }}<br><strong>Response:</strong> {{ $event->details['answer'] }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="console-empty-copy">No account activity has been logged yet.</p>
            @endforelse
        </div>
    </section>
    </div>
@endsection
