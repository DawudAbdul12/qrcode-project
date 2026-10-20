<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyDashboardController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\LabelOrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UssdVerificationController;
use App\Http\Controllers\VerificationReportController;
use App\Http\Controllers\VerifyController;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\Company;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\QrCode;
use App\Models\ScanLog;
use App\Models\User;
use App\Services\ScanAnalytics;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('active8.page', 'dashboard')
        : redirect()->route('login');
})->name('home');

Route::redirect('/admin', '/admin/dashboard')->name('admin.full');
Route::get('/active8-full/{path?}', function (?string $path = null) {
    $legacyPages = [
        'profile' => 'dashboard',
        'editor' => 'company-profile',
        'contacts' => 'companies',
        'email' => 'products',
        'analytics' => 'scan-analytics',
        'integrations' => 'scan-logs',
        'shop' => 'label-orders',
        'settings' => 'accounts',
    ];
    $path = $legacyPages[$path] ?? ($path ?: 'dashboard');
    $query = request()->getQueryString();

    return redirect('/admin/'.$path.($query ? '?'.$query : ''));
})->where('path', '.*');
Route::get('/admin/{legacyPage}', function (string $legacyPage) {
    $pages = [
        'profile' => 'dashboard',
        'editor' => 'company-profile',
        'contacts' => 'companies',
        'email' => 'products',
        'analytics' => 'scan-analytics',
        'integrations' => 'scan-logs',
        'shop' => 'label-orders',
        'settings' => 'accounts',
    ];
    $query = request()->getQueryString();

    return redirect('/admin/'.$pages[$legacyPage].($query ? '?'.$query : ''));
})->whereIn('legacyPage', ['profile', 'editor', 'contacts', 'email', 'analytics', 'integrations', 'shop', 'settings']);

Route::get('/verify/{token}', [VerifyController::class, 'show'])->middleware('throttle:30,1')->name('verify.show');
Route::post('/ussd/callback', [UssdVerificationController::class, 'callback'])->middleware('throttle:ussd')->name('ussd.callback');
Route::get('/ussd/test', [UssdVerificationController::class, 'simulator'])->name('ussd.test');
Route::post('/ussd/test', [UssdVerificationController::class, 'simulate'])->name('ussd.test.submit');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetOtp'])->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password/verify', [AuthController::class, 'verifyResetOtp'])->name('password.otp.verify');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function (): void {
    Route::get('/force-password-reset', [AuthController::class, 'showForcedPasswordReset'])->name('password.force.show');
    Route::post('/force-password-reset', [AuthController::class, 'completeForcedPasswordReset'])->name('password.force.update');
});

Route::middleware(['auth', 'password.reset.complete', 'company.user'])->prefix('company')->group(function (): void {
    Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('company.dashboard');
    Route::get('/products', [CompanyDashboardController::class, 'products'])->name('company.products');
    Route::get('/products/{product}', [CompanyDashboardController::class, 'product'])->name('company.products.show');
    Route::get('/batches', [CompanyDashboardController::class, 'batches'])->name('company.batches');
    Route::get('/batches/{batch}', [CompanyDashboardController::class, 'batch'])->name('company.batches.show');
    Route::get('/label-orders', [CompanyDashboardController::class, 'labelOrders'])->name('company.label-orders');
    Route::get('/label-orders/{labelOrder}', [CompanyDashboardController::class, 'labelOrder'])->name('company.label-orders.show');
    Route::get('/ussd-codes', [CompanyDashboardController::class, 'labelCodes'])->name('company.ussd-codes');
    Route::get('/ussd-codes/{labelCode}', [CompanyDashboardController::class, 'labelCode'])->name('company.ussd-codes.show');
    Route::get('/verifications', [CompanyDashboardController::class, 'verifications'])->name('company.verifications');
    Route::get('/verifications/{scanLog}', [CompanyDashboardController::class, 'verification'])->name('company.verifications.show');
    Route::get('/suspicious-activity', [CompanyDashboardController::class, 'suspicious'])->name('company.suspicious');
    Route::get('/account/{tab?}', [CompanyDashboardController::class, 'account'])->name('company.account');
    Route::put('/account/profile', [CompanyDashboardController::class, 'updateAccountProfile'])->name('company.account.profile.update');
    Route::put('/account/password', [CompanyDashboardController::class, 'updateAccountPassword'])->name('company.account.password.update');
    Route::get('/profile', [CompanyDashboardController::class, 'profile'])->name('company.profile');
    Route::get('/users', [CompanyDashboardController::class, 'users'])->name('company.users');
    Route::post('/users', [CompanyDashboardController::class, 'storeUser'])->name('company.users.store');
    Route::get('/users/{user}', [CompanyDashboardController::class, 'user'])->name('company.users.show');
    Route::get('/exports/{resource}.csv', [CompanyDashboardController::class, 'export'])->name('company.exports');
});

Route::middleware(['auth', 'password.reset.complete', 'platform.user'])->group(function (): void {
    Route::post('/admin/companies', [CompanyController::class, 'store'])->name('companies.store');
    Route::post('/admin/companies/bulk', [CompanyController::class, 'bulk'])->name('companies.bulk');
    Route::get('/admin/companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
    Route::put('/admin/companies/{company}/profile', [CompanyProfileController::class, 'update'])->name('companies.profile.update');
    Route::put('/admin/companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
    Route::delete('/admin/companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store');
    Route::post('/admin/products/bulk', [ProductController::class, 'bulk'])->name('products.bulk');
    Route::put('/admin/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('/admin/categories', [ProductCategoryController::class, 'store'])->name('categories.store');
    Route::post('/admin/categories/bulk', [ProductCategoryController::class, 'bulk'])->name('categories.bulk');
    Route::put('/admin/categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/admin/batches', [BatchController::class, 'store'])->name('batches.store');
    Route::post('/admin/batches/bulk', [BatchController::class, 'bulk'])->name('batches.bulk');
    Route::post('/admin/batches/{batch}/qr-code', [BatchController::class, 'generateQrCode'])->name('batches.qr-code.store');
    Route::put('/admin/batches/{batch}/qr-code/activate', [BatchController::class, 'activateQrCode'])->name('batches.qr-code.activate');
    Route::put('/admin/batches/{batch}/qr-code/revoke', [BatchController::class, 'revokeQrCode'])->name('batches.qr-code.revoke');
    Route::put('/admin/batches/{batch}/qr-code/controls', [BatchController::class, 'updateQrCodeControls'])->name('batches.qr-code.controls');
    Route::get('/admin/batches/{batch}/qr-code/download', [BatchController::class, 'downloadQrCode'])->name('batches.qr-code.download');
    Route::get('/admin/reports/verification.csv', [VerificationReportController::class, 'export'])->name('reports.verification.export');
    Route::put('/admin/batches/{batch}', [BatchController::class, 'update'])->name('batches.update');
    Route::delete('/admin/batches/{batch}', [BatchController::class, 'destroy'])->name('batches.destroy');
    Route::post('/admin/label-orders', [LabelOrderController::class, 'store'])->name('label-orders.store');
    Route::post('/admin/label-orders/bulk', [LabelOrderController::class, 'bulk'])->name('label-orders.bulk');
    Route::get('/admin/label-orders/search', [LabelOrderController::class, 'search'])->name('label-orders.search');
    Route::get('/admin/label-orders/{labelOrder}/codes.csv', [LabelOrderController::class, 'exportCodes'])->name('label-orders.codes.export');
    Route::put('/admin/label-orders/{labelOrder}', [LabelOrderController::class, 'update'])->name('label-orders.update');
    Route::delete('/admin/label-orders/{labelOrder}', [LabelOrderController::class, 'destroy'])->name('label-orders.destroy');
    Route::post('/admin/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/admin/users/bulk', [UserController::class, 'bulk'])->name('users.bulk');
    Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::put('/admin/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile.update');
    Route::put('/admin/account/password', [AccountController::class, 'updatePassword'])->name('account.password.update');
    Route::get('/admin/{page}', function (string $page) {
        $pages = ['dashboard', 'company-profile', 'companies', 'products', 'categories', 'batches', 'scan-analytics', 'scan-logs', 'audit-trail', 'label-orders', 'label-codes', 'accounts', 'account'];
        abort_unless(in_array($page, $pages, true), 404);

        $analyticsReport = app(ScanAnalytics::class)->report(
            request('range', '30'),
            request('status', 'all'),
            request('device', 'all'),
            request('start_date'),
            request('end_date'),
            request('company', 'all'),
            request('product', 'all'),
            request('batch', 'all'),
        );

        $selectedCompanyId = request('company');
        $selectedCompany = Company::query()
            ->with('profile')
            ->when($selectedCompanyId, fn ($query) => $query->whereKey($selectedCompanyId))
            ->first()
            ?? Company::query()->with('profile')->first();
        $editorQrCode = $selectedCompany
            ? QrCode::query()->whereHas('batch.product', fn ($query) => $query->where('company_id', $selectedCompany->id))->latest()->first()
            : null;
        $activityCompanyId = request('activity_company', 'all');
        $activityBatchId = request('activity_batch', 'all');
        $activityEvent = request('activity_event', 'all');
        $activityBaseQuery = Schema::hasTable('activity_logs')
            ? ActivityLog::query()
                ->with('user')
                ->when($activityEvent !== 'all', fn ($query) => $query->where('event', $activityEvent))
                ->when($activityBatchId !== 'all', fn ($query) => $query
                    ->where('subject_type', Batch::class)
                    ->where('subject_id', $activityBatchId))
                ->when($activityCompanyId !== 'all' && $activityBatchId === 'all', fn ($query) => $query
                    ->where('subject_type', Batch::class)
                    ->whereIn('subject_id', Batch::query()
                        ->whereHas('product', fn ($productQuery) => $productQuery->where('company_id', $activityCompanyId))
                        ->select('id')))
            : null;
        $activityLogs = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)
                ->latest()
                ->paginate(15, ['*'], 'activity_page')
                ->withQueryString()
            : collect();
        $activityTotal = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)->count()
            : 0;
        $activityToday = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)->whereDate('created_at', now()->toDateString())->count()
            : 0;
        $activityQrEvents = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)->where('event', 'like', 'qr.%')->count()
            : 0;
        $activityActorCount = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)->whereNotNull('user_id')->distinct('user_id')->count('user_id')
            : 0;
        $activityEventBreakdown = Schema::hasTable('activity_logs')
            ? (clone $activityBaseQuery)
                ->selectRaw('event, count(*) as total')
                ->groupBy('event')
                ->orderByDesc('total')
                ->take(6)
                ->get()
            : collect();
        $labelCodeSearch = trim((string) request('code_search', ''));
        $labelCodeStatus = request('code_status', 'all');
        $labelCodeOrder = request('code_order', 'all');
        $labelCodeOrderSearch = trim((string) request('code_order_search', ''));
        $labelCodePerPage = (int) request('per_page', 50);
        $labelCodePerPage = in_array($labelCodePerPage, [25, 50, 100, 250], true) ? $labelCodePerPage : 50;
        $applyLabelOrderSearch = function ($query) use ($labelCodeOrderSearch): void {
            if ($labelCodeOrderSearch === '') {
                return;
            }

            $query->where(function ($builder) use ($labelCodeOrderSearch): void {
                if (ctype_digit($labelCodeOrderSearch)) {
                    $builder->orWhereKey($labelCodeOrderSearch);
                }

                $builder
                    ->orWhere('printer_vendor', 'like', '%'.$labelCodeOrderSearch.'%')
                    ->orWhereHas('company', fn ($companyQuery) => $companyQuery->where('name', 'like', '%'.$labelCodeOrderSearch.'%'))
                    ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$labelCodeOrderSearch.'%'))
                    ->orWhereHas('batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$labelCodeOrderSearch.'%'));
            });
        };
        $labelCodesQuery = LabelCode::query()
            ->with(['company', 'labelOrder.company', 'batch.product.company'])
            ->when($labelCodeSearch !== '', fn ($query) => $query->where('code', 'like', '%'.strtoupper($labelCodeSearch).'%'))
            ->when($labelCodeStatus !== 'all', fn ($query) => $query->where('status', $labelCodeStatus))
            ->when($labelCodeOrder !== 'all', fn ($query) => $query->where('label_order_id', $labelCodeOrder))
            ->when($labelCodeOrder === 'all' && $labelCodeOrderSearch !== '', fn ($query) => $query->whereHas('labelOrder', $applyLabelOrderSearch))
            ->oldest('label_order_id')
            ->oldest('id');
        $selectedLabelOrder = $labelCodeOrder !== 'all'
            ? LabelOrder::query()
                ->with(['company', 'batch.product'])
                ->withCount('labelCodes')
                ->find($labelCodeOrder)
            : null;
        $labelOrderOptions = LabelOrder::query()
            ->with(['company', 'batch.product'])
            ->withCount('labelCodes')
            ->when($labelCodeOrderSearch !== '', $applyLabelOrderSearch)
            ->latest()
            ->limit(50)
            ->get();

        if ($selectedLabelOrder && ! $labelOrderOptions->contains('id', $selectedLabelOrder->id)) {
            $labelOrderOptions->prepend($selectedLabelOrder);
        }

        return view('active8-full', [
            'activePage' => $page,
            'company' => $selectedCompany,
            'editorQrCode' => $editorQrCode,
            'companies' => Company::query()
                ->with('profile')
                ->withCount(['products', 'batches', 'labelOrders'])
                ->latest()
                ->get(),
            'products' => Product::query()
                ->with(['company', 'productCategory', 'suitabilityItems'])
                ->withCount('batches')
                ->latest()
                ->get(),
            'categories' => ProductCategory::query()
                ->with('company')
                ->withCount('products')
                ->latest()
                ->get(),
            'batches' => Batch::query()
                ->with(['product.company', 'qrCode'])
                ->latest()
                ->get(),
            'dashboardStats' => [
                ['label' => 'Companies', 'value' => Company::query()->count(), 'icon' => 'users'],
                ['label' => 'Products', 'value' => Product::query()->count(), 'icon' => 'bag'],
                ['label' => 'Batches', 'value' => Batch::query()->count(), 'icon' => 'form'],
                ['label' => 'Batch QR Codes', 'value' => QrCode::query()->count(), 'icon' => 'qr'],
                ['label' => 'Total Scans', 'value' => ScanLog::query()->count(), 'icon' => 'eye'],
                ['label' => 'Suspicious / Invalid', 'value' => ScanLog::query()->whereIn('status_result', ['invalid', 'suspicious', 'revoked', 'expired'])->count(), 'icon' => 'activity'],
            ],
            'recentBatches' => Batch::query()
                ->with(['product.company', 'qrCode'])
                ->latest()
                ->take(20)
                ->get(),
            'recentScans' => ScanLog::query()
                ->with('qrCode.batch.product.company')
                ->latest('scanned_at')
                ->take(20)
                ->get(),
            'scanLogs' => ScanLog::query()
                ->with('qrCode.batch.product.company')
                ->when(request('status') && request('status') !== 'all', fn ($query) => $query->where('status_result', request('status')))
                ->when(request('company') && request('company') !== 'all', fn ($query) => $query->whereHas('qrCode.batch.product', fn ($productQuery) => $productQuery->where('company_id', request('company'))))
                ->when(request('batch') && request('batch') !== 'all', fn ($query) => $query->whereHas('qrCode.batch', fn ($batchQuery) => $batchQuery->whereKey(request('batch'))))
                ->latest('scanned_at')
                ->take(50)
                ->get(),
            'scanSummary' => [
                'total' => ScanLog::query()->count(),
                'genuine' => ScanLog::query()->where('status_result', 'genuine')->count(),
                'invalid' => ScanLog::query()->where('status_result', 'invalid')->count(),
                'flagged' => ScanLog::query()->whereIn('status_result', ['suspicious', 'revoked', 'expired', 'recalled'])->count(),
                'mobile' => ScanLog::query()->where('device_type', 'mobile')->count(),
                'desktop' => ScanLog::query()->where('device_type', 'desktop')->count(),
            ],
            'analyticsReport' => $analyticsReport,
            'users' => User::query()->with('company')->latest()->get(),
            'userSummary' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('status', 'active')->count(),
                'owners' => User::query()->where('role', 'owner')->count(),
                'operators' => User::query()->where('role', 'operator')->count(),
                'company_users' => User::query()->whereNotNull('company_id')->count(),
            ],
            'labelOrders' => LabelOrder::query()
                ->with(['company', 'batch.product'])
                ->withCount('labelCodes')
                ->latest()
                ->get(),
            'recentLabelOrders' => LabelOrder::query()
                ->with(['company', 'batch.product'])
                ->withCount('labelCodes')
                ->latest()
                ->take(3)
                ->get(),
            'labelCodeSummary' => [
                'total' => LabelCode::query()->count(),
                'unused' => LabelCode::query()->where('status', 'unused')->count(),
                'verified' => LabelCode::query()->where('verification_count', '>', 0)->count(),
                'suspicious' => LabelCode::query()->where('status', 'suspicious')->count(),
            ],
            'labelCodes' => $labelCodesQuery
                ->paginate($labelCodePerPage, ['*'], 'code_page')
                ->withQueryString(),
            'labelCodeFilters' => [
                'search' => $labelCodeSearch,
                'status' => $labelCodeStatus,
                'order' => $labelCodeOrder,
                'order_search' => $labelCodeOrderSearch,
                'per_page' => $labelCodePerPage,
            ],
            'labelOrderOptions' => $labelOrderOptions,
            'selectedLabelOrder' => $selectedLabelOrder,
            'recentActivityLogs' => $activityLogs,
            'activityFilters' => [
                'company' => $activityCompanyId,
                'batch' => $activityBatchId,
                'event' => $activityEvent,
            ],
            'activityEvents' => Schema::hasTable('activity_logs')
                ? ActivityLog::query()->select('event')->distinct()->orderBy('event')->pluck('event')
                : collect(),
            'activitySummary' => [
                'total' => $activityTotal,
                'today' => $activityToday,
                'qr_events' => $activityQrEvents,
                'actors' => $activityActorCount,
            ],
            'activityEventBreakdown' => $activityEventBreakdown,
        ]);
    })->name('active8.page');
});
