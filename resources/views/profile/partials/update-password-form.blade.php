<div>
    <p class="console-muted">{{ __('Use a unique password and keep it private to maintain workspace security.') }}</p>
    <form method="post" action="{{ route('password.update') }}" class="console-form mt-6">
        @csrf
        @method('put')
        <div class="console-field">
            <label for="update_password_current_password">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password">
            @error('current_password', 'updatePassword')<span class="console-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="console-field">
            <label for="update_password_password">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password">
            @error('password', 'updatePassword')<span class="console-field-error">{{ $message }}</span>@enderror
        </div>
        <div class="console-field">
            <label for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
            @error('password_confirmation', 'updatePassword')<span class="console-field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="console-button console-button-primary">{{ __('Update password') }} <span aria-hidden="true">→</span></button>
    </form>
</div>
