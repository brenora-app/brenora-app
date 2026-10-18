@extends('layouts.marketing')

@section('title', 'Brenora | Revenue intelligence for customer teams')
@section('description', 'Bring customer signals, evidence-backed AI insights, and human-reviewed commercial actions into one clear workspace with Brenora.')
@section('og_title', 'Brenora | Customer revenue intelligence for SaaS teams')
@section('canonical', route('home'))

@section('content')
    <section class="hero hero-home">
        <span class="parallax-object parallax-square parallax-square-one" data-parallax-speed="0.11" aria-hidden="true"></span>
        <span class="parallax-object parallax-square parallax-square-two" data-parallax-speed="-0.08" aria-hidden="true"></span>
        <div class="hero-inner">
            <div class="hero-copy">
                <p class="eyebrow eyebrow-light"><span class="eyebrow-square"></span> Customer revenue intelligence</p>
                <h1>Make every customer signal <span>mean something.</span></h1>
                <p class="hero-summary">Brenora connects account health, product activity, and commercial context — then helps your team turn the evidence into a clear next step.</p>
                <div class="hero-actions">
                    @guest
                        <a class="button button-primary" href="{{ route('register') }}">Start 14-day free trial <span aria-hidden="true">↗</span></a>
                    @else
                        @if (auth()->user()->trial_ends_at && auth()->user()->trial_ends_at->isPast() && ! auth()->user()->paid_until?->isFuture())
                            <a class="button button-primary" href="#contact">Talk to us <span aria-hidden="true">↗</span></a>
                        @else
                            <a class="button button-primary" href="{{ route('dashboard') }}">Open workspace <span aria-hidden="true">↗</span></a>
                        @endif
                    @endguest
                    <a class="button button-light-outline" href="#platform">See how it works <span aria-hidden="true">↓</span></a>
                </div>
                <a class="hero-contact-link" href="#contact">Not ready to sign up? Talk with us <span aria-hidden="true">↗</span></a>
                <div class="hero-proof">
                    <span>Evidence-linked insights</span>
                    <span>Human-reviewed decisions</span>
                    <span>ROLE-BASED ACCESS CONTROLS</span>
                </div>
            </div>

            <div class="hero-art">
                <svg class="signal-graphic" viewBox="0 0 680 520" role="img" aria-labelledby="homeGraphicTitle homeGraphicDesc">
                    <title id="homeGraphicTitle">Brenora turns customer signals into reviewed actions</title>
                    <desc id="homeGraphicDesc">An animated square-node diagram connects account activity, evidence, an AI draft, human review, and a commercial action.</desc>
                    <rect x="20" y="20" width="640" height="480" fill="#14232d" stroke="#45606b" stroke-width="1"/>
                    <path class="flow-line" d="M155 153 H281 M374 153 H501 M327 201 V324 M548 201 V324 M155 201 V382 H280 M374 374 H501"/>
                    <path class="flow-line flow-line-secondary" d="M327 201 V324 M155 201 V382 H280"/>
                    <g class="graphic-label">
                        <text x="48" y="63">ACCOUNT WORKSPACE / LIVE CONTEXT</text>
                        <text x="48" y="466">WORKFLOW OVERVIEW · HUMAN DECISION REQUIRED</text>
                    </g>
                    <g class="graphic-node node-account">
                        <rect x="78" y="105" width="154" height="96" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="96" y="138">01 / CUSTOMER</text>
                        <text x="96" y="166" class="node-title">Account context</text>
                        <text x="96" y="185" class="node-subtitle">Health · renewal · owner</text>
                    </g>
                    <g class="graphic-node node-signal">
                        <rect x="280" y="105" width="94" height="96" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="296" y="138">02 / DATA</text>
                        <text x="296" y="166" class="node-title">Signals</text>
                        <text x="296" y="185" class="node-subtitle">Product · CRM</text>
                    </g>
                    <g class="graphic-node node-evidence">
                        <rect x="280" y="324" width="94" height="96" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="295" y="356">03 / TRACE</text>
                        <text x="295" y="384" class="node-title">Evidence</text>
                        <text x="295" y="403" class="node-subtitle">Source-linked</text>
                    </g>
                    <g class="graphic-node node-ai">
                        <rect x="501" y="105" width="116" height="96" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="517" y="138">04 / ASSIST</text>
                        <text x="517" y="166" class="node-title">AI draft</text>
                        <text x="517" y="185" class="node-subtitle">Review-ready</text>
                    </g>
                    <g class="graphic-node node-review">
                        <rect x="501" y="324" width="116" height="96" fill="#24362f" stroke="#f0b35c"/>
                        <text x="517" y="356">05 / HUMAN</text>
                        <text x="517" y="384" class="node-title">Review</text>
                        <text x="517" y="403" class="node-subtitle">Approve · adjust</text>
                    </g>
                    <circle class="signal-pulse pulse-one" cx="252" cy="153" r="5" fill="#f0b35c"/>
                    <circle class="signal-pulse pulse-two" cx="452" cy="153" r="5" fill="#f0b35c"/>
                    <circle class="signal-pulse pulse-three" cx="450" cy="374" r="5" fill="#f0b35c"/>
                </svg>
                <p class="art-caption">A connected view of what’s happening — and why.</p>
            </div>
        </div>
        <div class="hero-bottom-rule"><span>BUILT FOR CUSTOMER SUCCESS</span><span>SALES LEADERSHIP</span><span>REVENUE OPERATIONS</span><span>01 — 03</span></div>
    </section>

    <section class="section section-paper" id="platform">
        <div class="section-inner">
            <div class="section-heading section-heading-row">
                <div>
                    <p class="eyebrow">The Brenora approach</p>
                    <h2>From scattered signals to a shared next step.</h2>
                </div>
                <p class="section-intro">Bring customer context together without losing the source, the reviewer, or the reasoning behind an action.</p>
            </div>
            <div class="feature-grid">
                <article class="feature-card">
                    <span class="feature-number">01</span>
                    <h3>See the whole account</h3>
                    <p>Keep health, renewal timing, ownership, and product signals connected in one workspace built for customer-facing teams.</p>
                    <span class="feature-mark" aria-hidden="true">↗</span>
                </article>
                <article class="feature-card">
                    <span class="feature-number">02</span>
                    <h3>Trace every insight</h3>
                    <p>Turn raw inputs into concise AI-assisted briefs that point back to their evidence and call out information that is missing.</p>
                    <span class="feature-mark" aria-hidden="true">↗</span>
                </article>
                <article class="feature-card">
                    <span class="feature-number">03</span>
                    <h3>Keep people in control</h3>
                    <p>Give your team a clear place to review, approve, and track a next step before it becomes a customer-facing action.</p>
                    <span class="feature-mark" aria-hidden="true">↗</span>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-sand parallax-section" id="workflow">
        <span class="parallax-object parallax-square parallax-square-three" data-parallax-speed="0.08" aria-hidden="true"></span>
        <div class="section-inner workflow-layout">
            <div class="workflow-copy">
                <p class="eyebrow">A practical workflow</p>
                <h2>Useful intelligence, not another alert.</h2>
                <p class="section-intro">A signal starts a conversation. Brenora helps your team see the evidence, review the context, and decide what to do next.</p>
                <a class="text-link" href="{{ route('product') }}">Explore product details <span aria-hidden="true">↗</span></a>
            </div>
            <div class="workflow-right">
                <div class="workflow-steps">
                    <article><span>01</span><div><h3>Collect context</h3><p>Bring product, account, and commercial signals together.</p></div></article>
                    <article><span>02</span><div><h3>Draft with evidence</h3><p>Summarize what changed and keep the sources in view.</p></div></article>
                    <article><span>03</span><div><h3>Review before action</h3><p>Let an accountable person decide the right next step.</p></div></article>
                </div>
                <div class="lottie-block" data-lottie-src="{{ asset('animations/signal-flow.json') }}" role="img" aria-label="Animated signal-to-review workflow"></div>
            </div>
        </div>
    </section>

    @include('marketing.partials.testimonials')
    @include('marketing.partials.pricing')
    @include('marketing.partials.faq')
    @include('marketing.partials.contact')
@endsection
