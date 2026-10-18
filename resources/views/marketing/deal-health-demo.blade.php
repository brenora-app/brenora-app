@extends('layouts.marketing')

@section('title', 'AI Deal Health & Evidence Auditor | Brenora')
@section('description', 'Explore Brenora deal health, evidence-linked customer signals, and suggested follow-up actions in an interactive public demo.')
@section('og_title', 'Deal Health & Evidence Auditor | Brenora')
@section('canonical', route('demo.deal-health'))

@section('content')
    <section class="deal-demo-hero">
        <div class="section-inner deal-demo-hero-grid">
            <div>
                <p class="eyebrow eyebrow-light"><span class="eyebrow-square"></span> PUBLIC PLAYGROUND / 01</p>
                <h1>See the evidence behind a deal-health signal.</h1>
                <p>Paste a short customer note and explore how account evidence can be turned into a reviewable next step.</p>
                <div class="deal-demo-hero-actions">
                    <a class="button button-primary" href="#playground">Try the auditor <span aria-hidden="true">↓</span></a>
                    <a class="button button-light-outline" href="#architecture">Explore the architecture <span aria-hidden="true">↗</span></a>
                </div>
                <p class="deal-demo-honesty">Explore an evidence-first deal review and see how customer context can be organized into actionable signals.</p>
            </div>
            <div class="deal-demo-hero-graphic" aria-hidden="true">
                <svg viewBox="0 0 520 370">
                    <rect x="14" y="14" width="492" height="342" fill="#14232d" stroke="#45606b"/>
                    <text x="39" y="54" class="deal-svg-label">DEAL REVIEW / EVIDENCE TRACE</text>
                    <path d="M167 154h70m115 0h36M286 194v72" class="deal-svg-line"/>
                    <rect x="42" y="108" width="125" height="92" fill="#1c343e" stroke="#70d4c0"/>
                    <rect x="237" y="108" width="99" height="92" fill="#1c343e" stroke="#70d4c0"/>
                    <rect x="388" y="108" width="92" height="92" fill="#24362f" stroke="#f0b35c"/>
                    <rect x="237" y="266" width="99" height="58" fill="#273932" stroke="#70d4c0"/>
                    <text x="58" y="137" class="deal-svg-label">01 / CUSTOMER</text>
                    <text x="58" y="164" class="deal-svg-title">Raw notes</text>
                    <text x="253" y="137" class="deal-svg-label">02 / REVIEW</text>
                    <text x="253" y="164" class="deal-svg-title">Evidence</text>
                    <text x="400" y="137" class="deal-svg-label">03 / SIGNAL</text>
                    <text x="400" y="164" class="deal-svg-title">Health</text>
                    <text x="253" y="300" class="deal-svg-title">Next action</text>
                    <circle cx="202" cy="154" r="5" class="deal-svg-pulse"/>
                    <circle cx="361" cy="154" r="5" class="deal-svg-pulse"/>
                </svg>
                <span>Evidence first · human decision required</span>
            </div>
        </div>
    </section>

    <section class="section section-paper deal-playground-section" id="playground">
        <div class="section-inner">
            <div class="section-heading section-heading-row">
                <div><p class="eyebrow">Interactive playground</p><h2>Bring a signal into focus.</h2></div>
                <p class="section-intro">Use an illustrative scenario or paste fictional notes. The demo processes your text for this request and does not save submissions.</p>
            </div>

            <div class="deal-demo-disclaimer" role="note">
                <strong>Use fictional data only.</strong> Do not submit personal, confidential, or customer-identifying information. The analysis is illustrative and is not a prediction or business decision.
            </div>

            <div class="deal-scenario-list" aria-label="Load a sample scenario">
                <button type="button" data-demo-scenario="risk">At-risk enterprise renewal</button>
                <button type="button" data-demo-scenario="expansion">Upsell candidate · usage spike</button>
                <button type="button" data-demo-scenario="unresponsive">Unresponsive buyer</button>
            </div>

            <div class="deal-playground-grid">
                <form class="deal-input-panel" data-deal-auditor action="{{ route('demo.deal-health.analyze') }}" method="POST">
                    @csrf
                    <div class="deal-panel-heading"><span>01 / INPUT</span><span>MAX 6,000 CHARACTERS</span></div>
                    <label for="deal-notes">Customer notes or interaction log</label>
                    <textarea id="deal-notes" name="notes" rows="13" minlength="20" maxlength="6000" required placeholder="Paste a short fictional call note, renewal update, or CRM summary…"></textarea>
                    <div class="deal-input-meta"><span data-word-count>0 words</span><span>Nothing is saved</span></div>
                    <p class="deal-form-error" data-demo-error role="alert" hidden></p>
                    <button class="button button-ink" type="submit"><span data-submit-label>Analyze evidence</span><span aria-hidden="true">↗</span></button>
                    <p class="deal-input-note">The score is a transparent demo heuristic, not an actuarial model or a customer-specific forecast.</p>
                </form>

                <section class="deal-results-panel" aria-labelledby="deal-results-title" aria-live="polite" aria-busy="false" data-demo-results>
                    <div class="deal-panel-heading"><span id="deal-results-title">02 / EVIDENCE REVIEW</span><span class="deal-engine-label">LARAVEL DEMO ENGINE</span></div>
                    <div class="deal-empty-state" data-demo-empty>
                        <span class="deal-empty-mark" aria-hidden="true">+</span>
                        <h3>Your review will appear here.</h3>
                        <p>Choose a scenario or paste notes to see matched signals, direct supporting text, and suggested follow-up.</p>
                    </div>
                    <div class="deal-analysis-output" data-demo-output hidden>
                        <div class="deal-score-row">
                            <div class="deal-score-gauge" data-score-gauge><strong data-score-value>—</strong><span>/ 100</span></div>
                            <div><p class="eyebrow">HEURISTIC HEALTH SCORE</p><h3 data-health-label>Awaiting notes</h3><p data-score-context>Signal strength in the submitted text</p></div>
                        </div>
                        <div class="deal-metrics" aria-label="Demo processing metrics">
                            <span>Laravel processing <strong data-processing-time>—</strong></span>
                            <span>Input size <strong data-input-words>—</strong></span>
                            <small>Measured during this demo request; results vary with server load and input size.</small>
                        </div>
                        <section class="deal-signal-group" data-risk-group hidden>
                            <h4>Risk signals <span data-risk-count>0</span></h4>
                            <div data-risk-signals></div>
                        </section>
                        <section class="deal-signal-group deal-opportunity-group" data-opportunity-group hidden>
                            <h4>Opportunity signals <span data-opportunity-count>0</span></h4>
                            <div data-opportunity-signals></div>
                        </section>
                        <section class="deal-recommendations" data-recommendations-section>
                            <h4>Suggested next steps</h4>
                            <ol data-recommendations></ol>
                        </section>
                        <details class="deal-outreach-details">
                            <summary>Review a draft outreach</summary>
                            <pre data-outreach></pre>
                            <small>Draft only. Review and personalize before use.</small>
                        </details>
                        <p class="deal-result-notice" data-result-notice></p>
                    </div>
                </section>
            </div>
        </div>
    </section>

    <section class="section section-ink deal-architecture-section" id="architecture">
        <div class="section-inner">
            <div class="section-heading section-heading-row">
                <div><p class="eyebrow eyebrow-light">NVIDIA deployment architecture</p><h2>Designed to keep evidence in the workflow.</h2></div>
                <p class="section-intro">An enterprise deployment can pair NVIDIA inference and guardrails with Brenora’s evidence-linked workflows, keeping source context and human review visible alongside each recommendation.</p>
            </div>
            <div class="deal-architecture-flow">
                <article><span>01 / INPUT</span><h3>Customer context</h3><p>CRM notes, renewal updates, support summaries, and usage events.</p></article>
                <span class="deal-flow-arrow" aria-hidden="true">→</span>
                <article><span>02 / INFERENCE</span><h3>NVIDIA NIM</h3><p>Inference microservices for structured signal extraction.</p></article>
                <span class="deal-flow-arrow" aria-hidden="true">→</span>
                <article><span>03 / GUARDRAILS</span><h3>NVIDIA NeMo</h3><p>Guardrails and policy checks before a result is presented.</p></article>
                <span class="deal-flow-arrow" aria-hidden="true">→</span>
                <article><span>04 / ACTION</span><h3>Human-reviewed workflow</h3><p>Evidence-linked recommendations for a person to review and decide.</p></article>
            </div>
            <p class="deal-architecture-note">The public playground presents a transparent, text-based signal review. For enterprise deployments, NVIDIA NIM provides the inference layer and NeMo adds policy guardrails; RAPIDS may support portfolio-scale data preparation.</p>
        </div>
    </section>

    <section class="section section-accent deal-enterprise-cta">
        <div class="section-inner deal-cta-row">
            <div><p class="eyebrow eyebrow-light">Enterprise engagement</p><h2>Explore a deployment for your revenue workflows.</h2><p>Discuss a potential on-premise or cloud architecture, governance needs, and integrations with the Brenora team.</p></div>
            <a class="button button-ink" href="{{ route('home', ['topic' => 'enterprise']) }}#contact">Talk about enterprise deployment <span aria-hidden="true">↗</span></a>
        </div>
    </section>
    @include('marketing.partials.contact')
@endsection
