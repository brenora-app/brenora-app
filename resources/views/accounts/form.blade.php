@extends('layouts.console', ['title' => $account->exists ? 'Edit account' : 'New account'])

@section('content')
    <a href="{{ $account->exists ? route('accounts.show', $account) : route('accounts.index') }}" class="text-sm text-cyan-200 hover:text-white">← Back to accounts</a>
    <h1 class="mb-8 mt-3 text-3xl font-bold text-white">{{ $account->exists ? 'Edit account' : 'Create account' }}</h1>

    <form method="POST" action="{{ $account->exists ? route('accounts.update', $account) : route('accounts.store') }}" class="console-panel console-account-form max-w-3xl space-y-6 p-6">
        @csrf
        @if ($account->exists) @method('PUT') @endif
        <div class="grid gap-5 md:grid-cols-2">
            @foreach (['name' => 'Account name', 'industry' => 'Industry', 'owner' => 'Account owner'] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="mb-2 block text-sm font-medium text-slate-200">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $account->$field) }}" maxlength="255" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                </div>
            @endforeach
            <div>
                <label for="annual_revenue" class="mb-2 block text-sm font-medium text-slate-200">Annual revenue (USD)</label>
                <input id="annual_revenue" name="annual_revenue" type="number" min="0" step="0.01" value="{{ old('annual_revenue', $account->annual_revenue ?? ($account->status === 'needs_review' ? null : 0)) }}" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
            </div>
            <div>
                <label for="health_score" class="mb-2 block text-sm font-medium text-slate-200">Health score (0–100)</label>
                <input id="health_score" name="health_score" type="number" min="0" max="100" value="{{ old('health_score', $account->health_score ?? ($account->status === 'needs_review' ? null : 50)) }}" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
            </div>
            <div>
                <label for="status" class="mb-2 block text-sm font-medium text-slate-200">Status</label>
                <select id="status" name="status" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white">
                    @foreach (['needs_review' => 'Needs review', 'healthy' => 'Healthy', 'expanding' => 'Expanding', 'at_risk' => 'At risk'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $account->status ?? 'healthy') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="renewal_date" class="mb-2 block text-sm font-medium text-slate-200">Renewal date</label>
                <input id="renewal_date" name="renewal_date" type="date" value="{{ old('renewal_date', $account->renewal_date?->format('Y-m-d')) }}" class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="subscription_plan" class="mb-2 block text-sm font-medium text-slate-200">Subscription plan</label>
                <input id="subscription_plan" name="subscription_plan" value="{{ old('subscription_plan', $account->subscription_plan) }}" maxlength="120" placeholder="Not provided" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="contract_length_months" class="mb-2 block text-sm font-medium text-slate-200">Contract length (months)</label>
                <input id="contract_length_months" name="contract_length_months" type="number" min="1" max="120" value="{{ old('contract_length_months', $account->contract_length_months) }}" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="engagement_status" class="mb-2 block text-sm font-medium text-slate-200">Engagement</label>
                <select id="engagement_status" name="engagement_status" required class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
                    @foreach (['unknown' => 'Not yet assessed', 'active' => 'Active', 'low_engagement' => 'Low engagement', 'disengaged' => 'Disengaged'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('engagement_status', $account->engagement_status ?? 'active') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="segment" class="mb-2 block text-sm font-medium text-slate-200">Customer segment</label>
                <input id="segment" name="segment" value="{{ old('segment', $account->segment ?? 'unclassified') }}" maxlength="80" required placeholder="e.g. Enterprise" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="expansion_value" class="mb-2 block text-sm font-medium text-slate-200">Expansion opportunity (USD)</label>
                <input id="expansion_value" name="expansion_value" type="number" min="0" step="0.01" value="{{ old('expansion_value', $account->expansion_value ?? ($account->status === 'needs_review' ? null : 0)) }}" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="forecast_revenue" class="mb-2 block text-sm font-medium text-slate-200">Forecast revenue (USD)</label>
                <input id="forecast_revenue" name="forecast_revenue" type="number" min="0" step="0.01" value="{{ old('forecast_revenue', $account->forecast_revenue) }}" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
            </div>
            <div>
                <label for="opportunity_stage" class="mb-2 block text-sm font-medium text-slate-200">Pipeline stage</label>
                <select id="opportunity_stage" name="opportunity_stage" class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
                    <option value="" @selected(old('opportunity_stage', $account->opportunity_stage ?? ($account->status === 'needs_review' ? null : 'discovery')) === null)>Not recorded</option>
                    @foreach (['discovery' => 'Discovery', 'validation' => 'Validation', 'proposal' => 'Proposal', 'commit' => 'Commit', 'won' => 'Won', 'lost' => 'Lost'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('opportunity_stage', $account->opportunity_stage ?? ($account->status === 'needs_review' ? null : 'discovery')) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="consent_status" class="mb-2 block text-sm font-medium text-slate-200">Data processing consent</label>
                <select id="consent_status" name="consent_status" required class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
                    @foreach (['pending' => 'Pending verification', 'granted' => 'Granted', 'revoked' => 'Revoked'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('consent_status', $account->consent_status ?? 'pending') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @foreach ($customFields as $field)
                <div @class(['md:col-span-2' => $field->type === 'text'])>
                    <label for="custom-field-{{ $field->id }}" class="mb-2 block text-sm font-medium text-slate-200">
                        {{ $field->name }}@if ($field->required)<span aria-hidden="true"> *</span>@endif
                    </label>
                    @if ($field->type === 'select')
                        <select id="custom-field-{{ $field->id }}" name="custom_fields[{{ $field->key }}]" @required($field->required) class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white">
                            <option value="">Choose {{ strtolower($field->name) }}</option>
                            @foreach ($field->options ?? [] as $option)
                                <option value="{{ $option }}" @selected(old('custom_fields.'.$field->key, $account->custom_fields[$field->key] ?? '') === $option)>{{ $option }}</option>
                            @endforeach
                        </select>
                    @elseif ($field->type === 'checkbox')
                        <input id="custom-field-{{ $field->id }}" type="checkbox" name="custom_fields[{{ $field->key }}]" value="1" @checked(old('custom_fields.'.$field->key, $account->custom_fields[$field->key] ?? false)) @required($field->required) class="border-slate-300 text-emerald-700 focus:ring-emerald-600">
                    @else
                        <input id="custom-field-{{ $field->id }}" name="custom_fields[{{ $field->key }}]" type="{{ $field->type }}" value="{{ old('custom_fields.'.$field->key, $account->custom_fields[$field->key] ?? '') }}" @required($field->required) @if ($field->type === 'text') maxlength="2000" @endif class="w-full border border-white/10 bg-slate-950 px-4 py-3 text-white focus:border-emerald-600 focus:outline-none">
                    @endif
                </div>
            @endforeach
        </div>
        <button class="console-button console-button-primary" type="submit">Save account <span aria-hidden="true">→</span></button>
    </form>
@endsection
