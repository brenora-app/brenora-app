<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,nofollow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Workspace' }} | Brenora</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="console-shell antialiased" x-data="{ sidebarOpen: false }">
        <div class="console-frame">
            <button class="console-mobile-shade" type="button" aria-label="Close navigation" x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" x-cloak></button>
            <aside class="console-sidebar" :class="{ 'console-sidebar-open': sidebarOpen }" aria-label="Workspace sidebar">
                <a href="{{ route('dashboard') }}" class="console-brand">
                    <span class="console-brand-mark">B</span>
                    <span>BRENORA <small>INTELLIGENCE</small></span>
                </a>
                <div class="console-workspace">
                    <span class="console-workspace-icon">W</span>
                    <span><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(auth()->user()->role) }} workspace</small></span>
                    <span class="console-workspace-chevron" aria-hidden="true">⌄</span>
                </div>

                <p class="console-nav-label">WORKSPACE</p>
                <nav class="console-nav" aria-label="Main navigation">
                    <a href="{{ route('dashboard') }}" @class(['is-active' => request()->routeIs('dashboard')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h6v7H4zM14 4h6v4h-6zM14 12h6v8h-6zM4 15h6v5H4z"/></svg>
                        <span>Overview</span>
                    </a>
                    <a href="{{ route('accounts.index') }}" @class(['is-active' => request()->routeIs('accounts.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20V8l8-4 8 4v12M8 20v-6h8v6M8 9h.01M12 9h.01M16 9h.01"/></svg>
                        <span>Accounts</span>
                        <span class="console-nav-arrow" aria-hidden="true">↗</span>
                    </a>
                    <a href="{{ route('revenue.index') }}" @class(['is-active' => request()->routeIs('revenue.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h16M7 15l4-4 3 2 5-7"/><path d="M16 6h3v3"/></svg>
                        <span>Revenue</span>
                    </a>
                    @if (auth()->user()->canManageAccounts())
                        <a href="{{ route('custom-fields.index') }}" @class(['is-active' => request()->routeIs('custom-fields.*')])>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5zM9 8h6M9 12h6M9 16h3"/></svg>
                            <span>Custom fields</span>
                        </a>
                    @endif
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.index') }}" @class(['is-active' => request()->routeIs('admin.*')])>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 20 7v5c0 5-3 8-8 9-5-1-8-4-8-9V7zM9 12h6M12 9v6"/></svg>
                            <span>Administration</span>
                        </a>
                        <a href="{{ route('salesforce.index') }}" @class(['is-active' => request()->routeIs('salesforce.*')])>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4 20 8v8l-8 4-8-4V8zM12 4v8m8-4-8 4-8-4m8 4v8"/></svg>
                            <span>Integrations</span>
                        </a>
                        <a href="{{ route('activity.index') }}" @class(['is-active' => request()->routeIs('activity.*')])>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5"/></svg>
                            <span>Activity log</span>
                        </a>
                    @endif
                </nav>

                <div class="console-sidebar-bottom">
                    <p class="console-nav-label">PERSONAL</p>
                    <a href="{{ route('profile.edit') }}" @class(['console-settings-link', 'is-active' => request()->routeIs('profile.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM19 13.5l2 1.5-2 3.5-2.3-.8a8 8 0 0 1-2 .9L14 21h-4l-.7-2.4a8 8 0 0 1-2-.9L5 18.5 3 15l2-1.5a8 8 0 0 1 0-2L3 10l2-3.5 2.3.8a8 8 0 0 1 2-.9L10 4h4l.7 2.4a8 8 0 0 1 2 .9l2.3-.8 2 3.5-2 1.5a8 8 0 0 1 0 2Z"/></svg>
                        <span>Settings</span>
                    </a>
                    <div class="console-user">
                        <span class="console-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        <span class="console-user-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="console-logout" aria-label="Log out" title="Log out">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 5H5v14h5M14 8l4 4-4 4M8 12h10"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <div class="console-main-wrap">
                <header class="console-topbar">
                    <button type="button" class="console-menu-toggle" aria-label="Open navigation" @click="sidebarOpen = true">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="console-breadcrumb"><span>Workspace</span><span aria-hidden="true">/</span><strong>{{ $title ?? 'Overview' }}</strong></div>
                    <div class="console-top-actions">
                        <span class="console-live"><i></i> Live workspace</span>
                        <a href="{{ route('profile.edit') }}" class="console-top-avatar" aria-label="Open settings">{{ mb_substr(auth()->user()->name, 0, 1) }}</a>
                    </div>
                </header>

                <main class="console-main">
                    @if (auth()->user()->trial_ends_at)
                        <div role="status" class="console-trial-alert">
                            <span class="console-alert-icon" aria-hidden="true">i</span>
                            <p>
                                @if (auth()->user()->trial_ends_at->isPast())
                                    Your 14-day trial ended {{ auth()->user()->trial_ends_at->toFormattedDateString() }}.
                                    <a href="{{ route('home') }}#contact">Contact us about access.</a>
                                @else
                                    Your 14-day trial ends {{ auth()->user()->trial_ends_at->diffForHumans() }} ({{ auth()->user()->trial_ends_at->toFormattedDateString() }}).
                                    <a href="{{ route('home') }}#contact">Questions? Contact us.</a>
                                @endif
                            </p>
                        </div>
                    @endif
                    @if (session('status'))
                        <div class="console-toast console-toast-success" role="status" x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 6000)">
                            <span class="console-toast-dot" aria-hidden="true"></span>
                            <span>{{ session('status') }}</span>
                            <button type="button" aria-label="Dismiss notification" @click="show = false">×</button>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="console-toast console-toast-error" role="alert">
                            <span class="console-toast-dot" aria-hidden="true"></span>
                            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                            <button type="button" aria-label="Dismiss notification" onclick="this.parentElement.remove()">×</button>
                        </div>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
