<section class="screen {{ $activePage === 'batches' ? 'active' : '' }}" id="screen-batches">
            <div class="topbar center">
                <div>
                    <div class="page-title">Batches</div>
                    <div class="page-subtitle">Create, update, archive, and review production batches with one QR code per batch.</div>
                </div>
                <div class="topbar-actions">
                    <button class="btn-learn" type="button" data-batch-drawer-open data-batch-form="create">
                        <svg width="14" height="14"><use href="#i-plus"></use></svg>
                        Create Batch
                    </button>
                    <a class="btn-learn" href="{{ route('active8.page', 'products') }}">Products</a>
                    <a class="btn-share" href="{{ route('active8.page', 'dashboard') }}">
                        <svg width="14" height="14"><use href="#i-activity"></use></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="company-page-layout batches-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Batches</div>
                            <div class="company-detail-value">{{ number_format($batches->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Approved</div>
                            <div class="company-detail-value">{{ number_format($batches->where('status', 'approved')->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">With QR Code</div>
                            <div class="company-detail-value">{{ number_format($batches->filter(fn ($item) => $item->qrCode !== null)->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Labels</div>
                            <div class="company-detail-value">{{ number_format($batches->sum('quantity')) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Batch Registry</div>
                                <div class="admin-card-sub">Search, filter, and manage batch-level QR operations from one table.</div>
                            </div>
                            <button class="btn-share" type="button" data-batch-drawer-open data-batch-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create Batch
                            </button>
                        </div>

                        <div class="table-toolbar" data-batch-table-toolbar>
                            <label class="table-search" aria-label="Search batches">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-batch-search placeholder="Search batch, product, company, token">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-batch-qa-filter aria-label="Filter by QA status">
                                    <option value="all">All QA</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="failed">Failed</option>
                                    <option value="review">Review</option>
                                </select>
                                <select class="font-select" data-batch-status-filter aria-label="Filter by batch status">
                                    <option value="all">All statuses</option>
                                    <option value="draft">Draft</option>
                                    <option value="approved">Approved</option>
                                    <option value="printed">Printed</option>
                                    <option value="distributed">Distributed</option>
                                    <option value="recalled">Recalled</option>
                                </select>
                                <select class="font-select" data-batch-qr-filter aria-label="Filter by QR status">
                                    <option value="all">All QR</option>
                                    <option value="ready">QR active</option>
                                    <option value="expired">QR expired</option>
                                    <option value="revoked">QR revoked</option>
                                    <option value="missing">No QR</option>
                                </select>
                                <select class="font-select" data-batch-company-filter aria-label="Filter by company">
                                    <option value="all">All companies</option>
                                    @foreach ($companies as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <select class="font-select" data-batch-product-filter aria-label="Filter by product">
                                    <option value="all">All products</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <form class="table-bulk-bar" method="POST" action="{{ route('batches.bulk') }}" data-batch-bulk-form data-confirm-delete data-delete-name="selected batches" data-delete-match="DELETE">
                            @csrf
                            <div>
                                <div class="table-bulk-count" data-batch-bulk-count>0 selected</div>
                                <div class="company-sub-name">Apply production status updates to selected batches.</div>
                            </div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="approve">Approve</option>
                                    <option value="print">Mark printed</option>
                                    <option value="distribute">Mark distributed</option>
                                    <option value="recall">Recall</option>
                                    <option value="delete">Archive</option>
                                </select>
                                <div data-batch-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="fit"><input class="row-check" type="checkbox" data-batch-select-all aria-label="Select all visible batches"></th>
                                        <th>Batch</th>
                                        <th>Product</th>
                                        <th>Dates</th>
                                        <th class="fit">Qty</th>
                                        <th class="fit">QA</th>
                                        <th class="fit">Status</th>
                                        <th class="fit">QR</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($batches as $batch)
                                        @php
                                            $isExpired = $batch->expiry_date && $batch->expiry_date->isPast();
                                            $qrExpired = $batch->qrCode?->isExpired() ?? false;
                                            $qrFilterStatus = $batch->qrCode
                                                ? ($batch->qrCode->isRevoked() ? 'revoked' : ($qrExpired ? 'expired' : 'ready'))
                                                : 'missing';
                                            $expiresSoon = $batch->expiry_date && ! $isExpired && $batch->expiry_date->lte(now()->addDays(30));
                                            $healthClass = 'info';
                                            $healthLabel = 'QA pending';

                                            if ($batch->status === 'recalled') {
                                                $healthClass = 'danger';
                                                $healthLabel = 'Recalled';
                                            } elseif ($isExpired) {
                                                $healthClass = 'danger';
                                                $healthLabel = 'Expired';
                                            } elseif (! $batch->qrCode) {
                                                $healthClass = 'warning';
                                                $healthLabel = 'Missing QR';
                                            } elseif ($qrExpired) {
                                                $healthClass = 'danger';
                                                $healthLabel = 'QR expired';
                                            } elseif ($batch->qa_status !== 'approved') {
                                                $healthClass = 'warning';
                                                $healthLabel = 'QA pending';
                                            } elseif ($expiresSoon) {
                                                $healthClass = 'warning';
                                                $healthLabel = 'Expires soon';
                                            } elseif (in_array($batch->status, ['printed', 'distributed'], true)) {
                                                $healthClass = 'ready';
                                                $healthLabel = 'Ready to verify';
                                            } else {
                                                $healthClass = 'ready';
                                                $healthLabel = 'Ready to print';
                                            }
                                        @endphp
                                        <tr class="row-clickable {{ $isExpired || $qrExpired || $batch->status === 'recalled' ? 'row-danger' : ($expiresSoon ? 'row-warning' : '') }}" data-batch-row data-batch-id="{{ $batch->id }}" data-batch-search-text="{{ Str::lower(($batch->batch_number ?? '').' '.($batch->product?->name ?? '').' '.($batch->product?->company?->name ?? '').' '.($batch->qrCode?->token ?? '').' '.($batch->qrCode?->publicUrl() ?? '').' '.($batch->qrCode?->custom_path ?? '').' '.($batch->production_location ?? '')) }}" data-batch-qa="{{ $batch->qa_status }}" data-batch-status="{{ $batch->status }}" data-batch-qr="{{ $qrFilterStatus }}" data-batch-company="{{ $batch->product?->company_id ?? 'none' }}" data-batch-product="{{ $batch->product_id }}" data-batch-health="{{ $healthLabel }}">
                                            <td class="fit"><input class="row-check" type="checkbox" value="{{ $batch->id }}" data-batch-select aria-label="Select {{ $batch->batch_number }}"></td>
                                            <td>
                                                <div style="display:grid;gap:7px;min-width:0">
                                                    <div class="company-main-name">{{ $batch->batch_number }}</div>
                                                    <span class="health-pill {{ $healthClass }}">{{ $healthLabel }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $batch->product?->name ?? 'No product' }}</div>
                                                <div class="company-sub-name">{{ $batch->product?->company?->name ?? 'No company' }}</div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $batch->manufacture_date?->format('M j, Y') ?? 'No manufacture date' }}</div>
                                                <div class="company-sub-name">{{ $batch->expiry_date?->format('M j, Y') ?? 'No expiry date' }}</div>
                                            </td>
                                            <td class="fit">{{ number_format($batch->quantity) }}</td>
                                            <td class="fit"><span class="status-pill {{ $batch->qa_status }}">{{ $batch->qa_status }}</span></td>
                                            <td class="fit"><span class="status-pill {{ $batch->status }}">{{ $batch->status }}</span></td>
                                            <td class="fit"><span class="status-pill {{ $batch->qrCode ? $batch->qrCode->status : 'draft' }}">{{ $batch->qrCode ? $batch->qrCode->status : 'missing' }}</span></td>
                                            <td class="fit">
                                                <div class="table-actions">
                                                    <button class="icon-btn" type="button" data-batch-drawer-open data-batch-form="details-{{ $batch->id }}" aria-label="View batch details">
                                                        <svg width="15" height="15"><use href="#i-eye"></use></svg>
                                                    </button>
                                                    <button class="icon-btn" type="button" data-batch-drawer-open data-batch-form="edit-{{ $batch->id }}" aria-label="Edit batch">
                                                        <svg width="15" height="15"><use href="#i-edit"></use></svg>
                                                    </button>
                                                    @if ($batch->qrCode)
                                                        <button class="icon-btn" type="button" data-copy-text="{{ $batch->qrCode->publicUrl() }}" aria-label="Copy QR verification URL">
                                                            <svg width="15" height="15"><use href="#i-link"></use></svg>
                                                        </button>
                                                        <a class="icon-btn" href="{{ $batch->qrCode->publicUrl() }}" target="_blank" rel="noopener" aria-label="Open verification page">
                                                            <svg width="15" height="15"><use href="#i-globe"></use></svg>
                                                        </a>
                                                        <a class="icon-btn" href="{{ route('batches.qr-code.download', $batch) }}" aria-label="Download QR code">
                                                            <svg width="15" height="15"><use href="#i-download"></use></svg>
                                                        </a>
                                                        @if ($batch->qrCode->isRevoked())
                                                            <form method="POST" action="{{ route('batches.qr-code.activate', $batch) }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <button class="icon-btn" type="submit" aria-label="Activate QR code">
                                                                    <svg width="15" height="15"><use href="#i-check"></use></svg>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('batches.qr-code.revoke', $batch) }}" data-confirm-delete data-delete-name="{{ $batch->batch_number }} QR code" data-delete-match="REVOKE">
                                                                @csrf
                                                                @method('PUT')
                                                                <input type="hidden" name="revocation_reason" value="Manual admin revocation">
                                                                <button class="icon-btn del" type="submit" aria-label="Revoke QR code">
                                                                    <svg width="15" height="15"><use href="#i-alert"></use></svg>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endif
                                                    <form method="POST" action="{{ route('batches.destroy', $batch) }}" data-confirm-delete data-delete-name="{{ $batch->batch_number }}" data-delete-match="{{ $batch->batch_number }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="icon-btn del" type="submit" aria-label="Archive batch">
                                                            <svg width="15" height="15"><use href="#i-trash"></use></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-batch-empty><td colspan="9">@include('partials.admin-empty-state', ['icon' => 'i-qr', 'title' => 'No batches yet', 'copy' => 'Create a batch to generate its QR code and prepare label production.', 'actionLabel' => 'Create Batch', 'actionTarget' => 'data-batch-drawer-open data-batch-form="create"'])</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-batch-no-results>
                                        <td colspan="9">
                                            <div class="filter-empty-title">No batches match those filters.</div>
                                            <div class="filter-empty-copy">Try another batch, product, QA status, or QR state.</div>
                                            <button class="btn-learn" type="button" data-clear-batch-filters>Clear filters</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-batch-pagination>
                            <span data-batch-page-summary>Showing batches</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-batch-page-prev aria-label="Previous batch page">‹</button>
                                <span data-batch-page-current>1</span>
                                <button class="pager-btn" type="button" data-batch-page-next aria-label="Next batch page">›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="batch-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-batch-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="batch-drawer-title">
                    <div class="drawer-header">
                        <div>
                            <div class="admin-card-title" id="batch-drawer-title">Create Batch</div>
                            <div class="admin-card-sub" id="batch-drawer-subtitle">Create a production batch and generate its verification QR code.</div>
                        </div>
                        <button class="drawer-close" type="button" data-batch-drawer-close aria-label="Close batch panel">×</button>
                    </div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="batch-form-create" method="POST" action="{{ route('batches.store') }}" data-form-title="Create Batch" data-form-subtitle="Create a production batch and generate its verification QR code.">
                            @csrf
                            <label class="form-label">Product</label>
                            <input type="hidden" name="product_id" value="" data-product-value>
                            <div class="smart-select" data-batch-product-select>
                                <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>Search product</span></button>
                                <div class="smart-select-menu">
                                    <input class="smart-select-search" type="search" placeholder="Search products or companies" data-smart-search>
                                    <div class="smart-select-options">
                                        @foreach ($products as $product)
                                            <button class="smart-option" type="button" data-smart-option data-value="{{ $product->id }}" data-search="{{ Str::lower($product->name.' '.($product->company?->name ?? '').' '.($product->sku ?? '')) }}">{{ $product->name }} · {{ $product->company?->name }}</button>
                                        @endforeach
                                    </div>
                                    <div class="smart-empty" data-smart-empty>No products found.</div>
                                </div>
                            </div>
                            <label class="form-label">Batch Number</label>
                            <input class="input-field" name="batch_number" placeholder="AK-SG-2026-002" required>
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Manufacture Date</label>
                                    <input class="input-field" type="date" name="manufacture_date">
                                </div>
                                <div>
                                    <label class="form-label">Expiry Date</label>
                                    <input class="input-field" type="date" name="expiry_date">
                                </div>
                            </div>
                            <label class="form-label">Quantity</label>
                            <input class="input-field" type="number" min="0" name="quantity" value="0" required>
                            <label class="form-label">Production Location</label>
                            <input class="input-field" name="production_location" placeholder="Spintex Factory Line A">
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">QA Status</label>
                                    <select class="font-select" name="qa_status" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="failed">Failed</option>
                                        <option value="review">Review</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Batch Status</label>
                                    <select class="font-select" name="status" required>
                                        <option value="draft">Draft</option>
                                        <option value="approved">Approved</option>
                                        <option value="printed">Printed</option>
                                        <option value="distributed">Distributed</option>
                                        <option value="recalled">Recalled</option>
                                    </select>
                                </div>
                            </div>
                            @include('partials.batch-qr-controls', ['batch' => null, 'qrCode' => null])
                            <button class="btn-add-content" type="submit">Create Batch</button>
                        </form>

                        @foreach ($batches as $batch)
                            <form class="drawer-user-form" id="batch-form-edit-{{ $batch->id }}" method="POST" action="{{ route('batches.update', $batch) }}" data-form-title="Edit Batch" data-form-subtitle="Update {{ $batch->batch_number }}.">
                                @csrf
                                @method('PUT')
                                <label class="form-label">Product</label>
                                <input type="hidden" name="product_id" value="{{ $batch->product_id }}" data-product-value>
                                <div class="smart-select" data-batch-product-select>
                                    <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>{{ $batch->product?->name ?? 'Search product' }}{{ $batch->product?->company?->name ? ' · '.$batch->product?->company?->name : '' }}</span></button>
                                    <div class="smart-select-menu">
                                        <input class="smart-select-search" type="search" placeholder="Search products or companies" data-smart-search>
                                        <div class="smart-select-options">
                                            @foreach ($products as $product)
                                                <button class="smart-option {{ $batch->product_id === $product->id ? 'active' : '' }}" type="button" data-smart-option data-value="{{ $product->id }}" data-search="{{ Str::lower($product->name.' '.($product->company?->name ?? '').' '.($product->sku ?? '')) }}">{{ $product->name }} · {{ $product->company?->name }}</button>
                                            @endforeach
                                        </div>
                                        <div class="smart-empty" data-smart-empty>No products found.</div>
                                    </div>
                                </div>
                                <label class="form-label">Batch Number</label>
                                <input class="input-field" name="batch_number" value="{{ $batch->batch_number }}" required>
                                <div class="form-row-2">
                                    <div>
                                        <label class="form-label">Manufacture Date</label>
                                        <input class="input-field" type="date" name="manufacture_date" value="{{ $batch->manufacture_date?->format('Y-m-d') }}">
                                    </div>
                                    <div>
                                        <label class="form-label">Expiry Date</label>
                                        <input class="input-field" type="date" name="expiry_date" value="{{ $batch->expiry_date?->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <label class="form-label">Quantity</label>
                                <input class="input-field" type="number" min="0" name="quantity" value="{{ $batch->quantity }}" required>
                                <label class="form-label">Production Location</label>
                                <input class="input-field" name="production_location" value="{{ $batch->production_location }}">
                                <div class="form-row-2">
                                    <div>
                                        <label class="form-label">QA Status</label>
                                        <select class="font-select" name="qa_status" required>
                                            @foreach (['pending', 'approved', 'failed', 'review'] as $status)
                                                <option value="{{ $status }}" @selected($batch->qa_status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Batch Status</label>
                                        <select class="font-select" name="status" required>
                                            @foreach (['draft', 'approved', 'printed', 'distributed', 'recalled'] as $status)
                                                <option value="{{ $status }}" @selected($batch->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @include('partials.batch-qr-controls', ['batch' => $batch, 'qrCode' => $batch->qrCode])
                                <button class="btn-add-content" type="submit">Save Batch</button>
                            </form>
                        @endforeach

                        @foreach ($batches as $batch)
                            <div class="drawer-user-form activity-panel" id="batch-form-details-{{ $batch->id }}" data-form-title="Batch Details" data-form-subtitle="{{ $batch->batch_number }} verification workflow.">
                                <div class="activity-hero">
                                    <div class="company-logo-sm" style="width:48px;height:48px;font-size:16px">{{ Str::of($batch->batch_number)->substr(0, 2)->upper() }}</div>
                                    <div style="min-width:0">
                                        <div class="company-main-name">{{ $batch->batch_number }}</div>
                                        <div class="company-sub-name">{{ $batch->product?->name ?? 'No product' }} · {{ $batch->product?->company?->name ?? 'No company' }}</div>
                                    </div>
                                </div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Quantity</span><strong>{{ number_format($batch->quantity) }} labels</strong></div>
                                    <div class="activity-meta-row"><span>QA Status</span><strong>{{ ucfirst($batch->qa_status) }}</strong></div>
                                    <div class="activity-meta-row"><span>Batch Status</span><strong>{{ ucfirst($batch->status) }}</strong></div>
                                    <div class="activity-meta-row"><span>Manufacture</span><strong>{{ $batch->manufacture_date?->format('M j, Y') ?? 'No date' }}</strong></div>
                                    <div class="activity-meta-row"><span>Expiry</span><strong>{{ $batch->expiry_date?->format('M j, Y') ?? 'No date' }}</strong></div>
                                    <div class="activity-meta-row"><span>Location</span><strong>{{ $batch->production_location ?? 'Not recorded' }}</strong></div>
                                </div>
                                <div class="role-preview">
                                    <div class="role-preview-title">Batch QR</div>
                                    @if ($batch->qrCode)
                                        <div style="display:flex;align-items:center;gap:14px">
                                            <span style="width:88px;height:88px;display:inline-flex;align-items:center;justify-content:center;border:1px solid var(--gray-200);border-radius:12px;background:#fff;overflow:hidden">
                                                {!! $batch->qrCode->svg(88) !!}
                                            </span>
                                            <div style="min-width:0">
                                                <div class="company-main-name">{{ $batch->qrCode->token }}</div>
                                                <div class="company-sub-name">{{ $batch->qrCode->publicUrl() }}</div>
                                                @if ($batch->qrCode->custom_path)
                                                    <div class="company-sub-name">Custom link: /verify/{{ $batch->qrCode->custom_path }}</div>
                                                @endif
                                                <div style="margin-top:8px"><span class="status-pill {{ $batch->qrCode->status }}">{{ $batch->qrCode->status }}</span></div>
                                                <div class="table-actions" style="margin-top:10px">
                                                    <button class="icon-btn" type="button" data-copy-text="{{ $batch->qrCode->publicUrl() }}" aria-label="Copy QR verification URL">
                                                        <svg width="15" height="15"><use href="#i-link"></use></svg>
                                                    </button>
                                                    <a class="icon-btn" href="{{ $batch->qrCode->publicUrl() }}" target="_blank" rel="noopener" aria-label="Open verification page">
                                                        <svg width="15" height="15"><use href="#i-globe"></use></svg>
                                                    </a>
                                                    <a class="icon-btn" href="{{ route('batches.qr-code.download', $batch) }}" aria-label="Download QR code">
                                                        <svg width="15" height="15"><use href="#i-download"></use></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="activity-meta" style="margin-top:14px">
                                            <div class="activity-meta-row"><span>Generated</span><strong>{{ $batch->qrCode->generated_at?->format('M j, Y H:i') ?? 'Not recorded' }}</strong></div>
                                            <div class="activity-meta-row"><span>Activated</span><strong>{{ $batch->qrCode->activated_at?->format('M j, Y H:i') ?? 'Not active' }}</strong></div>
                                            <div class="activity-meta-row"><span>QR Expires</span><strong>{{ $batch->qrCode->expires_at?->format('M j, Y H:i') ?? 'No QR expiry' }}</strong></div>
                                            <div class="activity-meta-row"><span>Revoked</span><strong>{{ $batch->qrCode->revoked_at?->format('M j, Y H:i') ?? 'Not revoked' }}</strong></div>
                                            @if ($batch->qrCode->revocation_reason)
                                                <div class="activity-meta-row"><span>Revocation reason</span><strong>{{ $batch->qrCode->revocation_reason }}</strong></div>
                                            @endif
                                        </div>
                                        <form method="POST" action="{{ route('batches.qr-code.controls', $batch) }}" style="margin-top:12px">
                                            @csrf
                                            @method('PUT')
                                            @include('partials.batch-qr-controls', ['batch' => $batch, 'qrCode' => $batch->qrCode])
                                            <button class="btn-share" type="submit" style="width:100%;justify-content:center;margin-top:12px">Save QR Settings</button>
                                        </form>
                                        @if ($batch->qrCode->isRevoked())
                                            <form method="POST" action="{{ route('batches.qr-code.activate', $batch) }}" style="margin-top:12px">
                                                @csrf
                                                @method('PUT')
                                                <button class="btn-share" type="submit" style="width:100%;justify-content:center">Reactivate QR Code</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('batches.qr-code.revoke', $batch) }}" data-confirm-delete data-delete-name="{{ $batch->batch_number }} QR code" data-delete-match="REVOKE" style="margin-top:12px">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="revocation_reason" value="Manual admin revocation">
                                                <button class="btn-learn" type="submit" style="width:100%;justify-content:center;color:var(--red)">Revoke QR Code</button>
                                            </form>
                                        @endif
                                    @else
                                        <div class="role-preview-list"><span>No QR code generated yet.</span></div>
                                        <form method="POST" action="{{ route('batches.qr-code.store', $batch) }}" style="margin-top:12px">
                                            @csrf
                                            <button class="btn-share" type="submit" style="width:100%;justify-content:center">Generate QR</button>
                                        </form>
                                    @endif
                                </div>
                                <button class="btn-share" type="button" data-batch-drawer-open data-batch-form="edit-{{ $batch->id }}" style="width:100%;justify-content:center;margin-top:14px">Edit Batch</button>
                            </div>
                        @endforeach

                        <div class="drawer-guide">
                            <div class="admin-card-title">Batch Flow</div>
                            <div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);margin:16px 0 0;overflow:hidden">
                                <div class="onboarding-step"><div class="step-num done">1</div><div style="flex:1"><div class="step-title active" style="text-decoration:none;color:var(--gray-900)">Create batch</div><div class="step-sub">Capture production dates, quantity, and QA status.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">2</div><div style="flex:1"><div class="step-title active">Generate QR</div><div class="step-sub">One verification URL for the whole batch.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">3</div><div style="flex:1"><div class="step-title active">Print labels</div><div class="step-sub">Apply the QR on hologram or tamper-proof labels.</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
