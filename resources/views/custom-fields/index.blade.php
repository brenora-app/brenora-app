@extends('layouts.console', ['title' => 'Custom fields'])

@section('content')
    <section class="console-page-heading">
        <div><p class="console-eyebrow">WORKSPACE CONFIGURATION</p><h1>Custom account fields</h1><p>Capture the details your team needs without changing your core account workflow.</p></div>
        <a href="{{ route('accounts.index') }}" class="console-button console-button-secondary">Back to accounts <span aria-hidden="true">↗</span></a>
    </section>

    <div class="custom-fields-layout">
        <section class="console-panel custom-field-builder">
            <div class="console-panel-heading"><div><p class="console-eyebrow">FIELD BUILDER</p><h2>Add a field</h2></div><span class="custom-builder-number">01</span></div>
            <p class="console-muted">New fields appear on account create and edit forms.</p>
            <form method="POST" action="{{ route('custom-fields.store') }}" class="console-form" x-data="{ type: @js(old('type', 'text')) }">
                @csrf
                <div class="console-field">
                    <label for="new-field-name">Field name</label>
                    <input id="new-field-name" name="name" value="{{ old('name') }}" maxlength="80" required placeholder="e.g. Success plan">
                </div>
                <div class="console-field">
                    <label for="new-field-type">Field type</label>
                    <select id="new-field-type" name="type" required x-model="type">
                        @foreach (['text' => 'Short text', 'number' => 'Number', 'date' => 'Date', 'select' => 'Dropdown', 'checkbox' => 'Checkbox'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('type', 'text') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="console-field" x-show="type === 'select'" x-cloak>
                    <label for="new-field-options">Dropdown options <span>One option per line</span></label>
                    <textarea id="new-field-options" name="options_text" rows="4" placeholder="Planned&#10;In progress&#10;Complete">{{ old('options_text') }}</textarea>
                    @error('options_text')<span class="console-field-error">{{ $message }}</span>@enderror
                </div>
                <label class="console-switch-row">
                    <input type="checkbox" name="required" value="1" @checked(old('required'))>
                    <span><strong>Required on account forms</strong><small>Team members must provide this value when saving an account.</small></span>
                </label>
                <button type="submit" class="console-button console-button-primary">Add custom field <span aria-hidden="true">→</span></button>
            </form>
        </section>

        <section class="console-panel custom-field-list">
            <div class="console-panel-heading"><div><p class="console-eyebrow">ACCOUNT SCHEMA</p><h2>Active fields</h2></div><span class="panel-count">{{ $fields->count() }}</span></div>
            @if ($fields->isEmpty())
                <div class="dashboard-empty custom-fields-empty">
                    <span class="dashboard-empty-mark">↳</span>
                    <strong>No custom fields yet.</strong>
                    <p>Add a field to capture details beyond industry, owner and account health.</p>
                </div>
            @else
                <div class="custom-field-rows">
                    @foreach ($fields as $field)
                        <article class="custom-field-row">
                            <div class="custom-field-summary">
                                <span class="custom-field-type">{{ strtoupper(substr($field->type, 0, 3)) }}</span>
                                <span><strong>{{ $field->name }}</strong><small>{{ str_replace('_', ' ', $field->key) }} · {{ $field->type }}{{ $field->required ? ' · required' : '' }}</small></span>
                            </div>
                            <form method="POST" action="{{ route('custom-fields.update', $field) }}" class="custom-field-edit" x-data="{ type: @js($field->type) }">
                                @csrf
                                @method('PUT')
                                <div class="console-field"><label for="field-name-{{ $field->id }}">Label</label><input id="field-name-{{ $field->id }}" name="name" value="{{ $field->name }}" maxlength="80" required></div>
                                <div class="console-field"><label for="field-type-{{ $field->id }}">Type</label>
                                    <select id="field-type-{{ $field->id }}" name="type" required x-model="type">
                                        @foreach (['text' => 'Short text', 'number' => 'Number', 'date' => 'Date', 'select' => 'Dropdown', 'checkbox' => 'Checkbox'] as $value => $label)
                                            <option value="{{ $value }}" @selected($field->type === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="console-field custom-field-options" x-show="type === 'select'" x-cloak><label for="field-options-{{ $field->id }}">Dropdown options <span>One per line</span></label><textarea id="field-options-{{ $field->id }}" name="options_text" rows="3">{{ implode("\n", $field->options ?? []) }}</textarea></div>
                                <label class="console-switch-row"><input type="checkbox" name="required" value="1" @checked($field->required)><span><strong>Required</strong><small>Require a value when saving</small></span></label>
                                <div class="custom-field-buttons">
                                    <button type="submit" class="console-button console-button-secondary">Save changes</button>
                                    <button type="submit" form="delete-field-{{ $field->id }}" class="console-button console-button-danger">Remove field</button>
                                </div>
                            </form>
                            <form id="delete-field-{{ $field->id }}" method="POST" action="{{ route('custom-fields.destroy', $field) }}" onsubmit="return confirm('Remove this field and all values saved for it on accounts?')">
                                @csrf
                                @method('DELETE')
                            </form>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
@endsection
