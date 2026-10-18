<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex,nofollow">
        <title>{{ request()->routeIs('register') ? 'Create your workspace' : 'Sign in' }} | Brenora</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="auth-page antialiased">
        @php($isRegister = request()->routeIs('register'))
        <main class="auth-frame">
            <section class="auth-story" aria-labelledby="auth-story-title">
                <a href="{{ route('home') }}" class="auth-brand" aria-label="Brenora home">
                    <span class="auth-brand-mark">B</span>
                    <span>BRENORA</span>
                </a>

                <div class="auth-story-copy">
                    <p class="auth-eyebrow">Customer intelligence, made actionable</p>
                    <h1 id="auth-story-title">{{ $isRegister ? 'Build a clearer view of every customer.' : 'Know where every customer stands.' }}</h1>
                    <p>{{ $isRegister ? 'Bring account health, real signals and accountable next steps into one focused workspace.' : 'A focused workspace for account health, evidence-backed signals and the next best action.' }}</p>
                </div>

                <div class="auth-graphic" aria-label="Animated customer signal visualization">
                    <svg viewBox="0 0 600 390" role="img" aria-labelledby="auth-graphic-title">
                        <title id="auth-graphic-title">Account signals flowing into a clear health overview</title>
                        <path class="auth-grid-line" d="M0 70H600M0 140H600M0 210H600M0 280H600M0 350H600M80 0V390M180 0V390M280 0V390M380 0V390M480 0V390"/>
                        <path class="auth-flow" d="M72 276C151 276 147 139 239 139S330 257 405 208 459 112 535 112"/>
                        <path class="auth-flow auth-flow-secondary" d="M72 324C166 324 173 248 238 248S322 84 393 84 463 165 535 165"/>
                        <g class="auth-node auth-node-one"><circle cx="72" cy="276" r="10"/><circle cx="72" cy="276" r="18"/></g>
                        <g class="auth-node auth-node-two"><circle cx="239" cy="139" r="10"/><circle cx="239" cy="139" r="18"/></g>
                        <g class="auth-node auth-node-three"><circle cx="405" cy="208" r="10"/><circle cx="405" cy="208" r="18"/></g>
                        <rect x="423" y="245" width="122" height="86" class="auth-graphic-card"/>
                        <path class="auth-bar" d="M444 307V286M465 307V273M486 307V293M507 307V263M528 307V279"/>
                        <path class="auth-card-line" d="M444 319H528"/>
                        <text x="444" y="265" class="auth-graphic-label">HEALTH SIGNALS</text>
                    </svg>
                </div>

                <div class="auth-proof">
                    <span class="auth-proof-mark" aria-hidden="true">+</span>
                    <p><strong>One customer view.</strong> Signals, decisions and follow-through together.</p>
                </div>
                <div class="auth-story-footer"><a href="{{ route('about') }}">About Brenora</a><span>·</span><a href="{{ route('product') }}">Explore the product</a></div>
            </section>

            <section class="auth-panel" aria-labelledby="auth-form-title">
                <div class="auth-panel-top">
                    <span>{{ $isRegister ? 'NEW WORKSPACE' : 'YOUR WORKSPACE' }}</span>
                    <a href="{{ $isRegister ? route('login') : route('register') }}">{{ $isRegister ? 'Sign in' : 'Create account' }} <span aria-hidden="true">↗</span></a>
                </div>
                <div class="auth-form-wrap">
                    <p class="auth-eyebrow">{{ $isRegister ? 'Start with a 14-day trial' : 'Welcome back' }}</p>
                    <h2 id="auth-form-title">{{ $isRegister ? 'Create your account' : 'Sign in to Brenora' }}</h2>
                    <p class="auth-form-intro">{{ $isRegister ? 'No payment details required. Set up your workspace in a few steps.' : 'Pick up where your team left off.' }}</p>
                    {{ $slot }}
                    <p class="auth-legal">By continuing, you agree to use Brenora responsibly and keep your account credentials secure.</p>
                </div>
                <div class="auth-panel-bottom"><a href="{{ route('home') }}">← Back to Brenora</a><span>Secure workspace access</span></div>
            </section>
        </main>
    </body>
</html>
