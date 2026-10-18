@extends('layouts.console', ['title' => $signal->exists ? 'Edit signal' : 'Add signal'])

@section('content')
    <a href="{{ route('accounts.show', $account) }}" class="text-sm text-cyan-200 hover:text-white">← {{ $account->name }}</a>
    <h1 class="mb-8 mt-3 text-3xl font-bold text-white">{{ $signal->exists ? 'Edit signal' : 'Add customer signal' }}</h1>
    <form method="POST" action="{{ $signal->exists ? route('accounts.signals.update', [$account, $signal]) : route('accounts.signals.store', $account) }}" class="max-w-3xl space-y-6 rounded-2xl border border-white/10 bg-slate-900 p-6">
        @csrf
        @if ($signal->exists) @method('PUT') @endif
        <div class="grid gap-5 md:grid-cols-2">
            <div><label for="type" class="mb-2 block text-sm text-slate-200">Signal type</label><input id="type" name="type" value="{{ old('type', $signal->type) }}" maxlength="80" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div><label for="source" class="mb-2 block text-sm text-slate-200">Source</label><input id="source" name="source" value="{{ old('source', $signal->source) }}" maxlength="160" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div><label for="score" class="mb-2 block text-sm text-slate-200">Score (0–100)</label><input id="score" name="score" type="number" min="0" max="100" value="{{ old('score', $signal->score ?? 50) }}" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div><label for="recorded_at" class="mb-2 block text-sm text-slate-200">Recorded at</label><input id="recorded_at" name="recorded_at" type="datetime-local" value="{{ old('recorded_at', $signal->recorded_at?->format('Y-m-d\\TH:i')) }}" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white"></div>
            <div class="md:col-span-2"><label for="summary" class="mb-2 block text-sm text-slate-200">Summary</label><textarea id="summary" name="summary" rows="4" maxlength="5000" required class="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-3 text-white">{{ old('summary', $signal->summary) }}</textarea></div>
        </div>
        <button class="rounded-full bg-cyan-400 px-6 py-3 font-semibold text-slate-950 hover:bg-cyan-300" type="submit">Save signal</button>
    </form>
@endsection
