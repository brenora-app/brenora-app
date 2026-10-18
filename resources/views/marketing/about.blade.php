@extends('layouts.marketing')

@section('title', 'About Brenora | Evidence-led customer workflows')
@section('description', 'Learn how Brenora brings account context, evidence-linked AI assistance, and human review together for customer and revenue teams.')
@section('og_title', 'About Brenora | Customer and revenue intelligence')
@section('canonical', route('about'))

@section('content')
    <section class="hero hero-about">
        <span class="parallax-object parallax-square parallax-square-one" data-parallax-speed="0.11" aria-hidden="true"></span>
        <span class="parallax-object parallax-square parallax-square-two" data-parallax-speed="-0.08" aria-hidden="true"></span>
        <div class="hero-inner">
            <div class="hero-copy">
                <p class="eyebrow eyebrow-light"><span class="eyebrow-square"></span> About Brenora</p>
                <h1>Customer context should lead to <span>better decisions.</span></h1>
                <p class="hero-summary">Brenora is a customer and revenue intelligence prototype for teams that need to understand what is happening in an account, see the evidence, and agree on a responsible next step.</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="{{ route('product') }}">See the product <span aria-hidden="true">↗</span></a>
                    <a class="button button-light-outline" href="{{ route('home') }}#contact">Contact Brenora <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <div class="about-graphic-panel">
                <div class="about-graphic-heading"><span>HOW THE WORKSPACE CONNECTS</span><span>01 / 03</span></div>
                <div class="about-graphic-flow">
                    <span>Account</span><i aria-hidden="true">→</i><span>Evidence</span><i aria-hidden="true">→</i><span>Review</span>
                </div>
                <div class="lottie-block" data-lottie-src="{{ asset('animations/signal-flow.json') }}" role="img" aria-label="Animated flow from account data to evidence review"></div>
                <p>AI prepares a reviewable draft. A person owns the decision.</p>
            </div>
        </div>
    </section>

    <section class="section section-paper">
        <div class="section-inner about-content">
            <div>
                <p class="eyebrow">What the product does today</p>
                <h2>A connected workflow for customer and revenue context.</h2>
            </div>
            <div class="about-copy">
                <p>The application stores customer accounts and their signals, associates insights with evidence, and routes commercial actions through reviewable statuses. Teams can work from an account view instead of stitching together context from disconnected notes.</p>
                <p>When the optional local Ollama service is configured, the app can draft structured insights from selected account context and signals. The application validates the output before saving it as a draft; a reviewer makes the decision. The model does not directly modify customer records or complete actions.</p>
                <a class="text-link" href="{{ route('product') }}#capabilities">See the product capabilities <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>

    <section class="section section-sand">
        <div class="section-inner">
            <div class="section-heading">
                <p class="eyebrow">Product principles</p>
                <h2>Useful context, visible sources, accountable people.</h2>
            </div>
            <div class="about-principles">
                <article><span>01</span><h3>Trace the evidence</h3><p>Keep source context close to signals and AI-assisted drafts so a reviewer can check the basis for a recommendation.</p></article>
                <article><span>02</span><h3>Keep review explicit</h3><p>Generated content remains a draft. Commercial actions follow states and approval checks rather than model instructions.</p></article>
                <article><span>03</span><h3>Be clear about what is planned</h3><p>The app currently supports a local Ollama integration. NVIDIA NeMo Guardrails is a possible future integration, not a deployed feature.</p></article>
            </div>
        </div>
    </section>

    <section class="section section-ink">
        <div class="section-inner about-status-layout">
            <div>
                <p class="eyebrow eyebrow-light">Current product status</p>
                <h2>Built as a working prototype.</h2>
            </div>
            <div>
                <p class="section-intro">Brenora includes registration, role-based access, account and signal management, evidence-associated insights, human review, and action history. It is a working prototype and is not yet a production multi-tenant service.</p>
                @guest
                    <a class="button button-primary about-trial-button" href="{{ route('register') }}">Explore with a 14-day trial <span aria-hidden="true">↗</span></a>
                @else
                    @if (auth()->user()->trial_ends_at && auth()->user()->trial_ends_at->isPast() && ! auth()->user()->paid_until?->isFuture())
                        <a class="button button-primary about-trial-button" href="{{ route('home') }}#contact">Contact about access <span aria-hidden="true">↗</span></a>
                    @else
                        <a class="button button-primary about-trial-button" href="{{ route('dashboard') }}">Open the workspace <span aria-hidden="true">↗</span></a>
                    @endif
                @endguest
            </div>
        </div>
    </section>

    @include('marketing.partials.testimonials')
    @include('marketing.partials.pricing')
    @include('marketing.partials.faq')
    @include('marketing.partials.contact')
@endsection
