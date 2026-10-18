<div>
    <p class="console-muted">Deleting your account permanently removes your profile and signs you out. Workspace records remain available to other authorized members.</p>
    <form method="post" action="{{ route('profile.destroy') }}" class="console-form mt-6" onsubmit="return confirm('Permanently delete your account? This cannot be undone.')">
        @csrf
        @method('delete')
        <div class="console-field">
            <label for="delete-account-password">{{ __('Confirm with your password') }}</label>
            <input id="delete-account-password" name="password" type="password" autocomplete="current-password" required>
            @error('password', 'userDeletion')<span class="console-field-error">{{ $message }}</span>@enderror
        </div>
        <button type="submit" class="console-button console-button-danger">{{ __('Delete account') }}</button>
    </form>
</div>
