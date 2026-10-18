<?php

namespace App\Http\Controllers;

use App\Models\AccountActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->isAdmin(), 403);
        $eventTypes = AccountActivity::query()->select('event_type')->distinct()->orderBy('event_type')->pluck('event_type');
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'event' => ['nullable', Rule::in($eventTypes->all())],
        ]);
        $activities = AccountActivity::query()
            ->with(['account:id,name', 'user:id,name'])
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event_type', $event))
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('summary', 'like', "%{$search}%")
                ->orWhereHas('account', fn ($related) => $related->where('name', 'like', "%{$search}%"))
                ->orWhereHas('user', fn ($related) => $related->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('activity.index', compact('activities', 'eventTypes', 'filters'));
    }
}
