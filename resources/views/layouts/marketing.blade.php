<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="@yield('description', 'Brenora turns customer signals into evidence-backed commercial action.')">
        <meta name="robots" content="index,follow">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta property="og:title" content="@yield('og_title', 'Brenora | Customer revenue intelligence')">
        <meta property="og:description" content="@yield('description', 'Brenora turns customer signals into evidence-backed commercial action.')">
        <meta property="og:type" content="website">
        <title>@yield('title', 'Brenora | Customer revenue intelligence')</title>
        <link rel="canonical" href="@yield('canonical', route('home'))">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="marketing-site">
        <header class="site-header">
            <div class="site-header-inner">
                <a class="brand" href="{{ route('home') }}" aria-label="Brenora home">
                    <span class="brand-mark" aria-hidden="true">B</span>
                    <span>BRENORA</span>
                </a>
                <nav class="site-nav" aria-label="Main navigation">
                    <a href="{{ route('home') }}">Home</a>
                    <details class="mega-menu">
                        <summary>Product <span aria-hidden="true">⌄</span></summary>
                        <div class="mega-panel">
                            <div class="mega-panel-inner">
                                <div class="mega-panel-lead">
                                    <p class="eyebrow">The Brenora workspace</p>
                                    <h2>Go from customer signal to reviewed action.</h2>
                                    <a class="text-link" href="{{ route('product') }}">Explore the product <span aria-hidden="true">↗</span></a>
                                </div>
                                <div class="mega-panel-links">
                                    <a href="{{ route('product') }}#capabilities"><span>01</span><span><strong>Account intelligence</strong><small>Connect customer health and context</small></span></a>
                                    <a href="{{ route('product') }}#architecture"><span>02</span><span><strong>Evidence-linked insights</strong><small>Understand the source behind a draft</small></span></a>
                                    <a href="{{ route('product') }}#review"><span>03</span><span><strong>Human review</strong><small>Keep decisions with your team</small></span></a>
                                </div>
                                <div class="mega-panel-cta">
                                    <span>14 days · No payment details</span>
                                    @guest
                                        <a class="button button-primary button-small" href="{{ route('register') }}">Start free trial <span aria-hidden="true">↗</span></a>
                                    @else
                                        <a class="button button-primary button-small" href="{{ route('dashboard') }}">Open workspace <span aria-hidden="true">↗</span></a>
                                    @endguest
                                </div>
                            </div>
                        </div>
                    </details>
                    <a class="demo-nav-link" href="{{ route('demo.deal-health') }}"><span class="demo-nav-indicator" aria-hidden="true"></span>Try our demo</a>
                    <a href="{{ route('pricing') }}">Pricing</a>
                    <a href="{{ route('about') }}">About</a>
                    <a href="{{ route('home') }}#stories">Reviews</a>
                    <a href="{{ route('home') }}#faq">FAQ</a>
                </nav>
                <div class="header-actions">
                    @auth
                        @if (auth()->user()->trial_ends_at && auth()->user()->trial_ends_at->isPast() && ! auth()->user()->paid_until?->isFuture())
                            <a class="button button-primary button-small" href="{{ route('home') }}#contact">Contact us</a>
                        @else
                            <a class="button button-primary button-small" href="{{ route('dashboard') }}">Open dashboard</a>
                        @endif
                    @else
                        <a class="button button-outline button-small" href="{{ route('login') }}">Sign in</a>
                        <a class="button button-primary button-small header-trial" href="{{ route('register') }}">Start free trial <span aria-hidden="true">↗</span></a>
                    @endauth
                </div>
            </div>
        </header>

        <main>
            @if (session('trial_expired'))
                <div class="trial-notice" role="status">
                    Your 14-day free trial has ended. Contact Brenora to discuss continued access.
                    <a href="{{ route('home') }}#contact">Contact us <span aria-hidden="true">↗</span></a>
                </div>
            @endif
            @yield('content')
        </main>

        <footer class="site-footer">
            <div class="footer-main">
                <div class="footer-about">
                    <a class="brand footer-brand" href="{{ route('home') }}">
                        <span class="brand-mark" aria-hidden="true">B</span>
                        <span>BRENORA</span>
                    </a>
                    <p>Customer and revenue intelligence built around evidence, clear workflows, and human decisions.</p>
                    @guest
                        <a class="footer-trial-link" href="{{ route('register') }}">Start your 14-day free trial <span aria-hidden="true">↗</span></a>
                    @else
                        <a class="footer-trial-link" href="{{ route('home') }}#contact">Questions about access? Contact us <span aria-hidden="true">↗</span></a>
                    @endguest
                </div>
                <div class="footer-column">
                    <h2>Explore</h2>
                    <a href="{{ route('product') }}">Product overview</a>
                    <a href="{{ route('product') }}#capabilities">Capabilities</a>
                    <a href="{{ route('product') }}#architecture">Architecture</a>
                    <a href="{{ route('pricing') }}">Pricing</a>
                </div>
                <div class="footer-column">
                    <h2>Company</h2>
                    <a href="{{ route('about') }}">About Brenora</a>
                    <a href="{{ route('home') }}#stories">Product principles</a>
                    <a href="{{ route('home') }}#faq">Frequently asked</a>
                    <a href="{{ route('home') }}#contact">Contact</a>
                </div>
                <div class="footer-column footer-office">
                    <h2>Sri Lanka</h2>
                    <p class="footer-entity">Brenora (Pvt) Ltd</p>
                    <address>
                        <span class="footer-contact-line">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 18s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"/><circle cx="10" cy="8" r="2"/></svg>
                            <span>24 New Parliament Road<br>Battaramulla, Sri Lanka</span>
                        </span>
                        <a class="footer-contact-line" href="tel:+94112785316">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M6 3H4a1 1 0 0 0-1 1c0 7.2 5.8 13 13 13a1 1 0 0 0 1-1v-2l-4-2-2 2a11 11 0 0 1-4-4l2-2-2-4H6Z"/></svg>
                            <span>+94 11 278 5316</span>
                        </a>
                    </address>
                    <a class="footer-contact-line footer-web-link" href="https://brenora.net/">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a11 11 0 0 1 0 14M10 3a11 11 0 0 0 0 14"/></svg>
                        <span>brenora.net</span>
                    </a>
                </div>
                <div class="footer-column footer-office">
                    <h2>United States</h2>
                    <p class="footer-entity">Brenora LLC</p>
                    <address>
                        <span class="footer-contact-line">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M10 18s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10Z"/><circle cx="10" cy="8" r="2"/></svg>
                            <span>800 Wilshire Boulevard<br>Los Angeles, CA 90017, USA</span>
                        </span>
                        <a class="footer-contact-line" href="tel:+12136473928">
                            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="M6 3H4a1 1 0 0 0-1 1c0 7.2 5.8 13 13 13a1 1 0 0 0 1-1v-2l-4-2-2 2a11 11 0 0 1-4-4l2-2-2-4H6Z"/></svg>
                            <span>+1 213 647 3928</span>
                        </a>
                    </address>
                    <a class="footer-contact-line footer-web-link" href="https://brenora.net/">
                        <svg viewBox="0 0 20 20" aria-hidden="true"><circle cx="10" cy="10" r="7"/><path d="M3 10h14M10 3a11 11 0 0 1 0 14M10 3a11 11 0 0 0 0 14"/></svg>
                        <span>brenora.net</span>
                    </a>
                </div>
                <div class="footer-column">
                    <h2>Workspace</h2>
                    @guest
                        <a href="{{ route('register') }}">Start free trial</a>
                    @else
                        @if (auth()->user()->trial_ends_at && auth()->user()->trial_ends_at->isPast() && ! auth()->user()->paid_until?->isFuture())
                            <a href="{{ route('home') }}#contact">Contact about access</a>
                        @else
                            <a href="{{ route('dashboard') }}">Open workspace</a>
                        @endif
                    @endguest
                    <a href="{{ route('login') }}">Sign in</a>
                    <p>AI drafts remain subject to human review. NVIDIA NeMo Guardrails integration is planned, not currently enabled.</p>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© {{ now()->year }} Brenora</span>
                <span>Founded 22 April 2024 · Product information reflects the current prototype.</span>
                <a href="{{ route('login') }}">Workspace sign in <span aria-hidden="true">↗</span></a>
            </div>
        </footer>
        <div class="marketing-chat" x-data="{ open: {{ session('contact_submitted') || $errors->any() ? 'true' : 'false' }} }">
            <section class="marketing-chat-panel" x-cloak x-show="open" x-transition.opacity id="marketing-chat-panel" aria-labelledby="marketing-chat-title">
                <div class="marketing-chat-heading">
                    <span class="chat-status-mark" aria-hidden="true"></span>
                    <div><h2 id="marketing-chat-title">Talk with Brenora</h2><p>Send a note to our team.</p></div>
                    <button type="button" class="chat-close" @click="open = false" aria-label="Close chat">×</button>
                </div>
                @if (session('contact_submitted'))
                    <p class="chat-success" role="status">Thanks for reaching out. Your message is with our team.</p>
                @endif
                @if ($errors->any())
                    <p class="chat-error" role="alert">Please check the details and try sending again.</p>
                @endif
                <form class="chat-form" method="POST" action="{{ route('contact.store') }}">
                    @csrf
                    <input type="hidden" name="topic" value="{{ old('topic', request('topic', 'other')) }}">
                    <label><span>Your name</span><input name="name" autocomplete="name" value="{{ old('name') }}" maxlength="120" required></label>
                    <label><span>Work email</span><input name="email" type="email" autocomplete="email" value="{{ old('email') }}" maxlength="254" required></label>
                    <label><span>How can we help?</span><textarea name="message" rows="3" minlength="10" maxlength="5000" required>{{ old('message') }}</textarea></label>
                    <button class="button button-ink" type="submit">Send message <span aria-hidden="true">↗</span></button>
                    <small>We’ll use your details only to respond to this request.</small>
                </form>
            </section>
            <button class="marketing-chat-launcher" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="marketing-chat-panel">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5h16v11H11l-5 3v-3H4z"/><path d="M8 10h8M8 13h5"/></svg>
                <span>Chat with us</span>
            </button>
        </div>
        <button class="scroll-to-top" type="button" aria-label="Scroll to top" title="Scroll to top" hidden>
            <svg viewBox="0 0 20 20" aria-hidden="true"><path d="m4 12 6-6 6 6M10 6v10"/></svg>
        </button>
        <span class="marketing-pointer" aria-hidden="true"><svg viewBox="0 0 24 32"><path d="M3 2v22l6-6 4 10 4-2-4-9 8-1L3 2Z"/></svg></span>
        @if (config('services.paypal.client_id') && config('services.paypal.secret'))
            <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode(config('services.paypal.client_id')) }}&currency=USD&intent=capture"></script>
        @endif
    </body>
</html>
