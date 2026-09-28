@php
    $initials = Str::of($company->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('');
    $location = collect([$company->city, $company->region, $company->country])->filter()->implode(', ') ?: 'Location not recorded';
    $logoUrl = $profile?->logo_path ? '/storage/'.ltrim($profile->logo_path, '/') : null;
    $coverUrl = $profile?->cover_image_path ? '/storage/'.ltrim($profile->cover_image_path, '/') : null;
    $tabs = [
        'overview' => 'Overview',
        'products' => 'Products',
        'batches' => 'Batches & QR Codes',
        'profile' => 'Verification Profile',
        'analytics' => 'Analytics',
        'activity' => 'Activity',
    ];
    $stats = [
        ['label' => 'Products', 'value' => $products->count(), 'icon' => 'bag'],
        ['label' => 'Active Batches', 'value' => $batches->where('status', 'approved')->count(), 'icon' => 'form'],
        ['label' => 'QR Codes', 'value' => $qrCodeCount, 'icon' => 'qr'],
        ['label' => 'Total Scans', 'value' => $scanLogs->count(), 'icon' => 'eye'],
        ['label' => 'Genuine Scans', 'value' => $genuineScans, 'icon' => 'check'],
        ['label' => 'Flagged Scans', 'value' => $flaggedScans, 'icon' => 'alert'],
        ['label' => 'Verification Rate', 'value' => $genuineRate.'%', 'icon' => 'activity'],
        ['label' => 'Label Orders', 'value' => $labelOrders->count(), 'icon' => 'print'],
    ];
    $authUser = auth()->user();
    $userInitials = $authUser
        ? Str::of($authUser->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('')
        : 'SA';
    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'activity'],
        ['key' => 'companies', 'label' => 'Companies', 'icon' => 'users'],
        ['key' => 'products', 'label' => 'Products', 'icon' => 'bag'],
        ['key' => 'categories', 'label' => 'Categories', 'icon' => 'form'],
        ['key' => 'batches', 'label' => 'Batches', 'icon' => 'form'],
        ['key' => 'scan-analytics', 'label' => 'Scan Analytics', 'icon' => 'eye'],
        ['key' => 'scan-logs', 'label' => 'Scan Logs', 'icon' => 'link'],
        ['key' => 'label-orders', 'label' => 'Label Orders', 'icon' => 'qr'],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $company->name }} | Company Workspace</title>
    <style>
        :root { --blue:#2563eb; --blue-soft:#eff6ff; --ink:#101828; --muted:#667085; --line:#e4e7ec; --soft:#f8fafc; --white:#fff; --green:#067647; --green-soft:#ecfdf3; --amber:#b54708; --amber-soft:#fffaeb; --red:#b42318; --red-soft:#fef3f2; --shadow:0 12px 34px rgba(16,24,40,.08); }
        * { box-sizing:border-box; }
        body { height:100vh; display:flex; overflow:hidden; margin:0; color:var(--ink); background:#f5f7fb; font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif; }
        a { color:inherit; text-decoration:none; }
        button { font:inherit; }
        .sidebar { position:sticky; top:0; z-index:10; width:236px; min-width:236px; flex:0 0 236px; height:100vh; display:flex; flex-direction:column; overflow:hidden; border-right:1px solid var(--line); background:#fff; }
        .sidebar-logo { min-height:72px; display:flex; align-items:center; padding:20px 20px 16px; border-bottom:1px solid #f2f4f7; }
        .active8-logo { display:inline-flex; align-items:center; width:124px; }
        .active8-logo img { display:block; width:100%; height:auto; }
        .sidebar-icons { display:flex; align-items:center; gap:10px; margin-left:auto; color:#98a2b3; }
        .sidebar-icons svg { width:15px; height:15px; flex-shrink:0; }
        .sidebar-nav { flex:1; overflow-y:auto; padding:12px; }
        .nav-item { min-height:38px; display:flex; align-items:center; gap:10px; margin-bottom:2px; padding:9px 12px; border-radius:8px; color:#344054; font-size:14px; font-weight:500; }
        .nav-item:hover { background:#f2f4f7; }
        .nav-item.active { background:var(--blue); color:#fff; }
        .nav-item svg,.sidebar-footer svg { width:15px; height:15px; flex-shrink:0; }
        .sidebar-footer { display:grid; gap:6px; padding:12px; border-top:1px solid #f2f4f7; }
        .footer-action { min-height:37px; display:flex; align-items:center; gap:8px; width:100%; padding:9px 12px; border:0; border-radius:8px; background:transparent; color:#344054; cursor:pointer; font-size:13px; font-weight:700; }
        .footer-action:hover { background:#f2f4f7; }
        .user-row { display:flex; align-items:center; gap:9px; padding:8px 10px; border-radius:8px; }
        .user-avatar { width:28px; height:28px; display:grid; place-items:center; flex-shrink:0; border-radius:50%; background:var(--blue); color:#fff; font-size:10px; font-weight:900; }
        .user-name { min-width:0; overflow:hidden; color:var(--ink); font-size:12px; font-weight:700; text-overflow:ellipsis; white-space:nowrap; }
        .workspace { min-width:0; max-width:calc(100vw - 236px); flex:1 1 auto; overflow:auto; background:#f5f7fb; }
        .shell { width:min(1480px,100%); margin:0 auto; padding:28px 28px 36px; }
        .topbar,.hero,.panel,.stat { border:1px solid var(--line); background:var(--white); box-shadow:var(--shadow); }
        .topbar { position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px 16px; border-radius:14px; }
        .brand,.actions,.hero-main,.meta,.tabs,.section-head,.avatar-row,.split,.stack,.socials { display:flex; align-items:center; gap:10px; }
        .brand-logo-inline { display:block; width:94px; height:auto; flex-shrink:0; }
        .brand { min-width:0; }
        .brand strong { flex-shrink:0; font-size:13px; }
        .crumb { min-width:0; overflow:hidden; color:var(--muted); font-size:12px; text-overflow:ellipsis; white-space:nowrap; }
        .btn { min-height:36px; display:inline-flex; align-items:center; justify-content:center; gap:7px; padding:0 11px; border:1px solid var(--line); border-radius:7px; background:#fff; color:#344054; cursor:pointer; font-size:12px; font-weight:800; }
        .btn.primary { border-color:var(--blue); background:var(--blue); color:#fff; }
        .btn svg,.metric-icon svg { width:15px; height:15px; }
        .hero { position:relative; overflow:hidden; margin-top:14px; border-radius:8px; }
        .cover { height:126px; background:linear-gradient(120deg,#172554,#1d4ed8 64%,#0f766e); background-position:center; background-size:cover; }
        .hero-body { display:flex; justify-content:space-between; gap:20px; padding:0 20px 18px; }
        .hero-main { min-width:0; align-items:flex-end; margin-top:-38px; }
        .logo { width:82px; height:82px; display:grid; place-items:center; overflow:hidden; flex-shrink:0; border:4px solid #fff; border-radius:16px; background:#dbeafe; color:#1d4ed8; box-shadow:0 6px 16px rgba(16,24,40,.12); font-size:22px; font-weight:900; }
        .logo img { width:100%; height:100%; object-fit:cover; }
        .hero-copy { min-width:0; padding-top:42px; }
        h1 { margin:0; overflow:hidden; font-size:24px; letter-spacing:0; text-overflow:ellipsis; white-space:nowrap; }
        .meta { flex-wrap:wrap; margin-top:7px; color:var(--muted); font-size:12px; }
        .meta span + span::before { content:""; width:3px; height:3px; display:inline-block; margin:0 9px 3px 0; border-radius:50%; background:#98a2b3; }
        .hero-actions { display:flex; flex-wrap:wrap; justify-content:flex-end; gap:8px; align-self:flex-end; }
        .pill { display:inline-flex; align-items:center; gap:6px; padding:4px 8px; border-radius:999px; font-size:10px; font-weight:900; text-transform:uppercase; }
        .pill::before { content:""; width:6px; height:6px; border-radius:50%; background:currentColor; }
        .pill.active,.pill.approved,.pill.genuine,.pill.printed { color:var(--green); background:var(--green-soft); }
        .pill.pending,.pill.draft { color:var(--amber); background:var(--amber-soft); }
        .pill.suspended,.pill.recalled,.pill.invalid,.pill.suspicious,.pill.revoked,.pill.expired { color:var(--red); background:var(--red-soft); }
        .stats { display:grid; grid-template-columns:repeat(8,minmax(0,1fr)); gap:10px; margin-top:14px; }
        .stat { min-width:0; padding:13px; border-radius:8px; }
        .metric-icon { width:28px; height:28px; display:grid; place-items:center; border-radius:7px; background:var(--blue-soft); color:var(--blue); }
        .stat-label { margin-top:14px; color:var(--muted); font-size:10px; font-weight:800; text-transform:uppercase; }
        .stat-value { margin-top:4px; font-size:21px; font-weight:900; }
        .tabs { overflow:auto; margin-top:14px; border-bottom:1px solid var(--line); }
        .tab { padding:12px 3px 10px; border-bottom:2px solid transparent; color:var(--muted); font-size:12px; font-weight:800; white-space:nowrap; }
        .tab.active { border-color:var(--blue); color:var(--blue); }
        .content { margin-top:14px; }
        .grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(300px,.55fr); gap:14px; align-items:start; }
        .panel { overflow:hidden; border-radius:8px; }
        .panel + .panel { margin-top:14px; }
        .panel-head,.section-head { justify-content:space-between; gap:12px; padding:15px 16px; border-bottom:1px solid var(--line); }
        .panel-title { font-size:14px; font-weight:900; }
        .panel-sub { margin-top:4px; color:var(--muted); font-size:11px; line-height:1.45; }
        .panel-body { padding:15px 16px; }
        .table-wrap { max-width:100%; overflow:auto; }
        table { width:100%; border-collapse:collapse; font-size:12px; }
        th { padding:10px 12px; color:var(--muted); background:var(--soft); font-size:10px; text-align:left; text-transform:uppercase; white-space:nowrap; }
        td { padding:11px 12px; border-top:1px solid var(--line); color:#344054; vertical-align:middle; }
        td strong { display:block; color:var(--ink); }
        td small { display:block; margin-top:3px; color:var(--muted); }
        .empty { padding:28px 16px; color:var(--muted); font-size:12px; text-align:center; }
        .table-pagination { display:flex; align-items:center; justify-content:space-between; gap:14px; margin:14px 16px 16px; padding:12px; border:1px solid var(--line); border-radius:12px; background:linear-gradient(180deg,#fff,#f8fbff); color:var(--muted); font-size:12px; font-weight:850; box-shadow:0 10px 26px rgba(16,24,40,.045); }
        .table-pagination > span:first-child { display:inline-flex; align-items:center; gap:8px; min-height:30px; }
        .table-pagination > span:first-child::before { content:""; width:8px; height:8px; border-radius:999px; background:var(--blue); box-shadow:0 0 0 4px var(--blue-soft); }
        .pager-actions { display:inline-flex; align-items:center; gap:7px; padding:3px; border:1px solid #eef2f6; border-radius:11px; background:#fff; }
        .pager-actions > span { min-width:54px; height:30px; display:inline-flex; align-items:center; justify-content:center; padding:0 10px; border-radius:8px; background:var(--soft); color:#344054; font-size:12px; font-weight:900; white-space:nowrap; }
        .pager-btn { width:30px; height:30px; display:inline-grid; place-items:center; border:1px solid transparent; border-radius:8px; background:#fff; color:#344054; cursor:pointer; font-size:15px; font-weight:900; line-height:1; transition:background .16s ease,border-color .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease; }
        .pager-btn:hover:not(:disabled) { border-color:rgba(37,99,235,.28); background:var(--blue-soft); color:var(--blue); box-shadow:0 6px 16px rgba(37,99,235,.1); transform:translateY(-1px); }
        .pager-btn:focus-visible { outline:3px solid rgba(37,99,235,.18); outline-offset:2px; }
        .pager-btn:disabled { opacity:.45; cursor:not-allowed; }
        .progress { overflow:hidden; height:7px; border-radius:999px; background:#eef2f6; }
        .progress span { display:block; height:100%; border-radius:inherit; background:var(--blue); }
        .summary-list { display:grid; gap:0; }
        .summary-row { display:flex; justify-content:space-between; gap:12px; padding:11px 0; border-bottom:1px solid var(--line); color:var(--muted); font-size:12px; }
        .summary-row:last-child { border-bottom:0; }
        .summary-row strong { color:var(--ink); text-align:right; }
        .activity { display:grid; gap:10px; }
        .activity-item { display:grid; grid-template-columns:9px minmax(0,1fr) auto; gap:10px; align-items:start; padding-bottom:10px; border-bottom:1px solid var(--line); }
        .activity-item:last-child { padding-bottom:0; border-bottom:0; }
        .dot { width:8px; height:8px; margin-top:4px; border-radius:50%; background:var(--blue); }
        .activity-copy { color:#344054; font-size:12px; line-height:1.4; }
        .activity-copy strong { color:var(--ink); }
        .activity-time { color:var(--muted); font-size:11px; white-space:nowrap; }
        .profile-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
        .profile-card { min-width:0; padding:13px; border:1px solid var(--line); border-radius:7px; background:var(--soft); }
        .profile-card.wide { grid-column:1/-1; }
        .label { color:var(--muted); font-size:10px; font-weight:900; text-transform:uppercase; }
        .value { margin-top:7px; color:var(--ink); font-size:13px; font-weight:700; line-height:1.5; overflow-wrap:anywhere; }
        .socials { flex-wrap:wrap; margin-top:10px; }
        .social { padding:6px 8px; border:1px solid var(--line); border-radius:6px; background:#fff; color:var(--blue); font-size:11px; font-weight:800; }
        .analytics-grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .analytics-card { padding:14px; border:1px solid var(--line); border-radius:7px; background:var(--soft); }
        .analytics-card strong { display:block; margin-top:8px; font-size:22px; }
        .analytics-card span { color:var(--muted); font-size:11px; font-weight:800; text-transform:uppercase; }
        .workspace-note { display:flex; gap:10px; align-items:flex-start; margin-top:14px; padding:12px 14px; border:1px solid #bfdbfe; border-radius:10px; background:#eff6ff; color:#1e3a8a; font-size:12px; line-height:1.45; }
        .workspace-note strong { display:block; color:#172554; }
        .widget-list { display:grid; gap:0; }
        .widget-list-item { padding:12px 0; border-bottom:1px solid var(--line); }
        .widget-list-item:last-child { border-bottom:0; }
        .widget-list-title { color:var(--ink); font-size:12px; font-weight:800; line-height:1.4; }
        .widget-list-sub { margin-top:4px; color:var(--muted); font-size:11px; line-height:1.45; }
        .mini-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; }
        .mini-card { padding:12px; border:1px solid var(--line); border-radius:8px; background:var(--soft); }
        .mini-card span { display:block; color:var(--muted); font-size:10px; font-weight:900; text-transform:uppercase; }
        .mini-card strong { display:block; margin-top:6px; color:var(--ink); font-size:16px; }
        @media (max-width:1180px) { .stats { grid-template-columns:repeat(4,minmax(0,1fr)); } }
        @media (max-width:850px) { body{height:auto;min-height:100vh;display:block;overflow:auto}.sidebar{position:relative;width:100%;height:auto;min-width:0;flex:0 0 auto}.sidebar-nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr))}.sidebar-footer{display:none}.workspace{max-width:none;overflow:visible}.shell{width:min(100% - 20px,680px);padding:14px 0 24px}.topbar,.hero-body{align-items:flex-start;flex-direction:column}.hero-actions{justify-content:flex-start}.grid{grid-template-columns:1fr}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.profile-grid,.analytics-grid{grid-template-columns:1fr}.profile-card.wide{grid-column:auto}.actions .btn span{display:none}.cover{height:96px}.hero-body{padding:0 14px 14px}.hero-main{margin-top:-28px}.logo{width:68px;height:68px}.hero-copy{padding-top:31px}h1{font-size:20px}.meta{align-items:flex-start;flex-direction:column;gap:4px}.meta span + span::before{display:none}.table-pagination{align-items:flex-start;flex-direction:column}.pager-actions{max-width:100%;overflow-x:auto} }
    </style>
</head>
<body>
    <svg width="0" height="0" style="position:absolute">
        <symbol id="i-bag" viewBox="0 0 24 24"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z M3 6h18 M16 10a4 4 0 0 1-8 0" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-form" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z M14 2v6h6" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-qr" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="14" y="3" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><rect x="3" y="14" width="7" height="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M14 14h3v3h-3zM20 14v7h-7" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-eye" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12Z" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path d="m5 12 4 4L19 6" fill="none" stroke="currentColor" stroke-width="2.4"/></symbol>
        <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 9v4m0 4h.01M10.3 3.8 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-activity" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-print" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-edit" viewBox="0 0 24 24"><path d="M12 20h9 M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-8 0v2" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-link" viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71 M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-settings" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
        <symbol id="i-user" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4" fill="none" stroke="currentColor" stroke-width="2"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7" fill="none" stroke="currentColor" stroke-width="2"/></symbol>
    </svg>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <span class="active8-logo"><img src="{{ asset('images/active8-logo.png') }}" alt="Active8"></span>
            <div class="sidebar-icons"><svg><use href="#i-eye"></use></svg><svg><use href="#i-qr"></use></svg></div>
        </div>
        <nav class="sidebar-nav">
            @foreach($navItems as $item)
                <a class="nav-item {{ $item['key'] === 'companies' ? 'active' : '' }}" href="{{ route('active8.page', $item['key']) }}">
                    <svg><use href="#i-{{ $item['icon'] }}"></use></svg>{{ $item['label'] }}
                </a>
            @endforeach
        </nav>
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="footer-action" type="submit"><svg><use href="#i-link"></use></svg>Sign out</button></form>
            <a class="footer-action" href="{{ route('active8.page', 'accounts') }}"><svg><use href="#i-users"></use></svg>Accounts</a>
            <a class="user-row" href="{{ route('active8.page', 'account') }}"><span class="user-avatar">{{ $userInitials }}</span><span class="user-name">{{ $authUser?->name ?? 'System Admin' }}</span></a>
        </div>
    </aside>
    <div class="workspace">
    <main class="shell">
        <header class="topbar">
            <div class="brand"><img class="brand-logo-inline" src="{{ asset('images/active8-logo.png') }}" alt="Active8"><span class="crumb">/ Companies / {{ $company->name }}</span></div>
            <div class="actions">
                <a class="btn" href="{{ route('active8.page', 'companies') }}"><svg><use href="#i-arrow"></use></svg><span>Companies</span></a>
                <a class="btn primary" href="{{ route('active8.page', ['page' => 'company-profile', 'company' => $company->id]) }}"><svg><use href="#i-edit"></use></svg><span>Edit Profile</span></a>
            </div>
        </header>

        <section class="hero">
            <div class="cover" @if($coverUrl) style="background-image:linear-gradient(120deg,rgba(15,23,42,.68),rgba(29,78,216,.45)),url('{{ $coverUrl }}')" @endif></div>
            <div class="hero-body">
                <div class="hero-main">
                    <div class="logo">@if($logoUrl)<img src="{{ $logoUrl }}" alt="{{ $company->name }} logo">@else{{ $initials }}@endif</div>
                    <div class="hero-copy">
                        <h1>{{ $company->name }}</h1>
                        <div class="meta"><span class="pill {{ $company->status }}">{{ $company->status }}</span><span>{{ $company->industry ?? 'Industry not recorded' }}</span><span>{{ $location }}</span><span>{{ $company->registration_number ?? 'Registration not recorded' }}</span></div>
                    </div>
                </div>
                <div class="hero-actions">
                    <a class="btn" href="{{ route('active8.page', ['page' => 'products', 'company' => $company->id]) }}">Products</a>
                    <a class="btn" href="{{ route('active8.page', ['page' => 'batches', 'company' => $company->id]) }}">Batches</a>
                    <a class="btn primary" href="{{ route('active8.page', ['page' => 'label-orders', 'company' => $company->id]) }}">Label Orders</a>
                </div>
            </div>
        </section>

        <section class="stats">
            @foreach($stats as $stat)
                <div class="stat"><div class="metric-icon"><svg><use href="#i-{{ $stat['icon'] }}"></use></svg></div><div class="stat-label">{{ $stat['label'] }}</div><div class="stat-value">{{ is_numeric($stat['value']) ? number_format($stat['value']) : $stat['value'] }}</div></div>
            @endforeach
        </section>

        <nav class="tabs" aria-label="Company workspace sections">
            @foreach($tabs as $key => $label)
                <a class="tab {{ $tab === $key ? 'active' : '' }}" href="{{ route('companies.show', ['company' => $company, 'tab' => $key]) }}">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="workspace-note">
            <svg width="16" height="16"><use href="#i-check"></use></svg>
            <div><strong>Company workspace</strong>This view now mirrors the main admin console: quick actions first, lighter tables, clearer empty states, and lifecycle signals for QR verification.</div>
        </div>

        <section class="content">
            @if($tab === 'overview')
                <div class="grid">
                    <div>
                        <div class="panel">
                            <div class="panel-head"><div><div class="panel-title">Recent Batches</div><div class="panel-sub">Latest production records and QR readiness.</div></div><a class="btn" href="{{ route('companies.show', ['company' => $company, 'tab' => 'batches']) }}">View All</a></div>
                            <div class="table-wrap"><table data-auto-paginate data-auto-paginate-label="batches" data-auto-paginate-per-page="5"><thead><tr><th>Batch</th><th>Product</th><th>QA</th><th>QR</th><th>Quantity</th></tr></thead><tbody>
                            @forelse($recentBatches as $batch)<tr><td><strong>{{ $batch->batch_number }}</strong><small>{{ $batch->created_at->format('M j, Y') }}</small></td><td>{{ $batch->product?->name }}</td><td><span class="pill {{ $batch->qa_status }}">{{ $batch->qa_status }}</span></td><td>{{ $batch->qrCode ? 'Ready' : 'Missing' }}</td><td>{{ number_format($batch->quantity) }}</td></tr>@empty<tr><td colspan="5" class="empty">No batches have been registered yet.</td></tr>@endforelse
                            </tbody></table></div>
                        </div>
                        <div class="panel">
                            <div class="panel-head"><div><div class="panel-title">Recent Verification Activity</div><div class="panel-sub">Latest scan outcomes across this company.</div></div><a class="btn" href="{{ route('companies.show', ['company' => $company, 'tab' => 'activity']) }}">View Activity</a></div>
                            <div class="panel-body activity">
                                @forelse($recentScans->take(5) as $scan)<div class="activity-item"><span class="dot"></span><div class="activity-copy"><strong>{{ ucfirst($scan->status_result) }} scan</strong> for {{ $scan->qrCode?->batch?->product?->name ?? 'unknown product' }} · {{ $scan->qrCode?->batch?->batch_number ?? $scan->token_scanned }}</div><span class="activity-time">{{ $scan->scanned_at->diffForHumans() }}</span></div>@empty<div class="empty">Scan activity will appear here after customers verify products.</div>@endforelse
                            </div>
                        </div>
                    </div>
                    <aside>
                        <div class="panel">
                            <div class="panel-head"><div><div class="panel-title">Profile Health</div><div class="panel-sub">Public trust-page readiness.</div></div></div>
                            <div class="panel-body">
                                @php $profileScore = collect([$profile?->logo_path,$profile?->cover_image_path,$profile?->verification_headline,$profile?->support_email ?: $profile?->support_phone,$profile?->company_description])->filter()->count() * 20; @endphp
                                <div class="progress"><span style="width:{{ $profileScore }}%"></span></div>
                                <div class="summary-row"><span>Completion</span><strong>{{ $profileScore }}%</strong></div>
                                <div class="summary-row"><span>Logo</span><strong>{{ $profile?->logo_path ? 'Uploaded' : 'Needed' }}</strong></div>
                                <div class="summary-row"><span>Cover image</span><strong>{{ $profile?->cover_image_path ? 'Uploaded' : 'Needed' }}</strong></div>
                                <div class="summary-row"><span>Support route</span><strong>{{ $profile?->support_email || $profile?->support_phone ? 'Ready' : 'Needed' }}</strong></div>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-head"><div><div class="panel-title">Company Contact</div><div class="panel-sub">Primary onboarding and operations details.</div></div></div>
                            <div class="panel-body summary-list">
                                <div class="summary-row"><span>Contact</span><strong>{{ $company->contact_person ?? 'Not assigned' }}</strong></div>
                                <div class="summary-row"><span>Email</span><strong>{{ $company->email ?? 'Not recorded' }}</strong></div>
                                <div class="summary-row"><span>Phone</span><strong>{{ $company->phone ?? 'Not recorded' }}</strong></div>
                                <div class="summary-row"><span>Website</span><strong>{{ $company->website ?? 'Not recorded' }}</strong></div>
                            </div>
                        </div>
                        <div class="panel">
                            <div class="panel-head"><div><div class="panel-title">Security Posture</div><div class="panel-sub">Verification risk snapshot for this company.</div></div></div>
                            <div class="panel-body mini-grid">
                                <div class="mini-card"><span>Flagged scans</span><strong>{{ number_format($flaggedScans) }}</strong></div>
                                <div class="mini-card"><span>Genuine rate</span><strong>{{ $genuineRate }}%</strong></div>
                                <div class="mini-card"><span>QR coverage</span><strong>{{ $batches->count() ? round(($qrCodeCount / max($batches->count(), 1)) * 100) : 0 }}%</strong></div>
                                <div class="mini-card"><span>Audit events</span><strong>{{ number_format(($activityLogs ?? collect())->count()) }}</strong></div>
                            </div>
                        </div>
                    </aside>
                </div>
            @elseif($tab === 'products')
                <div class="panel"><div class="panel-head"><div><div class="panel-title">Products</div><div class="panel-sub">Registered products owned by {{ $company->name }}.</div></div><a class="btn primary" href="{{ route('active8.page', ['page' => 'products', 'company' => $company->id]) }}">Manage Products</a></div><div class="table-wrap"><table data-auto-paginate data-auto-paginate-label="products" data-auto-paginate-per-page="10"><thead><tr><th>Product</th><th>SKU</th><th>Category</th><th>Batches</th><th>Status</th></tr></thead><tbody>
                @forelse($products as $product)<tr><td><strong>{{ $product->name }}</strong><small>{{ Str::limit($product->description, 54) ?: 'No description' }}</small></td><td>{{ $product->sku ?? 'Not listed' }}</td><td>{{ $product->productCategory?->name ?? $product->category ?? 'Uncategorized' }}</td><td>{{ number_format($product->batches_count) }}</td><td><span class="pill {{ $product->status }}">{{ $product->status }}</span></td></tr>@empty<tr><td colspan="5" class="empty">No products have been registered yet.</td></tr>@endforelse
                </tbody></table></div></div>
            @elseif($tab === 'batches')
                <div class="panel"><div class="panel-head"><div><div class="panel-title">Batches & QR Codes</div><div class="panel-sub">Production batches, QA status, expiry dates, and verification QR readiness.</div></div><a class="btn primary" href="{{ route('active8.page', ['page' => 'batches', 'company' => $company->id]) }}">Manage Batches</a></div><div class="table-wrap"><table data-auto-paginate data-auto-paginate-label="batches" data-auto-paginate-per-page="10"><thead><tr><th>Batch</th><th>Product</th><th>Status</th><th>Expiry</th><th>QR Code</th><th>Labels</th></tr></thead><tbody>
                @forelse($batches as $batch)<tr><td><strong>{{ $batch->batch_number }}</strong><small>{{ $batch->production_location ?? 'No production location' }}</small></td><td>{{ $batch->product?->name }}</td><td><span class="pill {{ $batch->status }}">{{ $batch->status }}</span></td><td>{{ $batch->expiry_date?->format('M j, Y') ?? 'Not listed' }}</td><td>@if($batch->qrCode)<span class="pill {{ $batch->qrCode->isExpired() ? 'expired' : $batch->qrCode->status }}">{{ $batch->qrCode->isExpired() ? 'expired' : $batch->qrCode->status }}</span><small>{{ $batch->qrCode->expires_at?->format('M j, Y') ?? 'No QR expiry' }}</small>@else Not generated @endif</td><td>{{ number_format($batch->labelOrders->sum('print_quantity')) }}</td></tr>@empty<tr><td colspan="6" class="empty">No batches yet. Create a batch from the main Batches console to generate its QR lifecycle.</td></tr>@endforelse
                </tbody></table></div></div>
            @elseif($tab === 'profile')
                <div class="grid"><div class="panel"><div class="panel-head"><div><div class="panel-title">Verification Profile</div><div class="panel-sub">Customer-facing company identity and product-verification support details.</div></div><a class="btn primary" href="{{ route('active8.page', ['page' => 'company-profile', 'company' => $company->id]) }}">Open Editor</a></div><div class="panel-body profile-grid">
                    <div class="profile-card wide"><div class="label">Verification Headline</div><div class="value">{{ $profile?->verification_headline ?? 'Not configured' }}</div></div>
                    <div class="profile-card"><div class="label">Support Email</div><div class="value">{{ $profile?->support_email ?? $company->email ?? 'Not configured' }}</div></div>
                    <div class="profile-card"><div class="label">Support Phone</div><div class="value">{{ $profile?->support_phone ?? $company->phone ?? 'Not configured' }}</div></div>
                    <div class="profile-card wide"><div class="label">Company Description</div><div class="value">{{ $profile?->company_description ?? 'Not configured' }}</div></div>
                    <div class="profile-card"><div class="label">Manufacturer</div><div class="value">{{ $profile?->manufacturer_name ?? 'Not configured' }}</div></div>
                    <div class="profile-card"><div class="label">Certification</div><div class="value">{{ $profile?->certification_number ?? 'Not configured' }}</div></div>
                    <div class="profile-card wide"><div class="label">Social Links</div><div class="socials">@forelse(($profile?->social_links ?? []) as $social)<a class="social" href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer">{{ $social['label'] }}</a>@empty<span class="value">No social links configured</span>@endforelse</div></div>
                </div></div><aside><div class="panel"><div class="panel-head"><div><div class="panel-title">Public Profile</div><div class="panel-sub">Brand assets used on scan results.</div></div></div><div class="panel-body summary-list"><div class="summary-row"><span>Logo</span><strong>{{ $profile?->logo_path ? 'Uploaded' : 'Needed' }}</strong></div><div class="summary-row"><span>Cover</span><strong>{{ $profile?->cover_image_path ? 'Uploaded' : 'Needed' }}</strong></div><div class="summary-row"><span>Layout</span><strong>{{ ucfirst($profile?->layout_style ?? 'default') }}</strong></div><div class="summary-row"><span>Platform branding</span><strong>{{ $profile?->show_platform_branding ? 'Visible' : 'Hidden' }}</strong></div></div></div></aside></div>
            @elseif($tab === 'analytics')
                <div class="panel"><div class="panel-head"><div><div class="panel-title">Verification Analytics</div><div class="panel-sub">Company-level scan health and customer verification outcomes.</div></div><a class="btn primary" href="{{ route('active8.page', ['page' => 'scan-analytics', 'company' => $company->id]) }}">Advanced Analytics</a></div><div class="panel-body"><div class="analytics-grid"><div class="analytics-card"><span>Total scans</span><strong>{{ number_format($scanLogs->count()) }}</strong></div><div class="analytics-card"><span>Genuine rate</span><strong>{{ $genuineRate }}%</strong></div><div class="analytics-card"><span>Flagged scans</span><strong>{{ number_format($flaggedScans) }}</strong></div></div></div><div class="table-wrap"><table data-auto-paginate data-auto-paginate-label="scans" data-auto-paginate-per-page="10"><thead><tr><th>Outcome</th><th>Product</th><th>Batch</th><th>Location</th><th>Device</th><th>Scanned</th></tr></thead><tbody>
                @forelse($scanLogs->take(30) as $scan)<tr><td><span class="pill {{ $scan->status_result }}">{{ $scan->status_result }}</span></td><td>{{ $scan->qrCode?->batch?->product?->name ?? 'Unknown' }}</td><td>{{ $scan->qrCode?->batch?->batch_number ?? 'Unknown' }}</td><td>{{ collect([$scan->city,$scan->country])->filter()->implode(', ') ?: 'Unknown' }}</td><td>{{ ucfirst($scan->device_type ?? 'Unknown') }}</td><td>{{ $scan->scanned_at->format('M j, Y H:i') }}</td></tr>@empty<tr><td colspan="6" class="empty">No verification scans recorded yet.</td></tr>@endforelse
                </tbody></table></div></div>
            @else
                <div class="grid"><div><div class="panel"><div class="panel-head"><div><div class="panel-title">Scan Activity</div><div class="panel-sub">Recent verification outcomes across products and batches.</div></div></div><div class="panel-body activity">@forelse($recentScans as $scan)<div class="activity-item"><span class="dot"></span><div class="activity-copy"><strong>{{ ucfirst($scan->status_result) }} verification</strong> · {{ $scan->qrCode?->batch?->product?->name ?? 'Unknown product' }} · {{ $scan->qrCode?->batch?->batch_number ?? $scan->token_scanned }}</div><span class="activity-time">{{ $scan->scanned_at->diffForHumans() }}</span></div>@empty<div class="empty">No scan activity recorded yet.</div>@endforelse</div></div><div class="panel"><div class="panel-head"><div><div class="panel-title">Audit Timeline</div><div class="panel-sub">Admin changes tied to this company’s batches and QR codes.</div></div></div><div class="panel-body">@include('partials.admin-audit-list', ['logs' => $activityLogs ?? collect(), 'emptyTitle' => 'No audit events for this company', 'emptyCopy' => 'Batch and QR lifecycle actions will appear here.'])</div></div></div><aside><div class="panel"><div class="panel-head"><div><div class="panel-title">Label Order Activity</div><div class="panel-sub">Latest print operations.</div></div></div><div class="panel-body activity">@forelse($recentOrders as $order)<div class="activity-item"><span class="dot"></span><div class="activity-copy"><strong>{{ ucfirst($order->status) }}</strong> · {{ number_format($order->print_quantity) }} {{ $order->label_type }} labels</div><span class="activity-time">{{ $order->created_at->diffForHumans() }}</span></div>@empty<div class="empty">No label orders recorded yet.</div>@endforelse</div></div></aside></div>
            @endif
        </section>
    </main>
    </div>
    <script>
        document.querySelectorAll('[data-auto-paginate]').forEach((table) => {
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            const perPage = Number(table.dataset.autoPaginatePerPage || 10);
            const label = table.dataset.autoPaginateLabel || 'rows';

            if (rows.length <= perPage) return;

            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / perPage);
            const pager = document.createElement('div');
            pager.className = 'table-pagination';
            pager.innerHTML = `<span data-page-summary></span><div class="pager-actions"><button class="pager-btn" type="button" data-page-prev aria-label="Previous page">‹</button><span data-page-current>1</span><button class="pager-btn" type="button" data-page-next aria-label="Next page">›</button></div>`;
            table.closest('.table-wrap').insertAdjacentElement('afterend', pager);

            const summary = pager.querySelector('[data-page-summary]');
            const pageCurrent = pager.querySelector('[data-page-current]');
            const previous = pager.querySelector('[data-page-prev]');
            const next = pager.querySelector('[data-page-next]');
            const render = () => {
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                rows.forEach((row, index) => {
                    row.style.display = index >= start && index < end ? '' : 'none';
                });

                summary.textContent = `Showing ${start + 1}-${Math.min(end, rows.length)} of ${rows.length} ${label}`;
                pageCurrent.textContent = `${currentPage} / ${totalPages}`;
                previous.disabled = currentPage <= 1;
                next.disabled = currentPage >= totalPages;
            };

            previous.addEventListener('click', () => {
                currentPage = Math.max(1, currentPage - 1);
                render();
            });
            next.addEventListener('click', () => {
                currentPage = Math.min(totalPages, currentPage + 1);
                render();
            });
            render();
        });
    </script>
</body>
</html>
