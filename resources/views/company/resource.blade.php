@extends('company.layout')

@section('title', $title.' | '.$company->name.' | Active8')

@section('content')
    @php
        $titleIcon = match (true) {
            Str::contains($title, 'Product') => 'bag',
            Str::contains($title, 'Batch') => 'layers',
            Str::contains($title, 'Label Order') => 'tag',
            Str::contains($title, 'USSD') => 'code',
            Str::contains($title, 'Verification') => 'list',
            Str::contains($title, 'Suspicious') => 'alert',
            Str::contains($title, 'User') => 'users',
            default => 'activity',
        };
    @endphp
    <div class="company-topbar">
        <div>
            <div class="company-title-row">
                <span class="company-title-icon" aria-hidden="true"><svg><use href="#company-i-{{ $titleIcon }}"></use></svg></span>
                <h1 class="company-title">{{ $title }}</h1>
            </div>
            <div class="company-subtitle">{{ $subtitle }}</div>
        </div>
        <a class="btn-learn" href="{{ route('company.dashboard') }}">Dashboard</a>
    </div>

    <div class="company-content">
        <section class="admin-card">
            @if ($table)
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">{{ $title }} Registry</div>
                        <div class="admin-card-sub">
                            {{ method_exists($table['rows'], 'total') ? number_format($table['rows']->total()) : $table['rows']->count() }}
                            {{ Str::lower($title) }} shown for {{ $company->name }}.
                        </div>
                    </div>
                    <div class="resource-actions">
                        @foreach (($table['actions'] ?? []) as $action)
                            <button class="btn-share" type="button" data-resource-drawer-open="{{ $action['target'] }}">{{ $action['label'] }}</button>
                        @endforeach
                        @if (! empty($table['exportRoute']) && $table['rows']->isNotEmpty())
                            <a class="btn-learn resource-export-link" href="{{ $table['exportRoute'] }}">Export CSV</a>
                        @endif
                    </div>
                </div>
                <form class="table-toolbar" method="GET" action="{{ url()->current() }}">
                    <label class="table-search" aria-label="Search {{ Str::lower($title) }}">
                        <input type="search" name="q" value="{{ $table['search']['value'] ?? '' }}" placeholder="{{ $table['search']['placeholder'] ?? 'Search '.Str::lower($title) }}">
                    </label>
                    <button class="btn-learn table-mobile-filter-toggle" type="button" aria-expanded="false" data-company-filter-toggle>
                        Filters
                    </button>
                    <div class="table-filter-row" data-company-filter-panel>
                        @foreach (($table['filters'] ?? []) as $filter)
                            <label class="filter-control">
                                <span>{{ $filter['label'] }}</span>
                                <select name="{{ $filter['name'] }}" aria-label="{{ $filter['label'] }}">
                                    @foreach ($filter['options'] as $option)
                                        <option value="{{ $option['value'] }}" @selected((string) $filter['value'] === (string) $option['value'])>{{ $option['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                        @endforeach
                        <label class="filter-control">
                            <span>Rows</span>
                            <select name="per_page" aria-label="Rows per page">
                                @foreach ([15, 25, 50, 100] as $perPageOption)
                                    <option value="{{ $perPageOption }}" @selected((int) ($table['perPage'] ?? 15) === $perPageOption)>{{ $perPageOption }}</option>
                                @endforeach
                            </select>
                        </label>
                        <input type="hidden" name="sort" value="{{ $table['sort']['value'] ?? 'latest' }}">
                        <input type="hidden" name="direction" value="{{ $table['sort']['direction'] ?? 'desc' }}">
                        <button class="btn-share" type="submit">Apply</button>
                        <a class="btn-learn" href="{{ url()->current() }}">Reset</a>
                        @if (! empty($table['exportRoute']) && $table['rows']->isNotEmpty())
                            <a class="btn-learn table-mobile-export-link" href="{{ $table['exportRoute'] }}">Export CSV</a>
                        @endif
                    </div>
                </form>
                <div class="admin-table-wrap">
                    <table class="admin-table" id="company-resource-table">
                        <thead>
                            <tr>
                                @foreach ($table['headers'] as $header)
                                    @php
                                        $headerLabel = is_array($header) ? $header['label'] : $header;
                                        $headerSort = is_array($header) ? ($header['sort'] ?? null) : null;
                                        $isActiveSort = $headerSort && ($table['sort']['value'] ?? null) === $headerSort;
                                        $nextDirection = $isActiveSort && ($table['sort']['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
                                        $sortUrl = $headerSort
                                            ? url()->current().'?'.http_build_query(array_merge(request()->except('page'), ['sort' => $headerSort, 'direction' => $nextDirection]))
                                            : null;
                                    @endphp
                                    <th>
                                        @if ($sortUrl)
                                            <a class="sort-link {{ $isActiveSort ? 'active' : '' }}" href="{{ $sortUrl }}">
                                                {{ $headerLabel }}
                                                <span>{{ $isActiveSort ? (($table['sort']['direction'] ?? 'desc') === 'asc' ? '↑' : '↓') : '↕' }}</span>
                                            </a>
                                        @else
                                            {{ $headerLabel }}
                                        @endif
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($table['rows'] as $row)
                                    <tr
                                        class="row-clickable"
                                        data-search="{{ Str::lower(collect($row['cells'])->implode(' ')) }}"
                                        data-company-detail-row
                                        data-detail-title="{{ $row['detailTitle'] ?? $row['cells'][0] ?? $title }}"
                                        data-detail-subtitle="{{ $row['detailSubtitle'] ?? Str::singular($title).' Details' }}"
                                        data-detail-href="{{ $row['href'] }}"
                                        data-detail-payload='@json($row['details'] ?? [])'
                                        tabindex="0"
                                        role="button"
                                        aria-label="Open {{ $row['detailTitle'] ?? $row['cells'][0] ?? Str::singular($title) }} details"
                                    >
                                        @foreach ($row['cells'] as $cell)
                                            <td>{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($table['headers']) }}">
                                        <div class="company-empty-state">
                                            <div class="company-empty-icon">{{ Str::substr($title, 0, 1) }}</div>
                                            <div>
                                                <div class="company-empty-title">No {{ Str::lower($title) }} found</div>
                                                <div class="company-empty-copy">{{ $table['empty'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (method_exists($table['rows'], 'hasPages') && $table['rows']->hasPages())
                    @php
                        $currentPage = $table['rows']->currentPage();
                        $lastPage = $table['rows']->lastPage();
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp
                    <div class="table-pagination">
                        <span>
                            Showing {{ number_format($table['rows']->firstItem()) }}-{{ number_format($table['rows']->lastItem()) }}
                            of {{ number_format($table['rows']->total()) }}
                        </span>
                        <div class="pager-actions">
                            @if ($table['rows']->onFirstPage())
                                <button class="pager-btn" type="button" disabled aria-label="Previous page">‹</button>
                            @else
                                <a class="pager-btn" href="{{ $table['rows']->previousPageUrl() }}" aria-label="Previous page">‹</a>
                            @endif

                            @if ($startPage > 1)
                                <a class="pager-number" href="{{ $table['rows']->url(1) }}">1</a>
                                @if ($startPage > 2)
                                    <span class="pager-ellipsis">...</span>
                                @endif
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                @if ($page === $currentPage)
                                    <span class="pager-number active" aria-current="page">{{ $page }}</span>
                                @else
                                    <a class="pager-number" href="{{ $table['rows']->url($page) }}">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($endPage < $lastPage)
                                @if ($endPage < $lastPage - 1)
                                    <span class="pager-ellipsis">...</span>
                                @endif
                                <a class="pager-number" href="{{ $table['rows']->url($lastPage) }}">{{ $lastPage }}</a>
                            @endif

                            @if ($table['rows']->hasMorePages())
                                <a class="pager-btn" href="{{ $table['rows']->nextPageUrl() }}" aria-label="Next page">›</a>
                            @else
                                <button class="pager-btn" type="button" disabled aria-label="Next page">›</button>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">{{ $title }}</div>
                        <div class="admin-card-sub">{{ $subtitle }}</div>
                    </div>
                    <a class="btn-learn" href="{{ $backUrl ?? route('company.dashboard') }}">Back</a>
                </div>
                <div class="details">
                    @foreach ($details as $label => $value)
                        <div class="detail-row">
                            <div class="detail-label">{{ $label }}</div>
                            <div class="detail-value">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        @if (($table['userCreate']['canCreate'] ?? false) && ! empty($table['userCreate']['storeRoute']))
            <div class="resource-drawer {{ $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('status') || $errors->has('password') ? 'active' : '' }}" data-resource-drawer="company-user-drawer" aria-hidden="{{ $errors->has('name') || $errors->has('email') || $errors->has('role') || $errors->has('status') || $errors->has('password') ? 'false' : 'true' }}">
                <button class="resource-drawer-backdrop" type="button" data-resource-drawer-close aria-label="Close add user drawer"></button>
                <aside class="resource-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="company-user-drawer-title">
                    <div class="resource-drawer-head">
                        <div>
                            <div class="resource-drawer-kicker">Company users</div>
                            <h2 id="company-user-drawer-title">Add user</h2>
                            <p>Create a dashboard user for {{ $company->name }}.</p>
                        </div>
                        <button class="resource-drawer-close" type="button" data-resource-drawer-close aria-label="Close">×</button>
                    </div>

                    <form class="resource-drawer-form" method="POST" action="{{ $table['userCreate']['storeRoute'] }}">
                        @csrf
                        <div class="resource-drawer-body">
                            <label class="resource-field">
                                <span>Full name</span>
                                <input name="name" value="{{ old('name') }}" placeholder="Ama Mensah" required>
                                <em>Name shown in activity logs and account screens.</em>
                                @error('name') <small>{{ $message }}</small> @enderror
                            </label>

                            <label class="resource-field">
                                <span>Email address</span>
                                <input type="email" name="email" value="{{ old('email') }}" placeholder="ama@example.com" autocomplete="email" required>
                                <em>The user signs in with this email.</em>
                                @error('email') <small>{{ $message }}</small> @enderror
                            </label>

                            <div class="resource-field-grid">
                                <label class="resource-field">
                                    <span>Role</span>
                                    <select name="role" required>
                                        <option value="company_staff" @selected(old('role', 'company_staff') === 'company_staff')>Company Staff</option>
                                        <option value="company_owner" @selected(old('role') === 'company_owner')>Company Owner</option>
                                    </select>
                                    <em>Owners can add users. Staff can view company data.</em>
                                    @error('role') <small>{{ $message }}</small> @enderror
                                </label>

                                <label class="resource-field">
                                    <span>Status</span>
                                    <select name="status" required>
                                        <option value="active" @selected(old('status', 'active') === 'active')>Active</option>
                                        <option value="invited" @selected(old('status') === 'invited')>Invited</option>
                                    </select>
                                    <em>Invited users cannot sign in until activated.</em>
                                    @error('status') <small>{{ $message }}</small> @enderror
                                </label>
                            </div>

                            <label class="resource-field">
                                <span>Temporary password</span>
                                <div class="password-toggle-field">
                                    <input type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" required data-password-toggle-input>
                                    <button type="button" data-password-toggle aria-label="Show temporary password" aria-pressed="false">Show</button>
                                </div>
                                <em>Share this securely and ask the user to change it after login.</em>
                                @error('password') <small>{{ $message }}</small> @enderror
                            </label>
                        </div>

                        <div class="resource-drawer-actions">
                            <button class="btn-learn" type="button" data-resource-drawer-close>Cancel</button>
                            <button class="btn-share" type="submit">Add user</button>
                        </div>
                    </form>
                </aside>
            </div>
        @endif

        @if ($table)
            <div class="company-detail-drawer" data-company-detail-drawer aria-hidden="true">
                <button class="company-detail-drawer-backdrop" type="button" data-company-detail-close aria-label="Close details"></button>
                <aside class="company-detail-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="company-detail-drawer-title">
                    <div class="company-detail-drawer-head">
                        <div>
                            <div class="company-detail-kicker" data-company-detail-subtitle>Details</div>
                            <h2 id="company-detail-drawer-title" data-company-detail-title>Details</h2>
                        </div>
                        <button class="company-detail-drawer-close" type="button" data-company-detail-close aria-label="Close">×</button>
                    </div>
                    <div class="company-detail-drawer-body">
                        <div class="details" data-company-detail-list></div>
                    </div>
                    <div class="company-detail-drawer-actions">
                        <a class="btn-learn" href="#" data-company-detail-link>Open full page</a>
                        <button class="btn-share" type="button" data-company-detail-close>Done</button>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection

@push('styles')
    <style>
        .resource-actions { display:flex; align-items:center; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
        .resource-actions .btn-learn, .resource-actions .btn-share { min-height:38px; }
        .table-mobile-filter-toggle, .table-mobile-export-link { display:none; }
        .row-clickable:focus-visible { outline:3px solid rgba(37,99,235,.18); outline-offset:-3px; }
        .resource-drawer { position:fixed; inset:0; z-index:90; display:none; justify-content:flex-end; }
        .resource-drawer.active { display:flex; }
        .resource-drawer-backdrop { position:absolute; inset:0; background:rgba(15,23,42,.46); backdrop-filter:blur(7px); animation:resource-fade .18s ease both; }
        .resource-drawer-panel { position:relative; width:min(460px, 100%); height:100%; display:flex; flex-direction:column; background:#fff; border-left:1px solid var(--gray-200); box-shadow:-28px 0 70px rgba(15,23,42,.24); animation:resource-slide .22s cubic-bezier(.2,.8,.2,1) both; }
        .resource-drawer-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:24px; border-bottom:1px solid var(--gray-100); background:linear-gradient(180deg, #fff, #f8fbff); }
        .resource-drawer-kicker { color:var(--blue); font-size:11px; font-weight:950; letter-spacing:.08em; text-transform:uppercase; }
        .resource-drawer-head h2 { margin-top:5px; color:var(--gray-900); font-size:22px; font-weight:900; line-height:1.15; }
        .resource-drawer-head p { margin-top:5px; color:var(--gray-500); font-size:13px; font-weight:700; line-height:1.4; }
        .resource-drawer-close { width:34px; height:34px; display:grid; place-items:center; border:1px solid transparent; border-radius:10px; background:#fff; color:var(--gray-500); font-size:24px; line-height:1; box-shadow:0 8px 18px rgba(15,23,42,.06); }
        .resource-drawer-close:hover { border-color:var(--gray-200); background:var(--gray-50); color:var(--gray-900); }
        .resource-drawer-form { min-height:0; display:flex; flex:1; flex-direction:column; }
        .resource-drawer-body { min-height:0; flex:1; display:grid; align-content:start; gap:15px; overflow-y:auto; padding:22px 24px; background:#f8fafc; }
        .resource-field-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:12px; }
        .resource-field { display:grid; gap:7px; color:var(--gray-700); font-size:13px; font-weight:850; }
        .resource-field input, .resource-field select { width:100%; height:44px; padding:10px 12px; border:1px solid #dbe3ef; border-radius:8px; background:#fff; color:var(--gray-900); outline:none; transition:border-color .16s ease,box-shadow .16s ease,background .16s ease; }
        .resource-field input:hover, .resource-field select:hover { border-color:#c8d4e4; background:#fbfdff; }
        .resource-field input:focus, .resource-field select:focus { border-color:rgba(37,99,235,.62); box-shadow:0 0 0 4px rgba(37,99,235,.1); background:#fff; }
        .resource-field em { color:var(--gray-500); font-size:12px; font-style:normal; font-weight:650; line-height:1.35; }
        .resource-field small { color:var(--red); font-size:12px; font-weight:800; }
        .password-toggle-field { position:relative; display:flex; align-items:center; }
        .password-toggle-field input { padding-right:70px; }
        .password-toggle-field button { position:absolute; right:6px; min-width:54px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:7px; background:var(--gray-100); color:var(--gray-700); font-size:12px; font-weight:900; }
        .password-toggle-field button:hover { background:var(--blue-light); color:var(--blue); }
        .resource-drawer-actions { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid var(--gray-100); background:#fff; box-shadow:0 -10px 24px rgba(15,23,42,.04); }
        .company-detail-drawer { position:fixed; inset:0; z-index:88; display:none; justify-content:flex-end; }
        .company-detail-drawer.active { display:flex; }
        .company-detail-drawer-backdrop { position:absolute; inset:0; background:rgba(15,23,42,.44); backdrop-filter:blur(7px); animation:resource-fade .18s ease both; }
        .company-detail-drawer-panel { position:relative; width:min(480px, 100%); height:100%; display:flex; flex-direction:column; overflow:hidden; border-left:1px solid var(--gray-200); background:#fff; box-shadow:-28px 0 70px rgba(15,23,42,.24); animation:resource-slide .22s cubic-bezier(.2,.8,.2,1) both; }
        .company-detail-drawer-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:24px; border-bottom:1px solid var(--gray-100); background:linear-gradient(180deg, #fff, #f8fbff); }
        .company-detail-kicker { color:var(--blue); font-size:11px; font-weight:950; letter-spacing:.08em; text-transform:uppercase; }
        .company-detail-drawer-head h2 { margin-top:5px; color:var(--gray-900); font-size:22px; font-weight:900; line-height:1.15; }
        .company-detail-drawer-close { width:34px; height:34px; display:grid; place-items:center; border:1px solid transparent; border-radius:10px; background:#fff; color:var(--gray-500); font-size:24px; line-height:1; box-shadow:0 8px 18px rgba(15,23,42,.06); }
        .company-detail-drawer-close:hover { border-color:var(--gray-200); background:var(--gray-50); color:var(--gray-900); }
        .company-detail-drawer-body { min-height:0; flex:1; overflow-y:auto; padding:22px 24px; background:#f8fafc; }
        .company-detail-drawer-body .details { padding:2px 0; border:1px solid #e5edf7; border-radius:14px; background:#fff; box-shadow:0 12px 28px rgba(15,23,42,.05); }
        .company-detail-drawer-body .detail-row { padding:14px 16px; }
        .company-detail-drawer-actions { display:flex; align-items:center; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1px solid var(--gray-100); background:#fff; box-shadow:0 -10px 24px rgba(15,23,42,.04); }
        @keyframes resource-fade { from { opacity:0; } to { opacity:1; } }
        @keyframes resource-slide { from { transform:translateX(24px); opacity:.8; } to { transform:translateX(0); opacity:1; } }
        @media (max-width: 720px) {
            .resource-actions { align-items:stretch; flex-direction:column; width:100%; }
            .resource-actions .btn-learn, .resource-actions .btn-share { width:100%; }
            .resource-export-link { display:none; }
            .table-mobile-filter-toggle { display:inline-flex; width:100%; min-height:38px; }
            .table-filter-row { display:none; width:100%; padding:12px; border:1px solid var(--gray-200); border-radius:12px; background:linear-gradient(180deg, #fff, #f8fbff); }
            .table-filter-row.is-open { display:flex; }
            .table-mobile-export-link { display:inline-flex; }
            .resource-drawer-panel, .company-detail-drawer-panel { border-left:0; }
            .resource-field-grid { grid-template-columns:1fr; }
            .resource-drawer-head, .resource-drawer-body, .resource-drawer-actions, .company-detail-drawer-head, .company-detail-drawer-body, .company-detail-drawer-actions { padding-left:16px; padding-right:16px; }
            .resource-drawer-actions, .company-detail-drawer-actions { align-items:stretch; flex-direction:column-reverse; }
            .resource-drawer-actions .btn-learn, .resource-drawer-actions .btn-share, .company-detail-drawer-actions .btn-learn, .company-detail-drawer-actions .btn-share { width:100%; min-height:42px; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const openDrawer = (name) => {
                const drawer = document.querySelector(`[data-resource-drawer="${name}"]`);
                if (!drawer) return;

                drawer.classList.add('active');
                drawer.setAttribute('aria-hidden', 'false');
                drawer.querySelector('input, select, button')?.focus();
            };
            const closeDrawer = (drawer) => {
                drawer.classList.remove('active');
                drawer.setAttribute('aria-hidden', 'true');
            };

            document.querySelectorAll('[data-resource-drawer-open]').forEach((button) => {
                button.addEventListener('click', () => openDrawer(button.dataset.resourceDrawerOpen));
            });
            document.querySelectorAll('[data-resource-drawer]').forEach((drawer) => {
                drawer.querySelectorAll('[data-resource-drawer-close]').forEach((button) => {
                    button.addEventListener('click', () => closeDrawer(drawer));
                });
            });
            document.addEventListener('keydown', (event) => {
                if (event.key !== 'Escape') return;
                document.querySelectorAll('[data-resource-drawer].active').forEach(closeDrawer);
            });

            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                const field = button.closest('.password-toggle-field');
                const input = field?.querySelector('[data-password-toggle-input]');
                if (!input) return;

                button.addEventListener('click', () => {
                    const isVisible = input.type === 'text';
                    input.type = isVisible ? 'password' : 'text';
                    button.textContent = isVisible ? 'Show' : 'Hide';
                    button.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} temporary password`);
                    button.setAttribute('aria-pressed', isVisible ? 'false' : 'true');
                    input.focus();
                });
            });

            const detailDrawer = document.querySelector('[data-company-detail-drawer]');
            if (detailDrawer) {
                const title = detailDrawer.querySelector('[data-company-detail-title]');
                const subtitle = detailDrawer.querySelector('[data-company-detail-subtitle]');
                const list = detailDrawer.querySelector('[data-company-detail-list]');
                const link = detailDrawer.querySelector('[data-company-detail-link]');
                const closeDetail = () => {
                    detailDrawer.classList.remove('active');
                    detailDrawer.setAttribute('aria-hidden', 'true');
                };
                const openDetail = (row) => {
                    let payload = {};
                    try {
                        payload = JSON.parse(row.dataset.detailPayload || '{}');
                    } catch (error) {
                        payload = {};
                    }

                    title.textContent = row.dataset.detailTitle || 'Details';
                    subtitle.textContent = row.dataset.detailSubtitle || 'Details';
                    link.href = row.dataset.detailHref || '#';
                    list.innerHTML = '';

                    Object.entries(payload).forEach(([label, value]) => {
                        const item = document.createElement('div');
                        item.className = 'detail-row';
                        const key = document.createElement('div');
                        key.className = 'detail-label';
                        key.textContent = label;
                        const val = document.createElement('div');
                        val.className = 'detail-value';
                        val.textContent = value || 'Not recorded';
                        item.append(key, val);
                        list.appendChild(item);
                    });

                    detailDrawer.classList.add('active');
                    detailDrawer.setAttribute('aria-hidden', 'false');
                    detailDrawer.querySelector('[data-company-detail-close]')?.focus();
                };

                document.querySelectorAll('[data-company-detail-row]').forEach((row) => {
                    row.addEventListener('click', () => openDetail(row));
                    row.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            openDetail(row);
                        }
                    });
                });
                detailDrawer.querySelectorAll('[data-company-detail-close]').forEach((button) => {
                    button.addEventListener('click', closeDetail);
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && detailDrawer.classList.contains('active')) {
                        closeDetail();
                    }
                });
            }
        })();
    </script>
@endpush
