@php
    $company = $company ?? null;
    $companyProfile = $company?->profile;
    $companies = $companies ?? collect();
    $products = $products ?? collect();
    $batches = $batches ?? collect();
    $dashboardStats = $dashboardStats ?? [];
    $recentBatches = $recentBatches ?? collect();
    $recentScans = $recentScans ?? collect();
    $scanLogs = $scanLogs ?? collect();
    $scanSummary = $scanSummary ?? ['total' => 0, 'genuine' => 0, 'invalid' => 0, 'flagged' => 0, 'mobile' => 0, 'desktop' => 0];
    $analyticsReport = $analyticsReport ?? [
        'days' => 30,
        'range' => '30',
        'label' => 'Last 30 days',
        'ranges' => [
            '30' => ['label' => 'Last 30 days'],
            '90' => ['label' => 'Last 90 days'],
            '365' => ['label' => 'Last 365 days'],
            'last_year' => ['label' => 'Last Year'],
            'this_year' => ['label' => 'This year'],
        ],
        'status_filter' => 'all',
        'status_filters' => ['all' => 'All statuses'],
        'device_filter' => 'all',
        'device_filters' => ['all' => 'All devices'],
        'company_filter' => 'all',
        'company_filters' => ['all' => 'All companies'],
        'product_filter' => 'all',
        'product_filters' => ['all' => 'All products'],
        'batch_filter' => 'all',
        'batch_filters' => ['all' => 'All batches'],
        'period_start' => now()->subDays(29)->startOfDay(),
        'period_end' => now()->endOfDay(),
        'period_display' => 'Last 30 days',
        'calendar_months' => collect(),
        'total' => 0,
        'genuine' => 0,
        'flagged' => 0,
        'unique_tokens' => 0,
        'repeat_scans' => 0,
        'chart_path' => 'M0,240 L900,240',
        'chart_y_labels' => [['value' => 1, 'y' => 20], ['value' => 0, 'y' => 240]],
        'x_labels' => [],
        'status_counts' => [],
        'device_counts' => [],
        'browser_counts' => [],
        'device_dash' => '0 430',
        'top_batches' => collect(),
        'top_products' => collect(),
    ];
    $labelOrders = $labelOrders ?? collect();
    $recentLabelOrders = $recentLabelOrders ?? $labelOrders->take(3);
    $recentActivityLogs = $recentActivityLogs ?? collect();
    $activityFilters = $activityFilters ?? ['company' => 'all', 'batch' => 'all', 'event' => 'all'];
    $activityEvents = $activityEvents ?? collect();
    $users = $users ?? collect();
    $userSummary = $userSummary ?? ['total' => 0, 'active' => 0, 'owners' => 0, 'operators' => 0];
    $featuredQrCode = $recentBatches->first(fn ($batch) => $batch->qrCode !== null)?->qrCode
        ?? $batches->first(fn ($batch) => $batch->qrCode !== null)?->qrCode;
    $dashboardBatchTotal = max($batches->count(), 1);
    $dashboardQrReady = $batches->filter(fn ($batch) => $batch->qrCode !== null)->count();
    $dashboardQrCoverage = round(($dashboardQrReady / $dashboardBatchTotal) * 100);
    $dashboardScanTotal = max((int) ($scanSummary['total'] ?? 0), 1);
    $dashboardGenuineRate = round(((int) ($scanSummary['genuine'] ?? 0) / $dashboardScanTotal) * 100);
    $dashboardFlaggedRate = round(((int) ($scanSummary['flagged'] ?? 0) / $dashboardScanTotal) * 100);
    $dashboardMobileRate = round(((int) ($scanSummary['mobile'] ?? 0) / $dashboardScanTotal) * 100);
    $dashboardDesktopRate = round(((int) ($scanSummary['desktop'] ?? 0) / $dashboardScanTotal) * 100);
    $dashboardOpenOrders = $labelOrders->whereIn('status', ['requested', 'pending', 'approved', 'in_production'])->count();
    $dashboardMissingQr = $batches->filter(fn ($batch) => $batch->qrCode === null)->count();
    $dashboardPendingBatches = $batches->whereIn('status', ['draft', 'pending'])->count();
    $dashboardAlerts = collect([
        [
            'tone' => ((int) ($scanSummary['flagged'] ?? 0)) > 0 ? 'danger' : 'success',
            'title' => number_format((int) ($scanSummary['flagged'] ?? 0)).' flagged '.Str::plural('scan', (int) ($scanSummary['flagged'] ?? 0)),
            'copy' => ((int) ($scanSummary['flagged'] ?? 0)) > 0 ? 'Review suspicious, invalid, revoked, recalled, and expired scans.' : 'No flagged scans recorded yet.',
            'href' => route('active8.page', ['page' => 'scan-logs', 'status' => 'suspicious']),
        ],
        [
            'tone' => $dashboardMissingQr > 0 ? 'warning' : 'success',
            'title' => number_format($dashboardMissingQr).' '.Str::plural('batch', $dashboardMissingQr).' missing QR',
            'copy' => $dashboardMissingQr > 0 ? 'Generate codes before label production starts.' : 'All tracked batches have QR coverage.',
            'href' => route('active8.page', 'batches'),
        ],
        [
            'tone' => $dashboardOpenOrders > 0 ? 'info' : 'success',
            'title' => number_format($dashboardOpenOrders).' open label '.Str::plural('order', $dashboardOpenOrders),
            'copy' => $dashboardOpenOrders > 0 ? 'Keep print jobs moving through production.' : 'No open print orders waiting.',
            'href' => route('active8.page', 'label-orders'),
        ],
    ]);
    $authUser = auth()->user();

    $profile = [
        'name' => $authUser?->name ?? 'System Admin',
        'initials' => $authUser
            ? Str::of($authUser->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('')
            : 'SA',
        'job' => $authUser ? ucfirst($authUser->role).' access' : 'Anti-counterfeit operations',
        'location' => $company ? trim(collect([$company->city, $company->country])->filter()->implode(', ')) : 'Accra, Ghana',
        'company' => $company?->name ?? 'Verification Platform',
        'email' => $company?->email ?? 'admin@example.com',
        'phone' => $company?->phone ?? '+233 50 121 7190',
        'url' => $company ? url('/verify/'.$company->slug) : url('/admin/dashboard'),
    ];

    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'activity'],
        ['key' => 'companies', 'label' => 'Companies', 'icon' => 'users'],
        ['key' => 'products', 'label' => 'Products', 'icon' => 'bag'],
        ['key' => 'categories', 'label' => 'Categories', 'icon' => 'form'],
        ['key' => 'batches', 'label' => 'Batches', 'icon' => 'form'],
        ['key' => 'scan-analytics', 'label' => 'Scan Analytics', 'icon' => 'eye'],
        ['key' => 'scan-logs', 'label' => 'Scan Logs', 'icon' => 'link'],
        ['key' => 'audit-trail', 'label' => 'Audit Trail', 'icon' => 'activity'],
        ['key' => 'label-orders', 'label' => 'Label Orders', 'icon' => 'qr'],
    ];

    $editorTabs = [
        ['key' => 'about', 'label' => 'About', 'icon' => 'user'],
        ['key' => 'content', 'label' => 'Content', 'icon' => 'edit'],
    ];

    $analytics = [
        ['label' => 'Total Scans', 'value' => $analyticsReport['total'], 'icon' => 'eye'],
        ['label' => 'Genuine Scans', 'value' => $analyticsReport['genuine'], 'icon' => 'activity'],
        ['label' => 'Flagged Scans', 'value' => $analyticsReport['flagged'], 'icon' => 'cursor'],
    ];

    $shareActions = [
        ['label' => 'Share via SMS', 'icon' => 'message'],
        ['label' => 'Share via Email', 'icon' => 'mail'],
        ['label' => 'View Profile', 'icon' => 'globe'],
        ['label' => 'Add to my wallet', 'icon' => 'wallet'],
        ['label' => 'Download the App', 'icon' => 'download'],
        ['label' => 'Download QR Code', 'icon' => 'qr'],
        ['label' => 'Offline QR Code', 'icon' => 'offline'],
    ];

    $contentBlocks = [
        [
            'title' => 'Socials',
            'sectionTitle' => 'SOCIAL',
            'items' => [
                ['name' => 'Youtube', 'value' => 'https://youtube.com/watch', 'icon' => 'play', 'class' => 'red'],
            ],
            'button' => 'Add Social',
        ],
        [
            'title' => 'Video',
            'sectionTitle' => '',
            'fields' => [
                ['label' => 'Youtube or Vimeo URL', 'value' => 'https://www.youtube.com/embed/k1Sy0cY8Xqc'],
            ],
        ],
        [
            'title' => 'Images',
            'sectionTitle' => '',
            'images' => true,
        ],
        [
            'title' => 'Link',
            'sectionTitle' => 'LINK',
            'items' => [
                ['name' => 'LINK', 'value' => 'https://github.com/Trapzium-Digital/SCHOLSEC-WEB-APP/...', 'icon' => 'code', 'class' => 'dark'],
            ],
            'button' => 'Add Link',
        ],
        [
            'title' => 'About Me',
            'sectionTitle' => 'This is Manan',
            'bio' => 'This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan This is Manan v',
        ],
    ];

    $stubScreens = [
    ];

    $activePage = $activePage ?? 'dashboard';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Active8</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    @include('admin.layout.styles')

</head>
<body>
    <svg width="0" height="0" style="position:absolute">
        <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-8 0v2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-mail" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="m22 7-10 7L2 7" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-activity" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-bag" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M3 6h18M16 10a4 4 0 0 1-8 0" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-edit" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-trash" viewBox="0 0 24 24"><path d="M3 6h18M8 6V4h8v2M6 6l1 16h10l1-16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="i-globe" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M2 12h20M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-form" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 2v6h6" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-image" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="8.5" cy="8.5" r="1.5" fill="none" stroke="currentColor" stroke-width="2"/><path d="m21 15-5-5L5 21" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-wallet" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M1 10h22" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-cursor" viewBox="0 0 24 24"><path d="M15 15 21 21m-6-6a6 6 0 1 0-8.485-8.485A6 6 0 0 0 15 15Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-filter" viewBox="0 0 24 24"><path d="M3 5h18M7 12h10M10 19h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-search" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="m16 16 5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="i-message" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-download" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" fill="none" stroke="currentColor" stroke-width="2"/><path d="m7 10 5 5 5-5M12 15V3" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-qr" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-offline" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="2"/><path d="M7 7h4v4H7Zm6 6h4v4h-4Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-play" viewBox="0 0 24 24"><path d="m8 5 11 7-11 7Z" fill="currentColor"/></symbol>
        <symbol id="i-code" viewBox="0 0 24 24"><path d="m8 9-4 3 4 3m8-6 4 3-4 3M14 4l-4 16" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 2.1 4.18 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.62 2.61a2 2 0 0 1-.45 2.11L8 9.7a16 16 0 0 0 6.3 6.3l1.26-1.27a2 2 0 0 1 2.11-.45c.84.29 1.71.5 2.61.62A2 2 0 0 1 22 16.9Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
    </svg>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="active8-logo">Active8</span>
            <div class="sidebar-icons">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <svg width="15" height="15"><use href="#i-qr"></use></svg>
            </div>
        </div>
        <nav class="sidebar-nav">
            @foreach ($navItems as $item)
                <a class="nav-item {{ $activePage === $item['key'] || ($activePage === 'company-profile' && $item['key'] === 'dashboard') ? 'active' : '' }}" href="{{ route('active8.page', $item['key']) }}" id="nav-{{ $item['key'] }}">
                    <svg width="15" height="15"><use href="#i-{{ $item['icon'] }}"></use></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-refer" type="submit">
                    <svg width="13" height="13"><use href="#i-link"></use></svg>
                    Sign out
                </button>
            </form>
            <a class="btn-refer {{ $activePage === 'accounts' ? 'active' : '' }}" href="{{ route('active8.page', 'accounts') }}">
                <svg width="13" height="13"><use href="#i-users"></use></svg>
                Accounts
            </a>
            <div class="user-row">
                <div class="user-avatar-sm">{{ $profile['initials'] }}</div>
                <a class="user-name-sm" href="{{ route('active8.page', 'account') }}" style="color:inherit;text-decoration:none">{{ $profile['name'] }}</a>
                <span style="color:var(--gray-400);font-size:12px">⌄</span>
            </div>
        </div>
    </aside>

    <div class="main">
        @include('admin.pages.dashboard')
        @include('admin.pages.company-profile')
        @include('admin.pages.scan-analytics')
        @include('admin.pages.companies')
        @include('admin.pages.categories')
        @include('admin.pages.products')
        @include('admin.pages.batches')
        @include('admin.pages.scan-logs')
        @include('admin.pages.audit-trail')
        @include('admin.pages.label-orders')
        @include('admin.pages.accounts')
        @include('admin.pages.account')

        <div class="delete-alert" id="delete-alert" aria-hidden="true">
            <div class="delete-alert-backdrop" data-delete-cancel></div>
            <div class="delete-alert-card" role="alertdialog" aria-modal="true" aria-labelledby="delete-alert-title" aria-describedby="delete-alert-copy">
                <div class="delete-alert-icon">
                    <svg width="20" height="20"><use href="#i-trash"></use></svg>
                </div>
                <div class="delete-alert-title" id="delete-alert-title">Confirm delete</div>
                <div class="delete-alert-copy" id="delete-alert-copy">This action cannot be undone.</div>
                <input class="delete-alert-input" id="delete-alert-input" type="text" autocomplete="off" placeholder="Type the name to confirm" data-delete-input>
                <div class="delete-alert-actions">
                    <button class="btn-learn" type="button" data-delete-cancel>Cancel</button>
                    <button class="btn-share" type="button" data-delete-confirm disabled>Delete</button>
                </div>
            </div>
        </div>

        @foreach ($stubScreens as $key => $screen)
            <section class="screen {{ $activePage === $key ? 'active' : '' }}" id="screen-{{ $key }}">
                <div class="empty-state">
                    <svg width="40" height="40"><use href="#i-{{ $screen['icon'] }}"></use></svg>
                    <h2>{{ $screen['title'] }}</h2>
                    <p>{{ $screen['copy'] }}</p>
                </div>
            </section>
        @endforeach
    </div>

    @include('admin.layout.scripts')

</body>
</html>
