@extends('layouts.console', ['title' => 'Salesforce integration'])

@section('content')
    <section class="console-page-heading">
        <div><p class="console-eyebrow">READ-ONLY CRM CONNECTOR</p><h1>Salesforce</h1><p>Import account profiles and contact records into the shared Brenora workspace. Sync is manual and one-way; Brenora does not write changes back to Salesforce.</p></div>
        <a href="{{ route('admin.index') }}" class="console-button console-button-secondary">Administration <span aria-hidden="true">↗</span></a>
    </section>

    @if (! $configured)
        <section class="integration-setup-notice">
            <span class="integration-notice-mark">!</span>
            <div><strong>Connect credentials are not configured</strong><p>Set <code>SALESFORCE_CLIENT_ID</code>, <code>SALESFORCE_CLIENT_SECRET</code>, and the exact callback URL in the server environment. Register the callback URL and API / refresh-token scopes in a Salesforce connected app. Keep the secret out of source control and chat.</p></div>
        </section>
    @endif

    <div class="integration-grid">
        <section class="console-panel p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">CONNECTION STATUS</p><h2>{{ $connection ? 'Connected' : 'Not connected' }}</h2></div><span class="integration-status {{ $connection ? 'integration-status-on' : 'integration-status-off' }}"><i></i>{{ $connection ? 'Connected' : 'Not connected' }}</span></div>
            @if ($connection)
                <dl class="integration-details">
                    <div><dt>Salesforce instance</dt><dd>{{ parse_url($connection->instance_url, PHP_URL_HOST) }}</dd></div>
                    <div><dt>Last sync</dt><dd>{{ $connection->last_synced_at?->format('M j, Y g:i A') ?? 'Not synced yet' }}</dd></div>
                    <div><dt>Authorization</dt><dd>OAuth tokens encrypted at rest</dd></div>
                </dl>
                <div class="integration-actions">
                    <form method="POST" action="{{ route('salesforce.sync') }}">@csrf<button class="console-button console-button-primary" type="submit">Sync Salesforce now <span aria-hidden="true">↻</span></button></form>
                    <form method="POST" action="{{ route('salesforce.disconnect') }}" onsubmit="return confirm('Remove Salesforce credentials? Imported Brenora records will remain.')">@csrf @method('DELETE')<button class="console-button console-button-danger" type="submit">Disconnect</button></form>
                </div>
            @else
                <p class="console-muted">The connector requests Salesforce API and refresh-token authorization, reads standard Account and Contact fields, and never completes writes in Salesforce.</p>
                <form method="GET" action="{{ route('salesforce.connect') }}" class="mt-5"><button class="console-button console-button-primary" type="submit" @disabled(! $configured)>Connect Salesforce <span aria-hidden="true">↗</span></button></form>
            @endif
        </section>

        <section class="console-panel p-6">
            <div class="console-panel-heading"><div><p class="console-eyebrow">DATA GOVERNANCE</p><h2>What sync imports</h2></div></div>
            <ul class="integration-import-list">
                <li><span>01</span><div><strong>Account profile</strong><small>Name, industry, owner and annual revenue.</small></div></li>
                <li><span>02</span><div><strong>Contact records</strong><small>Name, title, email and phone. Imported contacts begin with consent pending.</small></div></li>
                <li><span>03</span><div><strong>Review before use</strong><small>Imported accounts begin as needs review; renewal, health, consent and opportunities are not inferred.</small></div></li>
                <li><span>04</span><div><strong>Manual sync</strong><small>Only an administrator starts each sync. Removed Salesforce rows are not deleted from Brenora.</small></div></li>
            </ul>
        </section>
    </div>
    <section class="integration-boundary"><strong>Organization-wide connection:</strong> Brenora currently uses one shared workspace and one Salesforce connection. Tenant-isolated credentials and per-customer data scopes are not supported yet.</section>
@endsection
