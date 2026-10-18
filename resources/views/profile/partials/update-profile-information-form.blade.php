<div>
    <p class="console-muted">Your name and email are shown to your team across the workspace.</p>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>
    <form method="post" action="{{ route('profile.update') }}" class="console-form mt-6">
        @csrf
        @method('patch')
        <div class="console-field">
            <label for="name">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">
            @error('name')<span class="console-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="console-field">
            <label for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')<span class="console-field-error">{{ $message }}</span>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <p class="console-muted">
                        {{ __('Your email address is unverified.') }}
                        <button form="send-verification" class="console-text-link">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>
                    @if (session('status') === 'verification-link-sent')
                        <p class="console-field-success">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
            @endif
        </div>
        <div class="console-form-actions">
            <button type="submit" class="console-button console-button-primary">{{ __('Save profile') }} <span aria-hidden="true">→</span></button>
            @if (session('status') === 'profile-updated')
                <p class="console-field-success">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</div>
