@extends('layouts.marketing')

@section('title', 'Product | Brenora customer revenue intelligence')
@section('description', 'Explore Brenora’s evidence-linked account intelligence, AI-assisted summaries, and human review workflows for commercial teams.')
@section('og_title', 'Brenora Product | Evidence-first account intelligence')
@section('canonical', route('product'))

@section('content')
    <section class="hero hero-product">
        <span class="parallax-object parallax-square parallax-square-one" data-parallax-speed="0.11" aria-hidden="true"></span>
        <span class="parallax-object parallax-square parallax-square-two" data-parallax-speed="-0.08" aria-hidden="true"></span>
        <div class="hero-inner">
            <div class="hero-copy">
                <p class="eyebrow eyebrow-light"><span class="eyebrow-square"></span> Product / 01</p>
                <h1>Intelligence your team can <span>trace and trust.</span></h1>
                <p class="hero-summary">Bring customer signals, revenue context, and evidence-linked AI drafts into one governed workflow — with a person at every decision point.</p>
                <div class="hero-actions">
                    <a class="button button-primary" href="#capabilities">Explore capabilities <span aria-hidden="true">↓</span></a>
                    @guest
                        <a class="button button-light-outline" href="{{ route('register') }}">Start 14-day free trial <span aria-hidden="true">↗</span></a>
                    @else
                        @if (auth()->user()->trial_ends_at && auth()->user()->trial_ends_at->isPast() && ! auth()->user()->paid_until?->isFuture())
                            <a class="button button-light-outline" href="#contact">Talk to us <span aria-hidden="true">↗</span></a>
                        @else
                            <a class="button button-light-outline" href="{{ route('dashboard') }}">Open workspace <span aria-hidden="true">↗</span></a>
                        @endif
                    @endguest
                </div>
                <a class="hero-contact-link" href="#contact">Talk through your workflow <span aria-hidden="true">↗</span></a>
                <div class="hero-proof">
                    <span>Optional local Ollama</span>
                    <span>Source-aware outputs</span>
                    <span>Human approval states</span>
                </div>
            </div>
            <div class="hero-art product-art">
                <svg class="signal-graphic" viewBox="0 0 680 520" role="img" aria-labelledby="productGraphicTitle productGraphicDesc">
                    <title id="productGraphicTitle">Brenora product workflow architecture</title>
                    <desc id="productGraphicDesc">Solid-color square blocks show incoming customer data, evidence checks, a private AI service, human review, and a recorded action.</desc>
                    <rect x="20" y="20" width="640" height="480" fill="#14232d" stroke="#45606b"/>
                    <path class="flow-line" d="M191 132 H273 M407 132 H489 M340 177 V275 M340 365 V408 H480"/>
                    <path class="flow-line flow-line-secondary" d="M191 132 H273 M407 132 H489"/>
                    <g class="graphic-label">
                        <text x="48" y="63">BRENORA / SYSTEM OVERVIEW</text>
                        <text x="48" y="466">APPLICATION CONTROLS REMAIN SERVER-SIDE</text>
                    </g>
                    <g class="graphic-node">
                        <rect x="56" y="91" width="135" height="86" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="72" y="120">01 / INPUT</text>
                        <text x="72" y="148" class="node-title">Account data</text>
                        <text x="72" y="165" class="node-subtitle">Signals · evidence</text>
                    </g>
                    <g class="graphic-node">
                        <rect x="273" y="91" width="134" height="86" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="290" y="120">02 / VALIDATE</text>
                        <text x="290" y="148" class="node-title">Evidence</text>
                        <text x="290" y="165" class="node-subtitle">Schema · sources</text>
                    </g>
                    <g class="graphic-node">
                        <rect x="489" y="91" width="135" height="86" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="505" y="120">03 / GENERATE</text>
                        <text x="505" y="148" class="node-title">AI service</text>
                        <text x="505" y="165" class="node-subtitle">Adapter boundary</text>
                    </g>
                    <g class="graphic-node">
                        <rect x="273" y="275" width="134" height="90" fill="#24362f" stroke="#f0b35c"/>
                        <text x="290" y="305">04 / HUMAN</text>
                        <text x="290" y="334" class="node-title">Review gate</text>
                        <text x="290" y="352" class="node-subtitle">Approve · reject</text>
                    </g>
                    <g class="graphic-node">
                        <rect x="480" y="365" width="144" height="86" fill="#1c343e" stroke="#70d4c0"/>
                        <text x="497" y="394">05 / RECORD</text>
                        <text x="497" y="422" class="node-title">Action log</text>
                        <text x="497" y="439" class="node-subtitle">Status · audit trail</text>
                    </g>
                    <circle class="signal-pulse pulse-one" cx="232" cy="132" r="5" fill="#f0b35c"/>
                    <circle class="signal-pulse pulse-two" cx="448" cy="132" r="5" fill="#f0b35c"/>
                    <circle class="signal-pulse pulse-three" cx="445" cy="408" r="5" fill="#f0b35c"/>
                </svg>
                <p class="art-caption">A guarded path from signal to accountable action.</p>
            </div>
        </div>
        <div class="hero-bottom-rule"><span>ACCOUNT CONTEXT</span><span>EVIDENCE LINKS</span><span>HUMAN APPROVAL</span><span>02 — 03</span></div>
    </section>

    <section class="section section-paper" id="capabilities">
        <div class="section-inner">
            <div class="section-heading">
                <p class="eyebrow">Core capabilities</p>
                <h2>One calm place to see what matters.</h2>
                <p class="section-intro">Designed to help customer teams connect the dots without handing judgment over to automation.</p>
            </div>
            <div class="capability-grid">
                <article class="capability-card capability-wide">
                    <span class="feature-number">01 / CUSTOMER CONTEXT</span>
                    <h3>Account signals with their source attached.</h3>
                    <p>Keep account ownership, renewal context, health, and signals together. Each item stays associated with its account and source so teams can verify what they see.</p>
                    <div class="signal-list" aria-label="Account signal categories">
                        <div><span>PRODUCT USAGE</span><strong>Source attached</strong><i>↗</i></div>
                        <div><span>RENEWAL CONTEXT</span><strong>Owner visible</strong><i>↗</i></div>
                        <div><span>ACCOUNT HEALTH</span><strong>Updated record</strong><i>↗</i></div>
                    </div>
                </article>
                <article class="capability-card" id="review">
                    <span class="feature-number">02 / AI-ASSISTED</span>
                    <h3>Briefings with useful boundaries.</h3>
                    <p>Generate short structured drafts with evidence references and missing-information flags. A model output remains a draft until a person reviews it.</p>
                    <span class="capability-icon" aria-hidden="true">AI<span>↗</span></span>
                </article>
                <article class="capability-card">
                    <span class="feature-number">03 / GOVERNANCE</span>
                    <h3>Approvals and audit trails by design.</h3>
                    <p>Keep review states explicit, control access on the server, and record who changed an action and when.</p>
                    <span class="capability-icon" aria-hidden="true">✓<span>01</span></span>
                </article>
            </div>
        </div>
    </section>

    <section class="section section-ink" id="architecture">
        <span class="parallax-object parallax-square parallax-square-two" data-parallax-speed="0.08" aria-hidden="true"></span>
        <div class="section-inner architecture-layout">
            <div>
                <p class="eyebrow eyebrow-light">Architecture / 02</p>
                <h2>Flexible AI. Firm application controls.</h2>
                <p class="section-intro">The product keeps AI services behind a clear boundary. Authentication, validation, access control, and business actions remain in the application.</p>
            </div>
            <div class="architecture-list">
                <article><span>01</span><div><h3>PHP and Laravel application</h3><p>Server-side authentication, role checks, validation, web workflows, and authorized integration calls.</p></div></article>
                <article><span>02</span><div><h3>SQL workspace storage</h3><p>MySQL-backed account, signal, contact, consent, forecast, review, and activity records.</p></div></article>
                <article><span>03</span><div><h3>Controlled service connections</h3><p>Optional local Ollama model calls and a manually configured, read-only Salesforce import. External services are not assumed to be connected.</p></div></article>
                <article><span>04</span><div><h3>Human decision layer</h3><p>Evidence-linked outputs remain drafts; authorized people review sensitive customer work and record actions.</p></div></article>
            </div>
        </div>
        <div class="section-inner nvidia-note">
            <span class="nvidia-index">NVIDIA OPTIONS / CONCEPTUAL</span>
            <p>NeMo Guardrails, NIM, RAPIDS, and Triton are technology options for future evaluation only. None is represented as deployed, enabled, or required by the current Brenora prototype.</p>
        </div>
        <div class="section-inner product-animation">
            <div>
                <p class="eyebrow eyebrow-light">The review loop</p>
                <h2>Context in. Human decision out.</h2>
            </div>
            <div class="lottie-block" data-lottie-src="{{ asset('animations/signal-flow.json') }}" role="img" aria-label="Animated signal-to-review workflow"></div>
        </div>
    </section>

    <section class="section section-paper" id="application-preview">
        <div class="section-inner application-preview">
            <div class="section-heading">
                <p class="eyebrow">Application preview / 03</p>
                <h2>A workspace organized around evidence.</h2>
                <p class="section-intro">The live application uses connected workspace data. This preview describes the interface without presenting invented customer records or metrics.</p>
            </div>
            <div class="application-preview-frame" aria-label="Brenora workspace preview">
                <div class="application-preview-bar"><span></span><span></span><span></span><strong>BRENORA / WORKSPACE</strong></div>
                <div class="application-preview-body">
                    <aside><b>WORKSPACE</b><span>Portfolio overview</span><span>Accounts and contacts</span><span>Revenue pipeline</span><span>AI review queue</span><span>Commercial actions</span></aside>
                    <div class="application-preview-content">
                        <div class="application-preview-title"><span><small>PORTFOLIO / OVERVIEW</small><strong>Customer context, at a glance.</strong></span><i>Live workspace data</i></div>
                        <div class="application-preview-metrics"><article><small>PORTFOLIO HEALTH</small><b>Recorded account health</b><span>Assessed accounts only</span></article><article><small>REVENUE EXPOSURE</small><b>At-risk account value</b><span>Based on entered account records</span></article><article><small>OPEN WORK</small><b>Assigned actions</b><span>Owners and due dates</span></article></div>
                        <div class="application-preview-empty"><strong>Account evidence stays in context.</strong><span>Signals · source freshness · renewal details · reviewer activity</span></div>
                    </div>
                </div>
            </div>
            <a class="text-link" href="{{ route('login') }}">Sign in to the workspace <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <section class="section section-sand">
        <div class="section-inner closing-banner">
            <div><p class="eyebrow">Ready to see it in context?</p><h2>Start with the workflow your team already knows.</h2></div>
            <a class="button button-ink" href="#contact">Talk to Brenora <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    @include('marketing.partials.testimonials')
    @include('marketing.partials.pricing')
    @include('marketing.partials.faq')
    @include('marketing.partials.contact')
@endsection
