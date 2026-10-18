<section class="section section-accent" id="contact">
    <div class="section-inner contact-layout">
        <div class="contact-copy">
            <p class="eyebrow eyebrow-light">Start a conversation</p>
            <h2>Let’s make customer signals useful.</h2>
            <p>Tell us what your team is working on. We’ll follow up with a thoughtful answer — not an automated sales sequence.</p>
            <div class="contact-detail">
                <span class="contact-index">01</span>
                <span>Product walkthroughs, pricing questions, and enterprise partnerships.</span>
            </div>
            <div class="contact-detail">
                <span class="contact-index">02</span>
                <span><strong>Brenora (Pvt) Ltd</strong><br>24 New Parliament Road, Battaramulla, Sri Lanka<br><a href="tel:+94112785316">+94 11 278 5316</a></span>
            </div>
            <div class="contact-detail">
                <span class="contact-index">03</span>
                <span><strong>Brenora LLC</strong><br>800 Wilshire Boulevard, Los Angeles, CA 90017, USA<br><a href="tel:+12136473928">+1 213 647 3928</a></span>
            </div>
            <p class="contact-company-note">Founded 22 April 2024 · <a href="https://brenora.net/">brenora.net</a></p>
        </div>

        <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
            @csrf
            @if (session('contact_submitted'))
                <div class="form-success" role="status">Thank you. Your message has been received.</div>
            @endif
            @if ($errors->any())
                <div class="form-error" role="alert">Please check the marked fields and try again.</div>
            @endif
            <div class="form-grid">
                <label>
                    <span>Name</span>
                    <input name="name" type="text" autocomplete="name" value="{{ old('name') }}" maxlength="120" required>
                    @error('name')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Work email</span>
                    <input name="email" type="email" autocomplete="email" value="{{ old('email') }}" maxlength="254" required>
                    @error('email')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>Company <em>Optional</em></span>
                    <input name="company" type="text" autocomplete="organization" value="{{ old('company') }}" maxlength="160">
                    @error('company')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label>
                    <span>What would you like to discuss?</span>
                    <select name="topic" required>
                        <option value="">Select a topic</option>
                        <option value="product" @selected(old('topic', request('topic')) === 'product')>Product</option>
                        <option value="pricing" @selected(old('topic', request('topic')) === 'pricing')>Pricing</option>
                        <option value="partnership" @selected(old('topic', request('topic')) === 'partnership')>Partnership</option>
                        <option value="enterprise" @selected(old('topic', request('topic')) === 'enterprise')>Enterprise engagement</option>
                        <option value="other" @selected(old('topic', request('topic')) === 'other')>Something else</option>
                    </select>
                    @error('topic')<small class="field-error">{{ $message }}</small>@enderror
                </label>
                <label class="form-full">
                    <span>Message</span>
                    <textarea name="message" rows="4" minlength="10" maxlength="5000" required>{{ old('message') }}</textarea>
                    @error('message')<small class="field-error">{{ $message }}</small>@enderror
                </label>
            </div>
            <button class="button button-ink" type="submit">Send your message <span aria-hidden="true">↗</span></button>
            <p class="form-privacy">By sending this form, you agree that Brenora may use your details to respond to your request.</p>
        </form>
    </div>
</section>
