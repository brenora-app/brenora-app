@php($plans = config('pricing.plans'))

<section class="section section-paper pricing-section" id="pricing" x-data="{ annual: false }">
    <div class="section-inner">
        <div class="section-heading section-heading-row">
            <div>
                <p class="eyebrow">Clear plans, practical value</p>
                <h2>Build a healthier customer portfolio.</h2>
            </div>
            <p class="section-intro">Track renewals, connect customer context, and help your team take a well-informed next step.</p>
        </div>

        @if (session('trial_expired'))
            <p class="trial-expired-inline" role="status">Your trial has ended. Choose a plan or contact us to discuss continued access.</p>
        @endif

        <div class="pricing-billing-toggle" role="group" aria-label="Billing frequency">
            <span :class="{ 'billing-option-active': !annual }">Monthly</span>
            <button type="button" class="billing-switch" role="switch" :aria-checked="annual.toString()" aria-label="Toggle annual billing" @click="annual = !annual">
                <span></span>
            </button>
            <span :class="{ 'billing-option-active': annual }">Annual <strong>2 months free</strong></span>
        </div>

        <div class="pricing-packages">
            @foreach ($plans as $slug => $plan)
                <article class="pricing-package {{ $slug === 'growth' ? 'pricing-package-featured' : '' }} {{ $slug === 'enterprise' ? 'pricing-package-enterprise' : '' }}">
                    @if ($slug === 'growth')
                        <span class="pricing-package-badge">Best for growing teams</span>
                    @endif
                    <p class="pricing-label">{{ $plan['name'] ?? 'Enterprise' }}</p>
                    <h3>{{ $slug === 'enterprise' ? 'Revenue intelligence, built around your needs.' : $plan['description'] }}</h3>
                    @if ($slug !== 'enterprise')
                        <div class="pricing-package-price">
                            <strong x-text="annual ? '${{ $plan['annual'] }}' : '${{ $plan['monthly'] }}'">${{ $plan['monthly'] }}</strong>
                            <span x-text="annual ? 'billed annually' : 'billed monthly'">billed monthly</span>
                            <small x-show="annual">Equivalent to ${{ number_format((float) $plan['annual'] / 12, 2) }} per month · 2 months free</small>
                        </div>
                    @else
                        <div class="pricing-package-price pricing-package-quote"><strong>Let’s scope it together</strong><span>Custom quote</span></div>
                    @endif

                    <ul class="pricing-feature-list">
                        @foreach ($plan['features'] as $feature)
                            <li><span aria-hidden="true">✓</span>{{ $feature }}</li>
                        @endforeach
                    </ul>

                    <div class="pricing-package-actions">
                        @if ($slug === 'enterprise')
                            <a class="button button-ink" href="{{ route('home', ['topic' => 'enterprise']) }}#contact">Get an enterprise quote <span aria-hidden="true">↗</span></a>
                        @else
                            @guest
                                <a class="button button-outline" href="{{ route('login') }}">Sign in to buy with PayPal <span aria-hidden="true">↗</span></a>
                            @else
                                @if (config('services.paypal.client_id') && config('services.paypal.secret'))
                                    <div class="paypal-checkout"
                                         data-paypal-buttons
                                         data-plan="{{ $slug }}"
                                         data-period="monthly"
                                         :data-period="annual ? 'annual' : 'monthly'"
                                         data-create-url="{{ route('paypal.orders.store') }}"
                                         data-capture-url="{{ route('paypal.orders.capture', ['payment' => '__PAYMENT_ID__']) }}"
                                         aria-label="Buy the {{ $plan['name'] }} plan with PayPal"></div>
                                    <p class="paypal-message" data-paypal-message role="status" hidden></p>
                                    <p class="pricing-payment-note">Secure one-time payment. Access lasts for the selected billing period.</p>
                                @else
                                    <p class="pricing-checkout-unavailable">Online checkout is being configured. <a href="{{ route('home', ['topic' => 'pricing']) }}#contact">Contact us to get started.</a></p>
                                @endif
                            @endguest
                        @endif
                        @guest
                            <a class="button button-primary" href="{{ route('register') }}">Start free trial <span aria-hidden="true">↗</span></a>
                        @else
                            @if (! auth()->user()->trial_ends_at || auth()->user()->paid_until?->isFuture() || auth()->user()->trial_ends_at?->isFuture())
                                <a class="button button-primary" href="{{ route('dashboard') }}">Open workspace <span aria-hidden="true">↗</span></a>
                            @else
                                <a class="button button-primary" href="{{ route('home') }}#contact">Contact us <span aria-hidden="true">↗</span></a>
                            @endif
                        @endguest
                    </div>
                </article>
            @endforeach
        </div>

        <h3 class="comparison-title">Compare what’s included</h3>
        <div class="comparison-table-wrap" role="region" aria-label="Plan feature comparison" tabindex="0">
            <table class="comparison-table pricing-comparison">
                <thead>
                    <tr><th scope="col">Workspace capability</th><th scope="col">Launch</th><th scope="col">Growth</th><th scope="col">Enterprise</th></tr>
                </thead>
                <tbody>
                    <tr><th scope="row">Account and contact profiles</th><td>Included</td><td>Included</td><td>Included</td></tr>
                    <tr><th scope="row">Customer signal history and source freshness</th><td>Included</td><td>Included</td><td>Included</td></tr>
                    <tr><th scope="row">Health, renewals, and pipeline context</th><td>Included</td><td>Included</td><td>Included</td></tr>
                    <tr><th scope="row">Evidence-linked AI briefings and insight review</th><td>Included</td><td>Included</td><td>Included</td></tr>
                    <tr><th scope="row">Commercial actions and activity history</th><td>Included</td><td>Included</td><td>Included</td></tr>
                    <tr><th scope="row">Salesforce connection</th><td>—</td><td>Available by configuration</td><td>Discuss your requirements</td></tr>
                    <tr><th scope="row">Deployment, governance, and integration planning</th><td>—</td><td>—</td><td>Scoping conversation</td></tr>
                    <tr><th scope="row">Billing</th><td>Monthly or annual</td><td>Monthly or annual</td><td>Custom quote</td></tr>
                </tbody>
            </table>
        </div>
        <p class="pricing-footnote">All packages include a 14-day free trial. Paid access is for the selected period and renews manually. <a class="text-link" href="#contact">Questions about fit? Talk with us <span aria-hidden="true">↗</span></a></p>
    </div>
</section>
