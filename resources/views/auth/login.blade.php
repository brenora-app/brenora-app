<x-guest-layout>
    @if (session('status'))
        <div class="auth-notice" role="status">{{ session('status') }}</div>
    @endif

    @if (app()->environment('local'))
        <div class="auth-demo-note">
            <strong>Local demo workspace</strong>
            <span>demo@brenora.com · Demo@1234</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf
        <div class="auth-field">
            <label for="email">Work email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="you@company.com">
            @error('email')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <div class="auth-field">
            <div class="auth-label-row"><label for="password">Password</label><a href="{{ route('password.request') }}">Forgot password?</a></div>
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password">
            @error('password')<span class="auth-error">{{ $message }}</span>@enderror
        </div>
        <label class="auth-check"><input id="remember_me" type="checkbox" name="remember"><span>Keep me signed in</span></label>
        <button class="auth-submit" type="submit">Sign in <span aria-hidden="true">→</span></button>
    </form>
    <p class="auth-switch">New to Brenora? <a href="{{ route('register') }}">Start your free trial</a></p>
</x-guest-layout>
