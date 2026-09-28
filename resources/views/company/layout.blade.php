@php
    $user = auth()->user();
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'company.dashboard', 'icon' => 'activity'],
        ['label' => 'Products', 'route' => 'company.products', 'icon' => 'bag'],
        ['label' => 'Batches', 'route' => 'company.batches', 'icon' => 'layers'],
        ['label' => 'Label Orders', 'route' => 'company.label-orders', 'icon' => 'tag'],
        ['label' => 'USSD Codes', 'route' => 'company.ussd-codes', 'icon' => 'code'],
        ['label' => 'Verification Logs', 'route' => 'company.verifications', 'icon' => 'list'],
        ['label' => 'Suspicious Activity', 'route' => 'company.suspicious', 'icon' => 'alert'],
    ];
    $accountItem = ['label' => 'Account', 'route' => 'company.account', 'icon' => 'users', 'matches' => ['company.account*', 'company.profile', 'company.users*']];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $company->name.' | Active8')</title>
    <style>
        :root {
            --blue:#2563eb;
            --blue-light:#eff4ff;
            --gray-50:#f9fafb;
            --gray-100:#f3f4f6;
            --gray-200:#e5e7eb;
            --gray-300:#d1d5db;
            --gray-400:#9ca3af;
            --gray-500:#6b7280;
            --gray-700:#374151;
            --gray-900:#111827;
            --red:#ef4444;
            --red-light:#fef2f2;
            --white:#fff;
            --sidebar-w:220px;
            --radius:10px;
            --font-screen:Inter, "Instrument Sans", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        html { height:100%; }
        body { height:100vh; min-height:100vh; display:flex; overflow:hidden; background:var(--gray-50); color:var(--gray-900); font-family:var(--font-screen); font-size:14px; letter-spacing:0; font-feature-settings:"cv02","cv03","cv04","kern"; text-rendering:optimizeLegibility; -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }
        a, button { text-decoration:none; }
        button, input, textarea, select { font:inherit; }
        button { border:0; cursor:pointer; }
        svg { display:block; flex-shrink:0; }
        .company-shell { display:flex; width:100%; min-width:0; height:100vh; overflow:hidden; }
        .company-mobile-bar { display:none; }
        .company-side { width:var(--sidebar-w); min-width:var(--sidebar-w); height:100vh; display:flex; flex-direction:column; overflow:hidden; background:var(--white); border-right:1px solid var(--gray-200); z-index:10; }
        .company-brand { min-height:72px; display:flex; align-items:center; gap:10px; padding:20px 20px 16px; border-bottom:1px solid var(--gray-100); }
        .company-mark { display:flex; align-items:center; width:96px; }
        .company-mark img { display:block; width:100%; height:auto; }
        .company-brand span { display:none; }
        .company-menu-button, .company-menu-close { width:38px; height:38px; display:none; align-items:center; justify-content:center; flex-shrink:0; border:1px solid var(--gray-200); border-radius:10px; background:var(--white); color:var(--gray-700); box-shadow:0 8px 18px rgba(15,23,42,.05); }
        .company-menu-button:hover, .company-menu-close:hover { border-color:rgba(37,99,235,.28); background:var(--blue-light); color:var(--blue); }
        .company-menu-backdrop { display:none; }
        .company-card { margin:12px; padding:12px; border:1px solid var(--gray-200); border-radius:var(--radius); background:var(--gray-50); }
        .company-card-title { color:var(--gray-900); font-size:13px; font-weight:800; line-height:1.25; }
        .muted { color:var(--gray-500); font-size:13px; line-height:1.45; }
        .company-nav { flex:1; display:block; padding:0 12px 12px; overflow-y:auto; }
        .company-nav a, .company-footer-link, .company-logout { width:100%; min-height:38px; display:flex; align-items:center; gap:10px; margin-bottom:2px; padding:9px 12px; border-radius:8px; background:transparent; color:var(--gray-700); font-size:14px; font-weight:500; text-align:left; transition:background .15s,color .15s; }
        .company-nav svg, .company-footer-link svg, .company-logout svg { width:16px; height:16px; color:currentColor; }
        .company-nav a:hover, .company-footer-link:hover, .company-logout:hover { background:var(--gray-100); }
        .company-nav a.active, .company-footer-link.active { background:var(--blue); color:var(--white); }
        .company-footer { padding:12px; border-top:1px solid var(--gray-100); }
        .company-session { margin-bottom:10px; padding:10px 12px; border:1px solid var(--gray-200); border-radius:8px; background:var(--gray-50); }
        .company-session strong { display:block; color:var(--gray-900); font-size:13px; font-weight:800; line-height:1.25; overflow-wrap:anywhere; }
        .company-session span { display:block; margin-top:2px; color:var(--gray-500); font-size:12px; font-weight:600; line-height:1.25; text-transform:capitalize; }
        .company-logout { margin:0; color:var(--gray-700); }
        .company-side form { padding:0; border-top:0; }
        .company-main { flex:1; min-width:0; min-height:0; height:100vh; overflow:hidden; display:flex; flex-direction:column; }
        .company-topbar { flex-shrink:0; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px 28px; background:var(--white); border-bottom:1px solid var(--gray-200); margin:0; }
        .company-topbar > div:first-child { min-width:0; }
        .company-title-row { display:flex; align-items:center; gap:8px; min-width:0; }
        .company-title-icon { width:28px; height:28px; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid var(--gray-200); border-radius:8px; background:var(--gray-50); color:var(--gray-500); }
        .company-title-icon svg { width:16px; height:16px; }
        .company-title { min-width:0; color:var(--gray-900); font-size:20px; font-weight:700; line-height:1.2; letter-spacing:0; overflow-wrap:anywhere; }
        .company-subtitle { margin-top:2px; color:var(--gray-500); font-size:13px; line-height:1.4; }
        .company-content { flex:1; min-height:0; overflow-y:auto; padding:24px 28px; -webkit-overflow-scrolling:touch; }
        .company-pill { display:inline-flex; align-items:center; min-height:24px; padding:4px 9px; border-radius:999px; background:var(--gray-100); color:var(--gray-700); font-size:12px; font-weight:700; text-transform:capitalize; }
        .company-btn, .btn-learn, .btn-share { display:inline-flex; align-items:center; justify-content:center; gap:7px; border-radius:8px; font-size:13.5px; font-weight:600; white-space:nowrap; }
        .company-btn, .btn-learn { padding:8px 14px; background:var(--white); border:1px solid var(--gray-200); color:var(--gray-900); }
        .company-btn:hover, .btn-learn:hover { background:var(--gray-50); }
        .btn-share { padding:8px 16px; background:var(--blue); color:var(--white); }
        .company-panel, .admin-card { margin-bottom:20px; padding:20px; border:1px solid var(--gray-200); border-radius:var(--radius); background:var(--white); box-shadow:0 10px 28px rgba(17,24,39,.07); }
        .company-panel-head, .admin-card-header { position:relative; display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:16px; padding:0; border:0; }
        .company-panel-head > div, .admin-card-header > div { min-width:0; }
        .company-panel-title, .admin-card-title { color:var(--gray-900); font-size:17px; font-weight:700; }
        .admin-card-sub { margin-top:3px; color:var(--gray-500); font-size:13px; }
        .company-panel-body { padding:0; }
        .company-list { display:grid; gap:0; }
        .company-item { display:grid; grid-template-columns:minmax(0, 1fr) auto; gap:14px; padding:13px 0; border-bottom:1px solid var(--gray-100); }
        .company-item:last-child { border-bottom:0; }
        .company-item-title { color:var(--gray-900); font-weight:700; }
        .company-status, .status-pill { display:inline-flex; align-items:center; min-height:24px; padding:4px 9px; border-radius:999px; background:var(--gray-100); color:var(--gray-700); font-size:12px; font-weight:700; text-transform:capitalize; }
        .company-status.active, .company-status.approved, .company-status.printed, .company-status.genuine, .company-status.shipped, .company-status.delivered, .status-pill.active, .status-pill.approved, .status-pill.printed, .status-pill.genuine, .status-pill.shipped, .status-pill.delivered { background:#ecfdf3; color:#067647; }
        .company-status.invalid, .company-status.suspicious, .company-status.revoked, .company-status.expired, .company-status.recalled, .status-pill.invalid, .status-pill.suspicious, .status-pill.revoked, .status-pill.expired, .status-pill.recalled { background:var(--red-light); color:var(--red); }
        .company-status.pending, .company-status.draft, .company-status.invited, .status-pill.pending, .status-pill.draft, .status-pill.invited { background:#fffaeb; color:#b54708; }
        .company-status.suspended, .company-status.cancelled, .status-pill.suspended, .status-pill.cancelled { background:var(--red-light); color:var(--red); }
        .company-empty { padding:24px 0; color:var(--gray-500); }
        .admin-table { width:100%; min-width:720px; border-collapse:collapse; }
        .admin-table th { padding:10px 0; border-bottom:1px solid var(--gray-200); color:var(--gray-400); font-size:11px; font-weight:700; text-align:left; text-transform:uppercase; letter-spacing:.05em; }
        .admin-table td { padding:13px 0; border-bottom:1px solid var(--gray-100); color:var(--gray-700); font-size:13px; vertical-align:top; }
        .admin-table th:not(:last-child), .admin-table td:not(:last-child) { padding-right:28px; }
        .admin-table tr:last-child td { border-bottom:0; }
        .admin-table-wrap { width:100%; overflow-x:auto; }
        .row-clickable { cursor:pointer; }
        .row-clickable:hover td { background:var(--gray-50); }
        .table-toolbar { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:-2px 0 14px; flex-wrap:wrap; }
        .table-search { position:relative; flex:1; min-width:220px; }
        .table-search input { width:100%; height:36px; padding:8px 10px 8px 14px; border:1px solid var(--gray-200); border-radius:8px; background:var(--white); color:var(--gray-900); font-size:13px; outline:none; }
        .table-filter-row { display:flex; align-items:flex-end; gap:8px; flex-wrap:wrap; }
        .filter-control { display:grid; gap:4px; color:var(--gray-500); font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; }
        .filter-control select { height:36px; min-width:128px; padding:7px 30px 7px 10px; border:1px solid var(--gray-200); border-radius:8px; background:var(--white); color:var(--gray-900); font-size:12px; font-weight:700; text-transform:none; letter-spacing:0; outline:none; }
        .table-search input:focus, .filter-control select:focus { border-color:rgba(37,99,235,.55); box-shadow:0 0 0 4px rgba(37,99,235,.09); }
        .company-table-filter-toggle { display:none; align-items:center; justify-content:center; gap:7px; min-height:38px; padding:8px 12px; border:1px solid var(--gray-200); border-radius:8px; background:var(--white); color:var(--gray-900); font-size:13px; font-weight:800; white-space:nowrap; }
        .company-table-filter-toggle:hover { border-color:rgba(37,99,235,.28); background:var(--blue-light); color:var(--blue); }
        .company-table-filter-toggle svg:last-child { transition:transform .18s ease; }
        .table-toolbar.filters-open .company-table-filter-toggle svg:last-child { transform:rotate(180deg); }
        .sort-link { display:inline-flex; align-items:center; gap:6px; color:inherit; }
        .sort-link span { color:var(--gray-400); font-size:11px; }
        .sort-link.active { color:var(--blue); }
        .sort-link.active span { color:var(--blue); }
        .table-pagination { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-top:14px; padding:12px; border:1px solid var(--gray-100); border-radius:12px; background:linear-gradient(180deg, #fff, #f8fbff); color:var(--gray-500); font-size:12px; font-weight:850; box-shadow:0 10px 26px rgba(15,23,42,.04); }
        .table-pagination > span:first-child { display:inline-flex; align-items:center; gap:8px; min-height:30px; }
        .table-pagination > span:first-child::before { content:""; width:8px; height:8px; border-radius:999px; background:var(--blue); box-shadow:0 0 0 4px var(--blue-light); }
        .pager-actions { display:inline-flex; align-items:center; gap:7px; padding:3px; border:1px solid var(--gray-100); border-radius:11px; background:var(--white); box-shadow:inset 0 0 0 1px rgba(255,255,255,.7); }
        .pager-actions > span { min-width:54px; height:30px; display:inline-flex; align-items:center; justify-content:center; padding:0 10px; border-radius:8px; background:var(--gray-50); color:var(--gray-700); font-size:12px; font-weight:900; white-space:nowrap; }
        .pager-btn { width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border:1px solid transparent; border-radius:8px; background:var(--white); color:var(--gray-700); font-size:15px; font-weight:900; line-height:1; transition:background .16s ease,border-color .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease; }
        .pager-btn:hover:not(:disabled) { border-color:rgba(37,99,235,.28); background:var(--blue-light); color:var(--blue); box-shadow:0 6px 16px rgba(37,99,235,.1); transform:translateY(-1px); }
        .pager-btn:focus-visible, .pager-number:focus-visible { outline:3px solid rgba(37,99,235,.18); outline-offset:2px; }
        .pager-btn:disabled { color:var(--gray-300); cursor:not-allowed; opacity:.62; }
        .pager-number, .pager-ellipsis { min-width:30px; height:30px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; color:var(--gray-700); font-size:12px; font-weight:900; }
        .pager-number { border:1px solid transparent; background:var(--white); transition:background .16s ease,border-color .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease; }
        .pager-number:hover { border-color:rgba(37,99,235,.28); background:var(--blue-light); color:var(--blue); box-shadow:0 6px 16px rgba(37,99,235,.1); transform:translateY(-1px); }
        .pager-number.active { border-color:var(--blue); background:var(--blue); color:var(--white); box-shadow:0 8px 18px rgba(37,99,235,.22); }
        .pager-ellipsis { color:var(--gray-400); }
        .company-empty-state { display:flex; align-items:center; gap:14px; margin-top:12px; padding:18px; border:1px dashed var(--gray-300); border-radius:12px; background:linear-gradient(180deg, #fff, #f8fbff); }
        .company-empty-icon { width:38px; height:38px; display:grid; place-items:center; flex-shrink:0; border-radius:10px; background:var(--blue-light); color:var(--blue); font-weight:950; }
        .company-empty-title { color:var(--gray-900); font-size:14px; font-weight:900; }
        .company-empty-copy { margin-top:3px; color:var(--gray-500); font-size:13px; line-height:1.45; }
        .details { display:grid; gap:0; }
        .detail-row { display:grid; grid-template-columns:220px minmax(0, 1fr); gap:18px; padding:13px 0; border-bottom:1px solid var(--gray-100); }
        .detail-row:last-child { border-bottom:0; }
        .detail-label { color:var(--gray-500); font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; }
        .detail-value { color:var(--gray-900); font-weight:700; }
        .company-detail-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:12px; margin-bottom:16px; }
        .company-detail-card { padding:14px; border:1px solid var(--gray-200); border-radius:var(--radius); background:var(--gray-50); box-shadow:0 8px 20px rgba(17,24,39,.05); }
        .company-detail-label { color:var(--gray-500); font-size:12px; font-weight:600; }
        .company-detail-value { margin-top:4px; color:var(--gray-900); font-size:18px; font-weight:700; }
        .company-toast { position:fixed; top:18px; right:18px; z-index:120; width:min(380px, calc(100vw - 36px)); display:flex; align-items:flex-start; gap:12px; padding:14px 14px 14px 13px; border:1px solid rgba(187,247,208,.95); border-radius:14px; background:rgba(255,255,255,.96); color:#065f46; box-shadow:0 22px 54px rgba(15,23,42,.18); backdrop-filter:blur(12px); animation:company-toast-in .24s cubic-bezier(.2,.8,.2,1) both; }
        .company-toast-icon { width:28px; height:28px; display:grid; place-items:center; flex-shrink:0; border-radius:9px; background:#dcfce7; color:#047857; }
        .company-toast-body { min-width:0; flex:1; }
        .company-toast-title { color:#064e3b; font-size:13px; font-weight:950; line-height:1.25; }
        .company-toast-message { margin-top:2px; color:#047857; font-size:12px; font-weight:750; line-height:1.4; }
        .company-toast-close { width:26px; height:26px; display:grid; place-items:center; flex-shrink:0; border-radius:8px; background:transparent; color:#059669; font-size:18px; line-height:1; }
        .company-toast-close:hover { background:#ecfdf3; color:#065f46; }
        .company-toast.toast-hide { animation:company-toast-out .2s ease both; }
        @keyframes company-toast-in { from { opacity:0; transform:translateY(-10px) scale(.98); } to { opacity:1; transform:translateY(0) scale(1); } }
        @keyframes company-toast-out { to { opacity:0; transform:translateY(-10px) scale(.98); } }
        @media (max-width: 980px) {
            body { overflow:auto; display:block; }
            body.company-menu-open { overflow:hidden; }
            .company-shell { display:block; height:auto; min-height:100vh; overflow:visible; }
            .company-mobile-bar { position:sticky; top:0; z-index:60; display:flex; align-items:center; justify-content:space-between; gap:14px; min-height:58px; padding:10px 16px; border-bottom:1px solid var(--gray-200); background:rgba(255,255,255,.96); backdrop-filter:blur(16px); }
            .company-mobile-brand { min-width:0; display:flex; align-items:center; gap:10px; }
            .company-mobile-logo { width:68px; flex-shrink:0; }
            .company-mobile-logo img { display:block; width:100%; height:auto; }
            .company-mobile-meta { display:none; }
            .company-menu-button, .company-menu-close { display:inline-flex; }
            .company-menu-backdrop { position:fixed; inset:0; z-index:75; display:block; background:rgba(17,24,39,.42); opacity:0; pointer-events:none; transition:opacity .2s ease; }
            body.company-menu-open .company-menu-backdrop { opacity:1; pointer-events:auto; }
            .company-side { position:fixed; inset:0 auto 0 0; z-index:80; width:min(332px, calc(100vw - 38px)); min-width:0; height:100dvh; border-right:1px solid var(--gray-200); border-bottom:0; box-shadow:24px 0 60px rgba(15,23,42,.22); transform:translateX(-105%); transition:transform .24s cubic-bezier(.2,.8,.2,1); }
            .company-side.is-open { transform:translateX(0); }
            .company-brand { min-height:66px; justify-content:space-between; padding:14px 14px 12px 18px; }
            .company-mark { width:88px; }
            .company-card { margin:12px; }
            .company-nav { display:block; padding:0 12px 12px; overflow-y:auto; }
            .company-main { height:auto; min-height:0; overflow:visible; }
            .company-content { padding:18px; overflow:visible; }
            .company-topbar { padding:16px 18px; }
            .detail-row { grid-template-columns:1fr; gap:5px; }
            .company-panel, .admin-card { overflow-x:auto; }
            .company-detail-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); }
            .table-toolbar { align-items:stretch; flex-direction:column; }
            .company-table-filter-toggle { display:inline-flex; width:100%; }
            .table-toolbar.has-collapsible-filters .table-filter-row { display:none; width:100%; padding:12px; border:1px solid var(--gray-200); border-radius:12px; background:linear-gradient(180deg, #fff, #f8fbff); }
            .table-toolbar.filters-open .table-filter-row, .table-toolbar.has-collapsible-filters .table-filter-row.is-open { display:flex; align-items:stretch; flex-direction:column; }
            .table-search, .filter-control, .filter-control select, .table-filter-row .btn-share, .table-filter-row .btn-learn { width:100%; }
            .table-pagination { align-items:flex-start; flex-direction:column; }
            .pager-actions { justify-content:flex-start; max-width:100%; overflow-x:auto; }
            .company-toast { top:12px; right:12px; left:12px; width:auto; }
        }
        @media (max-width: 760px) {
            .company-side { overflow:hidden; }
            .company-brand { border-bottom:1px solid var(--gray-100); }
            .company-nav a { width:100%; min-width:0; flex:initial; margin-bottom:2px; padding:10px 12px; white-space:normal; }
            .company-footer { display:block; padding:12px; }
            .company-session { margin-bottom:10px; }
            .company-footer-link, .company-logout { margin-bottom:2px; justify-content:flex-start; }
            .company-topbar { align-items:flex-start; flex-direction:column; gap:12px; }
            .company-topbar > .company-pill, .company-topbar > .company-btn, .company-topbar > .btn-learn, .company-topbar > .btn-share, .company-topbar > a { align-self:flex-start; }
            .company-content { padding:14px; }
            .company-panel, .admin-card { padding:16px; border-radius:9px; }
            .company-panel-head, .admin-card-header { align-items:stretch; flex-direction:column; gap:10px; }
            .company-panel-head .btn-learn, .company-panel-head .btn-share, .admin-card-header .btn-learn, .admin-card-header .btn-share { align-self:flex-start; }
            .company-item { grid-template-columns:1fr; gap:8px; }
            .company-status, .status-pill { width:max-content; max-width:100%; }
            .company-empty-state { align-items:flex-start; }
        }
        @media (max-width: 520px) {
            .company-title { font-size:18px; }
            .company-subtitle { font-size:12.5px; }
            .company-footer { grid-template-columns:1fr; }
            .company-footer-link, .company-logout { justify-content:flex-start; }
            .company-detail-grid { grid-template-columns:1fr; }
            .table-pagination { padding:10px; }
            .pager-actions { width:100%; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @if (session('status'))
        <div class="company-toast" role="status" aria-live="polite" data-company-toast>
            <div class="company-toast-icon" aria-hidden="true">
                <svg width="16" height="16"><use href="#company-i-check"></use></svg>
            </div>
            <div class="company-toast-body">
                <div class="company-toast-title">Saved</div>
                <div class="company-toast-message">{{ session('status') }}</div>
            </div>
            <button class="company-toast-close" type="button" aria-label="Dismiss notification" data-company-toast-close>×</button>
        </div>
    @endif

    <svg width="0" height="0" style="position:absolute;visibility:hidden" aria-hidden="true" focusable="false">
        <symbol id="company-i-activity" viewBox="0 0 24 24"><path d="M3 12h4l3-8 4 16 3-8h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="company-i-bag" viewBox="0 0 24 24"><path d="M6 7h12l1 14H5L6 7Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M9 7a3 3 0 0 1 6 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="company-i-layers" viewBox="0 0 24 24"><path d="m12 3 9 5-9 5-9-5 9-5Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="m3 12 9 5 9-5M3 16l9 5 9-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="company-i-tag" viewBox="0 0 24 24"><path d="M20 13 13 20 4 11V4h7l9 9Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/></symbol>
        <symbol id="company-i-code" viewBox="0 0 24 24"><path d="m8 9-4 3 4 3M16 9l4 3-4 3M14 5l-4 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="company-i-list" viewBox="0 0 24 24"><path d="M8 6h13M8 12h13M8 18h13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="3.5" cy="6" r="1.5" fill="currentColor"/><circle cx="3.5" cy="12" r="1.5" fill="currentColor"/><circle cx="3.5" cy="18" r="1.5" fill="currentColor"/></symbol>
        <symbol id="company-i-alert" viewBox="0 0 24 24"><path d="M12 3 22 20H2L12 3Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M12 9v5M12 17h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="company-i-building" viewBox="0 0 24 24"><path d="M4 21V5a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v16M3 21h18M8 7h2M8 11h2M8 15h2M14 7h.01M14 11h.01M14 15h.01" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="company-i-users" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-8 0v2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M22 21v-2a4 4 0 0 0-3-3.6M18 3.3a4 4 0 0 1 0 7.4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></symbol>
        <symbol id="company-i-logout" viewBox="0 0 24 24"><path d="M10 17 15 12l-5-5M15 12H3M21 3v18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></symbol>
        <symbol id="company-i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></symbol>
    </svg>
    <header class="company-mobile-bar">
        <div class="company-mobile-brand">
            <a class="company-mobile-logo" href="{{ route('company.dashboard') }}" aria-label="Go to company dashboard"><img src="{{ asset('images/active8-logo.png') }}" alt="Active8"></a>
            <div class="company-mobile-meta">
                <div class="company-mobile-company">{{ $company->name }}</div>
                <div class="company-mobile-role">{{ str_replace('_', ' ', $user?->role ?? 'company user') }}</div>
            </div>
        </div>
        <button class="company-menu-button" type="button" aria-label="Open company menu" aria-controls="company-navigation" aria-expanded="false" data-company-menu-open>
            <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </header>
    <button class="company-menu-backdrop" type="button" aria-label="Close company menu" data-company-menu-close></button>
    <div class="company-shell">
        <aside class="company-side" id="company-navigation" aria-label="Company menu">
            <div class="company-brand">
                <a class="company-mark" href="{{ route('company.dashboard') }}" aria-label="Go to company dashboard"><img src="{{ asset('images/active8-logo.png') }}" alt="Active8"></a>
                <span>Company Portal</span>
                <button class="company-menu-close" type="button" aria-label="Close company menu" data-company-menu-close>
                    <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            <div class="company-card">
                <div class="company-card-title">{{ $company->name }}</div>
            </div>
            <nav class="company-nav" aria-label="Company navigation">
                @foreach ($navItems as $item)
                    @php
                        $matches = $item['matches'] ?? [$item['route'], $item['route'].'.show'];
                    @endphp
                    <a class="{{ request()->routeIs(...$matches) ? 'active' : '' }}" href="{{ route($item['route']) }}">
                        <svg width="16" height="16"><use href="#company-i-{{ $item['icon'] }}"></use></svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="company-footer">
                <div class="company-session">
                    <strong>{{ $user?->name }}</strong>
                    <span>{{ str_replace('_', ' ', $user?->role ?? 'company user') }}</span>
                </div>
                <a class="company-footer-link {{ request()->routeIs(...$accountItem['matches']) ? 'active' : '' }}" href="{{ route($accountItem['route']) }}">
                    <svg width="16" height="16"><use href="#company-i-{{ $accountItem['icon'] }}"></use></svg>
                    {{ $accountItem['label'] }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="company-logout" type="submit">
                        <svg width="16" height="16"><use href="#company-i-logout"></use></svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </aside>

        <main class="company-main">
            @yield('content')
        </main>
    </div>
    <script>
        document.querySelectorAll('[data-company-toast]').forEach((toast) => {
            const closeToast = () => {
                toast.classList.add('toast-hide');
                setTimeout(() => toast.remove(), 220);
            };

            toast.querySelector('[data-company-toast-close]')?.addEventListener('click', closeToast);
            setTimeout(closeToast, 4200);
        });

        document.querySelectorAll('[data-company-table-search]').forEach((input) => {
            const table = document.querySelector(input.dataset.companyTableSearch);
            if (!table) return;

            input.addEventListener('input', () => {
                const needle = input.value.trim().toLowerCase();
                table.querySelectorAll('tbody tr[data-search]').forEach((row) => {
                    row.style.display = row.dataset.search.includes(needle) ? '' : 'none';
                });
            });
        });

        document.querySelectorAll('.table-toolbar').forEach((toolbar, index) => {
            const filterRow = toolbar.querySelector('.table-filter-row');
            if (!filterRow) return;

            let toggle = toolbar.querySelector('[data-company-filter-toggle], [data-company-table-filter-toggle]');
            const filterId = filterRow.id || `company-table-filters-${index + 1}`;
            filterRow.id = filterId;
            toolbar.classList.add('has-collapsible-filters');

            if (!toggle) {
                toggle = document.createElement('button');
                toggle.className = 'company-table-filter-toggle';
                toggle.type = 'button';
                toggle.dataset.companyTableFilterToggle = '';
                toggle.innerHTML = `
                    <svg width="15" height="15" aria-hidden="true"><use href="#company-i-list"></use></svg>
                    <span data-company-filter-label>Show filters</span>
                    <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                `;
                toolbar.insertBefore(toggle, filterRow);
            } else {
                toggle.classList.add('company-table-filter-toggle');
                if (!toggle.querySelector('[data-company-filter-label]')) {
                    toggle.innerHTML = `
                        <svg width="15" height="15" aria-hidden="true"><use href="#company-i-list"></use></svg>
                        <span data-company-filter-label>Show filters</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                }
            }

            toggle.setAttribute('aria-controls', filterId);

            const toggleLabel = toggle.querySelector('[data-company-filter-label]');
            const activeFilterCount = () => {
                let count = 0;

                filterRow.querySelectorAll('select').forEach((select) => {
                    if (select.name === 'per_page') return;
                    if (select.value && select.value !== 'all') count += 1;
                });

                filterRow.querySelectorAll('input').forEach((input) => {
                    if (['hidden', 'submit', 'button'].includes(input.type)) return;
                    if (input.value.trim() !== '') count += 1;
                });

                return count;
            };

            const setFiltersOpen = (isOpen) => {
                toolbar.classList.toggle('filters-open', isOpen);
                filterRow.classList.toggle('is-open', isOpen);
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                const count = activeFilterCount();
                if (toggleLabel) {
                    toggleLabel.textContent = `${isOpen ? 'Hide' : 'Show'} filters${count ? ` (${count})` : ''}`;
                }
            };

            toggle.addEventListener('click', () => {
                setFiltersOpen(! toolbar.classList.contains('filters-open'));
            });
            filterRow.addEventListener('input', () => setFiltersOpen(toolbar.classList.contains('filters-open')));
            filterRow.addEventListener('change', () => setFiltersOpen(toolbar.classList.contains('filters-open')));
            setFiltersOpen(activeFilterCount() > 0 || filterRow.classList.contains('is-open'));
        });

        (() => {
            const body = document.body;
            const menu = document.getElementById('company-navigation');
            const openButton = document.querySelector('[data-company-menu-open]');
            const closeButtons = document.querySelectorAll('[data-company-menu-close]');
            const desktopQuery = window.matchMedia('(min-width: 981px)');

            if (!menu || !openButton) return;

            const setMenu = (isOpen) => {
                body.classList.toggle('company-menu-open', isOpen);
                menu.classList.toggle('is-open', isOpen);
                openButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            };

            openButton.addEventListener('click', () => setMenu(true));
            closeButtons.forEach((button) => button.addEventListener('click', () => setMenu(false)));
            menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setMenu(false)));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') setMenu(false);
            });

            desktopQuery.addEventListener('change', (event) => {
                if (event.matches) setMenu(false);
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
