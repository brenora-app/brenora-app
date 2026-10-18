<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountActivity;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Account::class);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'segment' => ['nullable', 'string', 'max:80'],
            'stage' => ['nullable', Rule::in(['discovery', 'validation', 'proposal', 'commit', 'won', 'lost', 'unassigned'])],
            'renewals' => ['nullable', Rule::in(['90', '180', '365'])],
        ]);

        $accounts = Account::query()
            ->when($filters['q'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('industry', 'like', "%{$search}%")))
            ->when($filters['segment'] ?? null, fn ($query, $segment) => $query->where('segment', $segment))
            ->when($filters['stage'] ?? null, fn ($query, $stage) => $stage === 'unassigned'
                ? $query->whereNull('opportunity_stage')
                : $query->where('opportunity_stage', $stage))
            ->when($filters['renewals'] ?? null, fn ($query, $days) => $query->whereBetween('renewal_date', [today(), today()->addDays((int) $days)]))
            ->orderBy('renewal_date')
            ->paginate(15)
            ->withQueryString();

        $segments = Account::query()->select('segment')->distinct()->orderBy('segment')->pluck('segment');
        $atRiskValue = Account::query()->where('status', 'at_risk')->sum('annual_revenue');
        $expansionValue = Account::query()->where('status', 'expanding')->sum('expansion_value');
        $forecastValue = Account::query()->sum('forecast_revenue');
        $forecastChanges = AccountActivity::query()
            ->with(['account:id,name', 'user:id,name'])
            ->whereHas('account')
            ->where('event_type', 'forecast_changed')
            ->latest()
            ->limit(8)
            ->get();

        return view('revenue.index', compact(
            'accounts',
            'segments',
            'filters',
            'atRiskValue',
            'expansionValue',
            'forecastValue',
            'forecastChanges',
        ));
    }
}
