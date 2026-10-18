@extends('layouts.console', ['title' => 'Settings'])

@section('content')
    <section class="console-page-heading">
        <div><p class="console-eyebrow">PERSONAL WORKSPACE</p><h1>Settings</h1><p>Manage your profile, security and dashboard layout.</p></div>
    </section>

    <nav class="settings-tabs" aria-label="Settings sections">
        <a href="#profile-settings">Profile</a>
        <a href="#dashboard-settings">Dashboard layout</a>
        <a href="#security-settings">Security</a>
    </nav>

    <div class="settings-grid">
        <section id="profile-settings" class="console-panel settings-panel">
            <div class="console-panel-heading"><div><p class="console-eyebrow">PERSONAL DETAILS</p><h2>Profile information</h2></div></div>
            @include('profile.partials.update-profile-information-form')
        </section>

        <section id="dashboard-settings" class="console-panel settings-panel">
            <div class="console-panel-heading"><div><p class="console-eyebrow">YOUR OVERVIEW</p><h2>Dashboard layout</h2></div></div>
            <p class="console-muted">Choose which information panels appear on your overview. Your choices only affect your account.</p>
            @php($preferences = $user->dashboard_preferences ?? [])
            <form method="POST" action="{{ route('settings.dashboard.update') }}" class="console-form dashboard-preferences-form">
                @csrf
                @method('PATCH')
                @foreach ([
                    'metrics' => ['Portfolio metrics', 'Revenue, health and account status at a glance.'],
                    'accounts' => ['Recently active accounts', 'Quick access to your latest customer records.'],
                    'insights' => ['Latest insights', 'Evidence-based account observations.'],
                    'actions' => ['Commercial actions', 'Follow-ups and next steps for your team.'],
                ] as $section => [$label, $description])
                    <label class="console-switch-row">
                        <input type="hidden" name="sections[{{ $section }}]" value="0">
                        <input type="checkbox" name="sections[{{ $section }}]" value="1" @checked(($preferences[$section] ?? true))>
                        <span><strong>{{ $label }}</strong><small>{{ $description }}</small></span>
                    </label>
                @endforeach
                <button type="submit" class="console-button console-button-primary">Save dashboard layout <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <section id="security-settings" class="console-panel settings-panel">
            <div class="console-panel-heading"><div><p class="console-eyebrow">ACCOUNT SECURITY</p><h2>Update password</h2></div></div>
            @include('profile.partials.update-password-form')
        </section>

        <section class="console-panel settings-panel settings-danger">
            <div class="console-panel-heading"><div><p class="console-eyebrow">IRREVERSIBLE</p><h2>Delete account</h2></div></div>
            @include('profile.partials.delete-user-form')
        </section>
    </div>
@endsection
