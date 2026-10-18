<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountCustomFieldController;
use App\Http\Controllers\AccountContactController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DemoDealAuditorController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PayPalCheckoutController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\SalesforceIntegrationController;
use App\Http\Controllers\SignalController;
use App\Models\Account;
use App\Models\CommercialAction;
use App\Models\Insight;
use Illuminate\Support\Facades\Route;

Route::get('/', [MarketingController::class, 'home'])->name('home');
Route::get('/about', [MarketingController::class, 'about'])->name('about');
Route::get('/product', [MarketingController::class, 'product'])->name('product');
Route::get('/pricing', [MarketingController::class, 'pricing'])->name('pricing');
Route::get('/demo/deal-health', [DemoDealAuditorController::class, 'show'])->name('demo.deal-health');
Route::post('/demo/deal-health/analyze', [DemoDealAuditorController::class, 'analyze'])
    ->middleware('throttle:15,1')
    ->name('demo.deal-health.analyze');
Route::post('/contact', [ContactSubmissionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('contact.store');
Route::get('/sitemap.xml', function () {
    return response()->view('sitemap', [
        'pages' => [
            route('home'),
            route('about'),
            route('product'),
            route('pricing'),
            route('demo.deal-health'),
            route('login'),
            route('register'),
        ],
    ])->header('Content-Type', 'application/xml');
})->name('sitemap');
Route::get('/robots.txt', function () {
    return response("User-agent: *\nAllow: /\nDisallow: /dashboard\nDisallow: /admin\nDisallow: /accounts\nSitemap: ".route('sitemap')."\n", 200)
        ->header('Content-Type', 'text/plain');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/paypal/orders', [PayPalCheckoutController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('paypal.orders.store');
    Route::post('/paypal/orders/{payment}/capture', [PayPalCheckoutController::class, 'capture'])
        ->middleware('throttle:10,1')
        ->name('paypal.orders.capture');

    Route::middleware(['trial'])->group(function () {
        Route::get('/dashboard', function () {
            $accounts = Account::with('signals')->orderByDesc('updated_at')->limit(8)->get();
            $insights = Insight::with('account')->latest()->limit(6)->get();
            $actions = CommercialAction::with('account')->orderByRaw("CASE WHEN status IN ('completed', 'cancelled') THEN 1 ELSE 0 END")
                ->orderBy('due_date')->limit(8)->get();
            $atRiskRevenue = Account::query()->where('status', 'at_risk')->sum('annual_revenue');
            $atRiskCount = Account::query()->where('status', 'at_risk')->count();
            $expansionValue = Account::query()->where('status', 'expanding')->sum('expansion_value');
            $expandingCount = Account::query()->where('status', 'expanding')->count();
            $totalRevenue = Account::query()->sum('annual_revenue');
            $totalAccounts = Account::query()->count();
            $assessedHealth = Account::query()->where('status', '!=', 'needs_review')->whereNotNull('health_score');
            $avgHealth = $assessedHealth->avg('health_score');
            $assessedAccounts = $assessedHealth->count();
            $upcomingRenewals = Account::query()->whereBetween('renewal_date', [today(), today()->addDays(90)])->count();
            $overdueActions = CommercialAction::query()
                ->whereNotNull('due_date')
                ->whereDate('due_date', '<', today())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count();
            $missingContext = Account::query()
                ->where(fn ($query) => $query->whereNull('subscription_plan')->orWhereDoesntHave('contacts')->orWhereDoesntHave('signals'))
                ->limit(5)
                ->get(['id', 'name']);
            $preferences = auth()->user()->dashboard_preferences ?? [];
            $sections = array_replace([
                'metrics' => true,
                'accounts' => true,
                'insights' => true,
                'actions' => true,
            ], $preferences);

            return view('dashboard', compact('accounts', 'insights', 'actions', 'sections', 'atRiskRevenue', 'atRiskCount', 'expansionValue', 'expandingCount', 'totalRevenue', 'totalAccounts', 'avgHealth', 'assessedAccounts', 'upcomingRenewals', 'overdueActions', 'missingContext'));
        })->name('dashboard');

        Route::get('/revenue', [RevenueController::class, 'index'])->name('revenue.index');
        Route::resource('accounts', AccountController::class);
        Route::post('/accounts/{account}/contacts', [AccountContactController::class, 'store'])->name('accounts.contacts.store');
        Route::put('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'update'])->name('accounts.contacts.update');
        Route::delete('/accounts/{account}/contacts/{contact}', [AccountContactController::class, 'destroy'])->name('accounts.contacts.destroy');
        Route::post('/accounts/{account}/briefing', [AccountController::class, 'brief'])->middleware('throttle:10,1')->name('accounts.brief');
        Route::resource('custom-fields', AccountCustomFieldController::class)
            ->only(['index', 'store', 'update', 'destroy']);
        Route::post('/accounts/{account}/insights', [AccountController::class, 'generateInsight'])
            ->middleware('throttle:5,1')
            ->name('accounts.insights.generate');
        Route::resource('accounts.signals', SignalController::class)->except(['index', 'show']);
        Route::post('/insights/{insight}/review', [AdminController::class, 'reviewInsight'])->name('insights.review');

        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/admin/activity', [ActivityLogController::class, 'index'])->name('activity.index');
        Route::get('/admin/integrations/salesforce', [SalesforceIntegrationController::class, 'index'])->name('salesforce.index');
        Route::get('/admin/integrations/salesforce/connect', [SalesforceIntegrationController::class, 'connect'])->name('salesforce.connect');
        Route::get('/admin/integrations/salesforce/callback', [SalesforceIntegrationController::class, 'callback'])->name('salesforce.callback');
        Route::post('/admin/integrations/salesforce/sync', [SalesforceIntegrationController::class, 'sync'])->name('salesforce.sync');
        Route::delete('/admin/integrations/salesforce', [SalesforceIntegrationController::class, 'disconnect'])->name('salesforce.disconnect');
        Route::patch('/admin/users/{user}/role', [AdminController::class, 'updateRole'])->name('admin.users.role');
        Route::post('/admin/actions', [AdminController::class, 'storeAction'])->name('admin.actions.store');
        Route::patch('/admin/actions/{action}', [AdminController::class, 'updateAction'])->name('admin.actions.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/settings/dashboard', [ProfileController::class, 'updateDashboardPreferences'])
        ->name('settings.dashboard.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
