<section class="screen {{ $activePage === 'label-orders' ? 'active' : '' }}" id="screen-label-orders">
            <div class="topbar center">
                <div>
                    <div class="page-title">Label Orders</div>
                    <div class="page-subtitle">Manage hologram and tamper-proof print orders connected to approved production batches.</div>
                </div>
                <div class="topbar-actions">
                    <a class="btn-learn" href="{{ route('active8.page', 'batches') }}">Batches</a>
                    <a class="btn-learn" href="{{ route('active8.page', 'label-codes') }}">View Codes</a>
                    <a class="btn-share" href="{{ route('active8.page', 'dashboard') }}">
                        <svg width="14" height="14"><use href="#i-activity"></use></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <div class="company-page-layout shop-orders-layout">
                <div>
                    @if (session('status'))
                        <div class="alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert-error">{{ $errors->first() }}</div>
                    @endif

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Orders</div>
                            <div class="company-detail-value">{{ number_format($labelOrders->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Printed</div>
                            <div class="company-detail-value">{{ number_format($labelOrders->where('status', 'printed')->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Pending</div>
                            <div class="company-detail-value">{{ number_format($labelOrders->where('status', 'pending')->count()) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Labels</div>
                            <div class="company-detail-value">{{ number_format($labelOrders->sum('print_quantity')) }}</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">USSD Codes</div>
                            <div class="company-detail-value">{{ number_format($labelCodeSummary['total'] ?? 0) }}</div>
                        </div>
                    </div>

                    <div class="admin-card">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Print Order Registry</div>
                                <div class="admin-card-sub">Track label type, print quantity, generated USSD codes, printer vendor, delivery date, and production status.</div>
                            </div>
                            <button class="btn-share" type="button" data-order-drawer-open data-order-form="create">
                                <svg width="14" height="14"><use href="#i-plus"></use></svg>
                                Create Order
                            </button>
                        </div>

                        <div class="table-toolbar" data-order-table-toolbar>
                            <label class="table-search" aria-label="Search label orders">
                                <svg width="14" height="14"><use href="#i-search"></use></svg>
                                <input type="search" data-order-search placeholder="Search company, batch, vendor">
                            </label>
                            <div class="table-filter-row">
                                <select class="font-select" data-order-type-filter aria-label="Filter by label type">
                                    <option value="all">All types</option>
                                    <option value="hologram">Hologram</option>
                                    <option value="tamper-proof">Tamper-proof</option>
                                    <option value="security seal">Security seal</option>
                                    <option value="combo">Combo</option>
                                </select>
                                <select class="font-select" data-order-status-filter aria-label="Filter by status">
                                    <option value="all">All statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="printed">Printed</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <form class="table-bulk-bar" method="POST" action="{{ route('label-orders.bulk') }}" data-order-bulk-form data-confirm-delete data-delete-name="selected label orders" data-delete-match="DELETE">
                            @csrf
                            <div>
                                <div class="table-bulk-count" data-order-bulk-count>0 selected</div>
                                <div class="company-sub-name">Apply status updates to selected print jobs.</div>
                            </div>
                            <div class="table-bulk-actions">
                                <select class="font-select" name="action" required>
                                    <option value="approve">Approve</option>
                                    <option value="print">Mark printed</option>
                                    <option value="ship">Mark shipped</option>
                                    <option value="deliver">Mark delivered</option>
                                    <option value="cancel">Cancel</option>
                                    <option value="delete">Delete</option>
                                </select>
                                <div data-order-bulk-inputs></div>
                                <button class="btn-share" type="submit">Apply</button>
                            </div>
                        </form>

                        <div class="admin-table-wrap">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th class="fit"><input class="row-check" type="checkbox" data-order-select-all aria-label="Select all visible orders"></th>
                                        <th>Order</th>
                                        <th>Batch</th>
                                        <th class="fit">Type</th>
                                        <th class="fit">Quantity</th>
                                        <th class="fit">USSD Codes</th>
                                        <th class="fit">Status</th>
                                        <th class="fit">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($labelOrders as $order)
                                        <tr class="row-clickable" data-order-row data-order-id="{{ $order->id }}" data-order-search-text="{{ Str::lower(($order->company?->name ?? '').' '.($order->batch?->batch_number ?? '').' '.($order->batch?->product?->name ?? '').' '.($order->printer_vendor ?? '').' '.($order->label_type ?? '')) }}" data-order-type="{{ $order->label_type }}" data-order-status="{{ $order->status }}">
                                            <td class="fit"><input class="row-check" type="checkbox" value="{{ $order->id }}" data-order-select aria-label="Select {{ $order->company?->name }} label order"></td>
                                            <td>
                                                <div class="company-name-cell">
                                                    <div class="company-logo-sm">{{ Str::of($order->label_type)->substr(0, 2)->upper() }}</div>
                                                    <div style="min-width:0">
                                                        <div class="company-main-name">{{ $order->company?->name ?? 'No company' }}</div>
                                                        <div class="company-sub-name">{{ $order->printer_vendor ?? 'Printer not assigned' }} · {{ $order->delivery_date?->format('M j, Y') ?? 'No delivery date' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="company-main-name">{{ $order->batch?->batch_number ?? 'General order' }}</div>
                                                <div class="company-sub-name">{{ $order->batch?->product?->name ?? 'No batch product' }}</div>
                                            </td>
                                            <td class="fit"><span class="status-pill">{{ $order->label_type }}</span></td>
                                            <td class="fit">{{ number_format($order->print_quantity) }}</td>
                                            <td class="fit">
                                                <span class="status-pill {{ $order->label_codes_count === $order->print_quantity ? 'active' : 'pending' }}">
                                                    {{ number_format($order->label_codes_count) }}
                                                </span>
                                            </td>
                                            <td class="fit"><span class="status-pill {{ $order->status }}">{{ $order->status }}</span></td>
                                            <td class="fit">
                                                <div class="table-actions">
                                                    <button class="icon-btn" type="button" data-order-drawer-open data-order-form="details-{{ $order->id }}" aria-label="View order details">
                                                        <svg width="15" height="15"><use href="#i-eye"></use></svg>
                                                    </button>
                                                    <a class="icon-btn" href="{{ route('label-orders.codes.export', $order) }}" aria-label="Download USSD codes CSV">
                                                        <svg width="15" height="15"><use href="#i-download"></use></svg>
                                                    </a>
                                                    <button class="icon-btn" type="button" data-order-drawer-open data-order-form="edit-{{ $order->id }}" aria-label="Edit label order">
                                                        <svg width="15" height="15"><use href="#i-edit"></use></svg>
                                                    </button>
                                                    <form method="POST" action="{{ route('label-orders.destroy', $order) }}" data-confirm-delete data-delete-name="{{ $order->company?->name ?? 'this label order' }}" data-delete-match="{{ $order->company?->name ?? 'DELETE' }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="icon-btn del" type="submit" aria-label="Delete label order">
                                                            <svg width="15" height="15"><use href="#i-trash"></use></svg>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr data-order-empty><td colspan="8">No label orders yet.</td></tr>
                                    @endforelse
                                    <tr class="filter-empty" data-order-no-results>
                                        <td colspan="8">
                                            <div class="filter-empty-title">No label orders match those filters.</div>
                                            <div class="filter-empty-copy">Try another company, batch, type, or status.</div>
                                            <button class="btn-learn" type="button" data-clear-order-filters>Clear filters</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="table-pagination" data-order-pagination>
                            <span data-order-page-summary>Showing orders</span>
                            <div class="pager-actions">
                                <button class="pager-btn" type="button" data-order-page-prev aria-label="Previous order page">‹</button>
                                <span data-order-page-current>1</span>
                                <button class="pager-btn" type="button" data-order-page-next aria-label="Next order page">›</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="user-create-drawer" id="order-drawer" aria-hidden="true">
                <div class="drawer-backdrop" data-order-drawer-close></div>
                <div class="drawer-panel" role="dialog" aria-modal="true" aria-labelledby="order-drawer-title">
                    <div class="drawer-header">
                        <div>
                            <div class="admin-card-title" id="order-drawer-title">Create Label Order</div>
                            <div class="admin-card-sub" id="order-drawer-subtitle">Create a print job for hologram or tamper-proof labels.</div>
                        </div>
                        <button class="drawer-close" type="button" data-order-drawer-close aria-label="Close label order panel">×</button>
                    </div>
                    <div class="drawer-body">
                        <form class="drawer-user-form active" id="order-form-create" method="POST" action="{{ route('label-orders.store') }}" data-form-title="Create Label Order" data-form-subtitle="Create a print job for hologram or tamper-proof labels.">
                            @csrf
                            <label class="form-label">Company</label>
                            <input type="hidden" name="company_id" value="{{ $companies->first()?->id }}" data-company-value required>
                            <div class="smart-select" data-smart-company>
                                <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>{{ $companies->first()?->name ?? 'Select company' }}</span></button>
                                <div class="smart-select-menu">
                                    <input class="smart-select-search" type="search" placeholder="Search companies" data-smart-search>
                                    <div class="smart-select-options">
                                        @foreach ($companies as $item)
                                            <button class="smart-option {{ $loop->first ? 'active' : '' }}" type="button" data-smart-option data-value="{{ $item->id }}" data-search="{{ Str::lower($item->name) }}">{{ $item->name }}</button>
                                        @endforeach
                                    </div>
                                    <div class="smart-empty" data-smart-empty>No companies found.</div>
                                </div>
                            </div>
                            <label class="form-label">Batch</label>
                            <input type="hidden" name="batch_id" value="" data-batch-value>
                            <div class="smart-select" data-smart-batch>
                                <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>General order</span></button>
                                <div class="smart-select-menu">
                                    <input class="smart-select-search" type="search" placeholder="Search batches" data-smart-search>
                                    <div class="smart-select-options">
                                        <button class="smart-option active" type="button" data-smart-option data-value="" data-company-id="all" data-search="general order">General order</button>
                                        @foreach ($batches as $batch)
                                            <button class="smart-option" type="button" data-smart-option data-value="{{ $batch->id }}" data-company-id="{{ $batch->product?->company_id }}" data-search="{{ Str::lower($batch->batch_number.' '.($batch->product?->name ?? '')) }}">{{ $batch->batch_number }} · {{ $batch->product?->name }}</button>
                                        @endforeach
                                    </div>
                                    <div class="smart-empty" data-smart-empty>No batches found for this company.</div>
                                </div>
                            </div>
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Label Type</label>
                                    <select class="font-select" name="label_type" required data-label-type-select>
                                        <option value="hologram">Hologram</option>
                                        <option value="tamper-proof">Tamper-proof</option>
                                        <option value="security seal">Security seal</option>
                                        <option value="combo">Combo</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Size</label>
                                    <input class="input-field" name="label_size" placeholder="30mm x 30mm">
                                </div>
                            </div>
                            <label class="form-label">Print Quantity</label>
                            <input class="input-field" type="number" min="1" name="print_quantity" value="1000" required>
                            <div class="company-sub-name" style="margin:-8px 0 12px">The system creates one unique USSD code for every printed label.</div>
                            <label class="form-label">Printer Vendor</label>
                            <input class="input-field" name="printer_vendor" placeholder="SecurePrint Ghana">
                            <div class="form-row-2">
                                <div>
                                    <label class="form-label">Status</label>
                                    <select class="font-select" name="status" required>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="printed">Printed</option>
                                        <option value="shipped">Shipped</option>
                                        <option value="delivered">Delivered</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Delivery Date</label>
                                    <input class="input-field" type="date" name="delivery_date">
                                </div>
                            </div>
                            <div class="role-preview" data-label-preview></div>
                            <label class="form-label">Notes</label>
                            <textarea class="textarea-field" name="notes" placeholder="Printing instructions, material, vendor notes, delivery details."></textarea>
                            <button class="btn-add-content" type="submit">Create Order</button>
                        </form>

                        @foreach ($labelOrders as $order)
                            <form class="drawer-user-form" id="order-form-edit-{{ $order->id }}" method="POST" action="{{ route('label-orders.update', $order) }}" data-form-title="Edit Label Order" data-form-subtitle="Update print details for {{ $order->company?->name ?? 'this order' }}.">
                                @csrf
                                @method('PUT')
                                <label class="form-label">Company</label>
                                <input type="hidden" name="company_id" value="{{ $order->company_id }}" data-company-value required>
                                <div class="smart-select" data-smart-company>
                                    <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>{{ $order->company?->name ?? 'Select company' }}</span></button>
                                    <div class="smart-select-menu">
                                        <input class="smart-select-search" type="search" placeholder="Search companies" data-smart-search>
                                        <div class="smart-select-options">
                                            @foreach ($companies as $item)
                                                <button class="smart-option {{ $order->company_id === $item->id ? 'active' : '' }}" type="button" data-smart-option data-value="{{ $item->id }}" data-search="{{ Str::lower($item->name) }}">{{ $item->name }}</button>
                                            @endforeach
                                        </div>
                                        <div class="smart-empty" data-smart-empty>No companies found.</div>
                                    </div>
                                </div>
                                <label class="form-label">Batch</label>
                                <input type="hidden" name="batch_id" value="{{ $order->batch_id }}" data-batch-value>
                                <div class="smart-select" data-smart-batch>
                                    <button class="smart-select-trigger" type="button" data-smart-toggle><span data-smart-label>{{ $order->batch ? $order->batch->batch_number.' · '.$order->batch?->product?->name : 'General order' }}</span></button>
                                    <div class="smart-select-menu">
                                        <input class="smart-select-search" type="search" placeholder="Search batches" data-smart-search>
                                        <div class="smart-select-options">
                                            <button class="smart-option {{ blank($order->batch_id) ? 'active' : '' }}" type="button" data-smart-option data-value="" data-company-id="all" data-search="general order">General order</button>
                                            @foreach ($batches as $batch)
                                                <button class="smart-option {{ $order->batch_id === $batch->id ? 'active' : '' }}" type="button" data-smart-option data-value="{{ $batch->id }}" data-company-id="{{ $batch->product?->company_id }}" data-search="{{ Str::lower($batch->batch_number.' '.($batch->product?->name ?? '')) }}">{{ $batch->batch_number }} · {{ $batch->product?->name }}</button>
                                            @endforeach
                                        </div>
                                        <div class="smart-empty" data-smart-empty>No batches found for this company.</div>
                                    </div>
                                </div>
                                <div class="form-row-2">
                                    <div>
                                        <label class="form-label">Label Type</label>
                                        <select class="font-select" name="label_type" required data-label-type-select>
                                            @foreach (['hologram', 'tamper-proof', 'security seal', 'combo'] as $type)
                                                <option value="{{ $type }}" @selected($order->label_type === $type)>{{ ucfirst($type) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Size</label>
                                        <input class="input-field" name="label_size" value="{{ $order->label_size }}">
                                    </div>
                                </div>
                                <label class="form-label">Print Quantity</label>
                                <input class="input-field" type="number" min="1" name="print_quantity" value="{{ $order->print_quantity }}" required>
                                <div class="company-sub-name" style="margin:-8px 0 12px">
                                    Saving will sync this order to {{ number_format($order->print_quantity) }} generated USSD codes. Quantity changes add codes or remove unused extras.
                                </div>
                                <label class="form-label">Printer Vendor</label>
                                <input class="input-field" name="printer_vendor" value="{{ $order->printer_vendor }}">
                                <div class="form-row-2">
                                    <div>
                                        <label class="form-label">Status</label>
                                        <select class="font-select" name="status" required>
                                            @foreach (['pending', 'approved', 'printed', 'shipped', 'delivered', 'cancelled'] as $status)
                                                <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">Delivery Date</label>
                                        <input class="input-field" type="date" name="delivery_date" value="{{ $order->delivery_date?->format('Y-m-d') }}">
                                    </div>
                                </div>
                                <div class="role-preview" data-label-preview></div>
                                <label class="form-label">Notes</label>
                                <textarea class="textarea-field" name="notes">{{ $order->notes }}</textarea>
                                <button class="btn-add-content" type="submit">Save Order</button>
                            </form>
                        @endforeach

                        @foreach ($labelOrders as $order)
                            <div class="drawer-user-form activity-panel" id="order-form-details-{{ $order->id }}" data-form-title="Order Details" data-form-subtitle="Print job details for {{ $order->company?->name ?? 'this order' }}.">
                                <div class="activity-hero">
                                    <div class="company-logo-sm" style="width:48px;height:48px;font-size:16px">{{ Str::of($order->label_type)->substr(0, 2)->upper() }}</div>
                                    <div style="min-width:0">
                                        <div class="company-main-name">{{ $order->company?->name ?? 'No company' }}</div>
                                        <div class="company-sub-name">{{ ucfirst($order->label_type) }} · {{ number_format($order->print_quantity) }} labels</div>
                                    </div>
                                </div>
                                <div class="activity-meta">
                                    <div class="activity-meta-row"><span>Batch</span><strong>{{ $order->batch?->batch_number ?? 'General order' }}</strong></div>
                                    <div class="activity-meta-row"><span>Product</span><strong>{{ $order->batch?->product?->name ?? 'No product' }}</strong></div>
                                    <div class="activity-meta-row"><span>Vendor</span><strong>{{ $order->printer_vendor ?? 'Not assigned' }}</strong></div>
                                    <div class="activity-meta-row"><span>Status</span><strong>{{ ucfirst($order->status) }}</strong></div>
                                    <div class="activity-meta-row"><span>USSD Codes</span><strong>{{ number_format($order->label_codes_count) }} / {{ number_format($order->print_quantity) }}</strong></div>
                                    <div class="activity-meta-row"><span>Delivery</span><strong>{{ $order->delivery_date?->format('M j, Y') ?? 'No date' }}</strong></div>
                                    <div class="activity-meta-row"><span>Updated</span><strong>{{ $order->updated_at?->diffForHumans() ?? 'Not recorded' }}</strong></div>
                                </div>
                                <div class="role-preview">
                                    <div class="role-preview-title">Sample USSD Codes</div>
                                    <div class="role-preview-list">
                                        @forelse ($order->labelCodes()->oldest('id')->take(8)->get() as $labelCode)
                                            <span>{{ $labelCode->formattedCode() }} · {{ $labelCode->status }}</span>
                                        @empty
                                            <span>No codes generated yet. Save this order to sync codes.</span>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="form-row-2" style="margin-top:14px">
                                    <a class="btn-share" href="{{ route('active8.page', ['page' => 'label-codes', 'code_order' => $order->id]) }}" style="width:100%;justify-content:center">View USSD Codes</a>
                                    <a class="btn-learn" href="{{ route('ussd.test') }}" style="width:100%;justify-content:center">Test USSD</a>
                                </div>
                                <a class="btn-share" href="{{ route('label-orders.codes.export', $order) }}" style="width:100%;justify-content:center;margin-top:14px">Download Print CSV</a>
                                <div class="role-preview" data-static-label="{{ $order->label_type }}"></div>
                                @if ($order->notes)
                                    <div class="activity-meta-row" style="align-items:flex-start"><span>Notes</span><strong>{{ $order->notes }}</strong></div>
                                @endif
                                <button class="btn-share" type="button" data-order-drawer-open data-order-form="edit-{{ $order->id }}" style="width:100%;justify-content:center;margin-top:14px">Edit Order</button>
                            </div>
                        @endforeach

                        <div class="drawer-guide">
                            <div class="admin-card-title">Label Flow</div>
                            <div class="onboarding-steps" style="grid-template-columns:1fr;border:1px solid var(--gray-200);border-radius:var(--radius);margin:16px 0 0;overflow:hidden">
                                <div class="onboarding-step"><div class="step-num done">1</div><div style="flex:1"><div class="step-title active" style="text-decoration:none;color:var(--gray-900)">Approve batch</div><div class="step-sub">Confirm product details, QA status, and batch quantity.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">2</div><div style="flex:1"><div class="step-title active">Attach QR</div><div class="step-sub">One secure QR verification URL is printed across the batch.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">3</div><div style="flex:1"><div class="step-title active">Generate USSD codes</div><div class="step-sub">Every printed label receives one unique code that can be checked by USSD.</div></div></div>
                                <div class="onboarding-step"><div class="step-num">4</div><div style="flex:1"><div class="step-title active">Track delivery</div><div class="step-sub">Follow vendor, print quantity, delivery date, and order status.</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
