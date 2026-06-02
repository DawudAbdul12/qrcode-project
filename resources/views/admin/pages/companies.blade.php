<section class="screen {{ $activePage === 'companies' ? 'active' : '' }}" id="screen-companies">
            <div class="topbar center">
                <div>
                    <div class="page-title">Companies</div>
                    <div class="page-subtitle">Manage client companies whose products will use batch-level QR verification.</div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-learn" type="button" data-company-drawer-open data-company-form="create">
                        <svg width="14" height="14"><use href="#i-plus"></use></svg>
                        Create Company
                    </button>
                    <a class="btn-learn" href="{{ route('active8.page', 'dashboard') }}">Dashboard</a>
                    <a class="btn-share" href="{{ route('active8.page', 'products') }}">Products</a>
                </div>
            </div>

            <div class="company-page-layout companies-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Companies</div>
                            <div class="company-detail-value">{{ number_format($companies->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Active Companies</div>
                            <div class="company-detail-value">{{ number_format($companies->where('status', 'active')->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Profile Completed</div>
                            <div class="company-detail-value">{{ number_format($companies->filter(fn ($item) => $item->profile !== null)->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Registered Products</div>
                            <div class="company-detail-value">{{ number_format($companies->sum('products_count')) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Company Registry</div>
                                <div class="admin-card-sub">Each company owns public verification branding, products, batches, QR codes, and label orders.</div>
                            </div>
                            <button class="btn-share" type="button" data-company-drawer-open data-company-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create Company
                            </button>
                        </div>

                        <div class="table-toolbar">
                            <label class="table-search" aria-label="Search companies">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-company-search placeholder="Search company, contact, location, industry">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-company-status-filter aria-label="Filter by company status">
                                    <option value="all">All statuses</option>
                                    <option value="active">Active</option>
                                    <option value="pending">Pending</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                                <select class="font-select" data-company-profile-filter aria-label="Filter by profile state">
                                    <option value="all">All profiles</option>
                                    <option value="complete">Profile complete</option>
                                    <option value="missing">Needs profile</option>
                                </select>
                            </div>
                        </div>

                        <form class="table-bulk-bar" method="POST" action="{{ route('companies.bulk') }}" data-company-bulk-form data-confirm-delete data-delete-name="selected companies" data-delete-match="DELETE">
                            @csrf
                            <div>
                                <div class="table-bulk-count" data-company-bulk-count>0 selected</div>
                                <div class="company-sub-name">Apply lifecycle changes to selected companies.</div>
                            </div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="activate">Activate</option>
                                    <option value="suspend">Suspend</option>
                                    <option value="delete">Archive</option>
                                </select>
                                <div data-company-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="fit"><input class="row-check" type="checkbox" data-company-select-all aria-label="Select all visible companies"></th>
                                        <th>Company</th>
                                        <th>Contact</th>
                                        <th>Location</th>
                                        <th class="fit">Products</th>
                                        <th class="fit">Batches</th>
                                        <th class="fit">Profile</th>
                                        <th class="fit">Status</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($companies as $item)
                                        @php
                                            $initials = Str::of($item->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('');
                                        @endphp
                                        <tr class="row-clickable" data-company-row data-company-id="{{ $item->id }}" data-company-search-text="{{ Str::lower($item->name.' '.($item->industry ?? '').' '.($item->contact_person ?? '').' '.($item->email ?? '').' '.($item->phone ?? '').' '.($item->country ?? '').' '.($item->region ?? '').' '.($item->city ?? '')) }}" data-company-status="{{ $item->status }}" data-company-profile="{{ $item->profile ? 'complete' : 'missing' }}">
                                            <td class="fit"><input class="row-check" type="checkbox" value="{{ $item->id }}" data-company-select aria-label="Select {{ $item->name }}"></td>
                                            <td><a class="company-name-cell" href="{{ route('companies.show', $item) }}"><div class="company-logo-sm">{{ $initials }}</div><div><div class="company-main-name">{{ $item->name }}</div><div class="company-sub-name">{{ $item->industry ?? 'Industry not set' }}</div></div></a></td>
                                            <td><div class="company-main-name">{{ $item->contact_person ?? 'Not assigned' }}</div><div class="company-sub-name">{{ $item->email ?? $item->phone ?? 'No contact' }}</div></td>
                                            <td><div class="company-main-name">{{ collect([$item->city, $item->region])->filter()->implode(', ') ?: 'No city' }}</div><div class="company-sub-name">{{ $item->country ?? 'Country not set' }}</div></td>
                                            <td class="fit">{{ number_format($item->products_count) }}</td>
                                            <td class="fit">{{ number_format($item->batches_count) }}</td>
                                            <td class="fit"><span class="status-pill {{ $item->profile ? 'active' : 'pending' }}">{{ $item->profile ? 'complete' : 'needed' }}</span></td>
                                            <td class="fit"><span class="status-pill {{ $item->status }}">{{ $item->status }}</span></td>
                                            <td class="fit">
                                                <div class="table-actions">
                                                    <a class="icon-btn" href="{{ route('companies.show', $item) }}" aria-label="Open {{ $item->name }} workspace"><svg width="15" height="15"><use href="#i-globe"></use></svg></a>
                                                    <button class="icon-btn" type="button" data-company-drawer-open data-company-form="details-{{ $item->id }}" aria-label="View {{ $item->name }} details"><svg width="15" height="15"><use href="#i-eye"></use></svg></button>
                                                    <button class="icon-btn" type="button" data-company-drawer-open data-company-form="edit-{{ $item->id }}" aria-label="Edit {{ $item->name }}"><svg width="15" height="15"><use href="#i-edit"></use></svg></button>
                                                    <form method="POST" action="{{ route('companies.destroy', $item) }}" data-confirm-delete data-delete-name="{{ $item->name }}" data-delete-match="{{ $item->name }}">@csrf @method('DELETE')<button class="icon-btn del" type="submit" aria-label="Archive {{ $item->name }}"><svg width="15" height="15"><use href="#i-trash"></use></svg></button></form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9">No companies yet.</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-company-no-results><td colspan="9"><div class="filter-empty-title">No companies match those filters.</div><div class="filter-empty-copy">Try another company, contact, status, or profile state.</div><button class="btn-learn" type="button" data-clear-company-filters>Clear filters</button></td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-company-pagination>
                            <span data-company-page-summary>Showing companies</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-company-page-prev aria-label="Previous company page">‹</button>
                                <span data-company-page-current>1</span>
                                <button class="pager-btn" type="button" data-company-page-next aria-label="Next company page">›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="company-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-company-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="company-drawer-title">
                    <div class="drawer-header"><div><div class="admin-card-title" id="company-drawer-title">Create Company</div><div class="admin-card-sub" id="company-drawer-subtitle">Add a client company before registering products and batches.</div></div><button class="drawer-close" type="button" data-company-drawer-close aria-label="Close company panel">×</button></div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="company-form-create" method="POST" action="{{ route('companies.store') }}" enctype="multipart/form-data" data-form-title="Create Company" data-form-subtitle="Add a client company before registering products and batches.">
                            @csrf
                            @include('partials.company-wizard')
                        </form>
                        @foreach ($companies as $item)
                            <form class="drawer-user-form" id="company-form-edit-{{ $item->id }}" method="POST" action="{{ route('companies.update', $item) }}" data-form-title="Edit Company" data-form-subtitle="Update {{ $item->name }} details and onboarding state.">
                                @csrf @method('PUT')
                                @include('partials.company-form', ['item' => $item, 'submitLabel' => 'Save Company'])
                            </form>
                        @endforeach
                        @foreach ($companies as $item)
                            <div class="drawer-user-form activity-panel" id="company-form-details-{{ $item->id }}" data-form-title="Company Details" data-form-subtitle="{{ $item->name }} operations summary.">
                                <div class="activity-hero"><div class="company-logo-sm" style="width:48px;height:48px;font-size:16px">{{ Str::of($item->name)->explode(' ')->filter()->map(fn ($word) => Str::substr($word, 0, 1))->take(2)->implode('') }}</div><div><div class="company-main-name">{{ $item->name }}</div><div class="company-sub-name">{{ $item->industry ?? 'Industry not set' }}</div></div></div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Status</span><strong>{{ ucfirst($item->status) }}</strong></div>
                                    <div class="activity-meta-row"><span>Contact</span><strong>{{ $item->contact_person ?? 'Not assigned' }}</strong></div>
                                    <div class="activity-meta-row"><span>Location</span><strong>{{ collect([$item->city, $item->region, $item->country])->filter()->implode(', ') ?: 'Not recorded' }}</strong></div>
                                    <div class="activity-meta-row"><span>Products</span><strong>{{ number_format($item->products_count) }}</strong></div>
                                    <div class="activity-meta-row"><span>Batches</span><strong>{{ number_format($item->batches_count) }}</strong></div>
                                    <div class="activity-meta-row"><span>Label Orders</span><strong>{{ number_format($item->label_orders_count) }}</strong></div>
                                </div>
                                <div class="role-preview"><div class="role-preview-title">Public Profile</div><div class="role-preview-list"><span>{{ $item->profile ? 'Profile content is configured' : 'Profile setup is still needed' }}</span><span>{{ $item->website ?: 'No website recorded' }}</span></div></div>
                                @if ($item->notes)<div class="activity-meta-row" style="align-items:flex-start"><span>Notes</span><strong>{{ $item->notes }}</strong></div>@endif
                                <a class="btn-share" href="{{ route('companies.show', $item) }}" style="width:100%;justify-content:center;margin-top:14px">Open Company Workspace</a>
                                <div class="form-row-2" style="margin-top:8px"><button class="btn-learn" type="button" data-company-drawer-open data-company-form="edit-{{ $item->id }}" style="width:100%;justify-content:center">Edit Company</button><a class="btn-learn" href="{{ route('active8.page', ['page' => 'company-profile', 'company' => $item->id]) }}" style="width:100%;justify-content:center">Profile Editor</a></div>
                            </div>
                        @endforeach
                        <div class="drawer-guide"><div class="admin-card-title">Company Flow</div><div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);margin:16px 0 0;overflow:hidden"><div class="onboarding-step"><div class="step-num done">1</div><div><div class="step-title active">Register company</div><div class="step-sub">Capture official identity and operations contacts.</div></div></div><div class="onboarding-step"><div class="step-num">2</div><div><div class="step-title active">Configure profile</div><div class="step-sub">Set customer-facing verification content and branding.</div></div></div><div class="onboarding-step"><div class="step-num">3</div><div><div class="step-title active">Add products</div><div class="step-sub">Create product records, batches, and label orders.</div></div></div></div></div>
                    </div>
                </div>
            </div>
        </section>
