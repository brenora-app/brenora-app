<x-guest-layout>
    <div class="auth-trial-note">
        <span class="auth-trial-icon" aria-hidden="true">14</span>
        <div><strong>14 days to explore</strong><span>Full workspace access. No payment details required.</span></div>
    </div>
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="name">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="Your name">
            @error('name')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-field">
            <label for="email">Work email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@company.com">
            @error('email')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Create a secure password">
            @error('password')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Enter it once more">
            @error('password_confirmation')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <button class="auth-submit" type="submit">Create workspace <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
</x-guest-layout>
