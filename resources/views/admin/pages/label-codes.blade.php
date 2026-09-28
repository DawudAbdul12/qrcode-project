<section class="screen {{ $activePage === 'label-codes' ? 'active' : '' }}" id="screen-label-codes">
    <div class="topbar center">
        <div>
            <div class="page-title">USSD Codes</div>
            <div class="page-subtitle">View generated scratch codes by order, company, product, batch, status, and verification count.</div>
        </div>
        <div class="topbar-actions">
            <a class="btn-learn" href="{{ route('active8.page', 'label-orders') }}">Label Orders</a>
            <button class="btn-learn" type="button" data-print-flow-open>
                <svg width="14" height="14"><use href="#i-activity"></use></svg>
                Printing Flow
            </button>
            <a class="btn-share" href="{{ route('ussd.test') }}">
                <svg width="14" height="14"><use href="#i-phone"></use></svg>
                Test USSD
            </a>
        </div>
    </div>

    <div class="company-page-layout scan-logs-layout">
        <div>
            <div class="company-detail-grid">
                <div class="company-detail-card">
                    <div class="company-detail-label">Total Codes</div>
                    <div class="company-detail-value">{{ number_format($labelCodeSummary['total'] ?? 0) }}</div>
                </div>
                <div class="company-detail-card">
                    <div class="company-detail-label">Unused</div>
                    <div class="company-detail-value">{{ number_format($labelCodeSummary['unused'] ?? 0) }}</div>
                </div>
                <div class="company-detail-card">
                    <div class="company-detail-label">Verified</div>
                    <div class="company-detail-value">{{ number_format($labelCodeSummary['verified'] ?? 0) }}</div>
                </div>
                <div class="company-detail-card">
                    <div class="company-detail-label">Suspicious</div>
                    <div class="company-detail-value">{{ number_format($labelCodeSummary['suspicious'] ?? 0) }}</div>
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card-header">
                    <div>
                        <div class="admin-card-title">Scratch Code Registry</div>
                        <div class="admin-card-sub">Use this screen for lookup and status review. Use order CSV exports for production printing.</div>
                    </div>
                    <span class="status-pill active">{{ method_exists($labelCodes, 'total') ? number_format($labelCodes->total()) : number_format($labelCodes->count()) }} shown by filters</span>
                </div>

                <form class="table-toolbar" method="GET" action="{{ route('active8.page', 'label-codes') }}">
                    <label class="table-search" aria-label="Search USSD codes">
                        <svg width="14" height="14"><use href="#i-search"></use></svg>
                        <input type="search" name="code_search" value="{{ $labelCodeFilters['search'] ?? '' }}" placeholder="Search code">
                    </label>
                    <div class="table-filter-row">
                        <select class="font-select" name="code_status" aria-label="Filter by code status">
                            @foreach (['all' => 'All statuses', 'unused' => 'Unused', 'used' => 'Used', 'suspicious' => 'Suspicious', 'revoked' => 'Revoked', 'expired' => 'Expired', 'recalled' => 'Recalled'] as $value => $label)
                                <option value="{{ $value }}" @selected(($labelCodeFilters['status'] ?? 'all') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @php
                            $orderPickerLabel = $selectedLabelOrder
                                ? '#'.$selectedLabelOrder->id.' · '.($selectedLabelOrder->company?->name ?? 'No company')
                                : (($labelCodeFilters['order_search'] ?? '') ? 'Matching orders' : 'All orders');
                            $orderPickerSub = $selectedLabelOrder
                                ? (($selectedLabelOrder->batch?->product?->name ?? 'No product').' · '.($selectedLabelOrder->batch?->batch_number ?? 'General'))
                                : (($labelCodeFilters['order_search'] ?? '') ? $labelOrderOptions->count().' results for "'.$labelCodeFilters['order_search'].'"' : 'Latest print jobs');
                        @endphp
                        <details class="order-picker" data-order-live-picker data-order-search-url="{{ route('label-orders.search') }}" data-order-current="{{ $labelCodeFilters['order'] ?? 'all' }}">
                            <summary class="order-picker-trigger">
                                <span class="order-picker-badge">Order</span>
                                <span class="order-picker-current">
                                    <strong>{{ $orderPickerLabel }}</strong>
                                    <small>{{ $orderPickerSub }}</small>
                                </span>
                                <svg width="15" height="15"><use href="#i-filter"></use></svg>
                            </summary>
                            <div class="order-picker-popover">
                                <div class="order-picker-head">
                                    <label class="order-search-control" aria-label="Search label orders">
                                        <svg width="14" height="14"><use href="#i-search"></use></svg>
                                        <input type="search" name="code_order_search" value="{{ $labelCodeFilters['order_search'] ?? '' }}" placeholder="Search order, company, product, batch" autocomplete="off" data-order-live-input>
                                    </label>
                                    <button class="order-search-submit" type="submit" name="code_order" value="all">Find</button>
                                </div>
                                <div class="order-option-list" aria-label="Matching label orders" data-order-live-results>
                                    <button class="order-option {{ ($labelCodeFilters['order'] ?? 'all') === 'all' ? 'active' : '' }}" type="submit" name="code_order" value="all">
                                        <span>
                                            <strong>All matching orders</strong>
                                            <small>{{ ($labelCodeFilters['order_search'] ?? '') ? 'Filtered by search text' : 'Latest print jobs' }}</small>
                                        </span>
                                        <em>{{ number_format($labelOrderOptions->count()) }} shown</em>
                                    </button>
                                    @foreach ($labelOrderOptions as $order)
                                        <button class="order-option {{ (string) ($labelCodeFilters['order'] ?? 'all') === (string) $order->id ? 'active' : '' }}" type="submit" name="code_order" value="{{ $order->id }}">
                                            <span>
                                                <strong>#{{ $order->id }} · {{ $order->company?->name ?? 'No company' }}</strong>
                                                <small>{{ $order->batch?->product?->name ?? 'No product' }} · {{ $order->batch?->batch_number ?? 'General' }} · {{ $order->printer_vendor ?? 'No printer' }}</small>
                                            </span>
                                            <em>{{ number_format($order->label_codes_count) }} codes</em>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </details>
                        <select class="font-select" name="per_page" aria-label="Rows per page">
                            @foreach ([25, 50, 100, 250] as $perPageOption)
                                <option value="{{ $perPageOption }}" @selected((int) ($labelCodeFilters['per_page'] ?? 50) === $perPageOption)>{{ $perPageOption }} / page</option>
                            @endforeach
                        </select>
                        <button class="btn-share" type="submit" name="code_order" value="{{ $labelCodeFilters['order'] ?? 'all' }}">Apply</button>
                        <a class="btn-learn" href="{{ route('active8.page', 'label-codes') }}">Reset</a>
                    </div>
                </form>

                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th class="fit">Seq</th>
                                <th>Scratch Code</th>
                                <th>Order</th>
                                <th>Product / Batch</th>
                                <th class="fit">Status</th>
                                <th class="fit">Checks</th>
                                <th class="fit">Last Checked</th>
                                <th class="fit">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($labelCodes as $labelCode)
                                <tr>
                                    <td class="fit">{{ method_exists($labelCodes, 'firstItem') ? $labelCodes->firstItem() + $loop->index : $loop->iteration }}</td>
                                    <td>
                                        <div class="company-main-name">{{ $labelCode->code }}</div>
                                        <div class="company-sub-name">{{ $labelCode->formattedCode() }}</div>
                                    </td>
                                    <td>
                                        <div class="company-main-name">Order #{{ $labelCode->label_order_id }}</div>
                                        <div class="company-sub-name">{{ $labelCode->labelOrder?->company?->name ?? $labelCode->company?->name ?? 'No company' }}</div>
                                    </td>
                                    <td>
                                        <div class="company-main-name">{{ $labelCode->batch?->product?->name ?? 'No product' }}</div>
                                        <div class="company-sub-name">{{ $labelCode->batch?->batch_number ?? 'No batch' }}</div>
                                    </td>
                                    <td class="fit"><span class="status-pill {{ $labelCode->status }}">{{ $labelCode->status }}</span></td>
                                    <td class="fit">{{ number_format($labelCode->verification_count) }}</td>
                                    <td class="fit">
                                        <div class="company-main-name">{{ $labelCode->last_verified_at?->diffForHumans() ?? 'Never' }}</div>
                                        <div class="company-sub-name">{{ $labelCode->last_verified_at?->format('M j, Y H:i') }}</div>
                                    </td>
                                    <td class="fit">
                                        <div class="table-actions">
                                            <button class="icon-btn" type="button" data-copy-text="{{ $labelCode->code }}" aria-label="Copy scratch code">
                                                <svg width="15" height="15"><use href="#i-code"></use></svg>
                                            </button>
                                            @if ($labelCode->labelOrder)
                                                <a class="icon-btn" href="{{ route('label-orders.codes.export', $labelCode->labelOrder) }}" aria-label="Download order codes CSV">
                                                    <svg width="15" height="15"><use href="#i-download"></use></svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">@include('partials.admin-empty-state', ['icon' => 'i-code', 'title' => 'No USSD codes found', 'copy' => 'Create or sync a label order to generate scratch codes.', 'actionLabel' => 'Create Label Order', 'actionTarget' => route('active8.page', 'label-orders'), 'actionType' => 'link'])</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if (method_exists($labelCodes, 'total') && $labelCodes->total() > 0)
                    @php
                        $currentPage = $labelCodes->currentPage();
                        $lastPage = $labelCodes->lastPage();
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp
                    <div class="code-pagination">
                        <div class="code-pagination-meta">
                            Showing {{ number_format($labelCodes->firstItem()) }}-{{ number_format($labelCodes->lastItem()) }}
                            of {{ number_format($labelCodes->total()) }} codes
                        </div>

                        @if ($labelCodes->hasPages())
                            <div class="code-pagination-actions" aria-label="USSD code pages">
                                @if ($labelCodes->onFirstPage())
                                    <span class="pager-btn disabled" aria-hidden="true">«</span>
                                    <span class="pager-btn disabled" aria-hidden="true">‹</span>
                                @else
                                    <a class="pager-btn" href="{{ $labelCodes->url(1) }}" aria-label="First code page">«</a>
                                    <a class="pager-btn" href="{{ $labelCodes->previousPageUrl() }}" aria-label="Previous code page">‹</a>
                                @endif

                                @if ($startPage > 1)
                                    <a class="pager-number" href="{{ $labelCodes->url(1) }}">1</a>
                                    @if ($startPage > 2)
                                        <span class="pager-ellipsis">...</span>
                                    @endif
                                @endif

                                @for ($page = $startPage; $page <= $endPage; $page++)
                                    @if ($page === $currentPage)
                                        <span class="pager-number active" aria-current="page">{{ $page }}</span>
                                    @else
                                        <a class="pager-number" href="{{ $labelCodes->url($page) }}">{{ $page }}</a>
                                    @endif
                                @endfor

                                @if ($endPage < $lastPage)
                                    @if ($endPage < $lastPage - 1)
                                        <span class="pager-ellipsis">...</span>
                                    @endif
                                    <a class="pager-number" href="{{ $labelCodes->url($lastPage) }}">{{ $lastPage }}</a>
                                @endif

                                @if ($labelCodes->hasMorePages())
                                    <a class="pager-btn" href="{{ $labelCodes->nextPageUrl() }}" aria-label="Next code page">›</a>
                                    <a class="pager-btn" href="{{ $labelCodes->url($lastPage) }}" aria-label="Last code page">»</a>
                                @else
                                    <span class="pager-btn disabled" aria-hidden="true">›</span>
                                    <span class="pager-btn disabled" aria-hidden="true">»</span>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @php
            $flowOrder = $selectedLabelOrder;
            $flowCodeCount = (int) ($flowOrder?->label_codes_count ?? 0);
            $flowPrintQuantity = (int) ($flowOrder?->print_quantity ?? 0);
            $flowCodesReady = $flowOrder && $flowCodeCount === $flowPrintQuantity && $flowPrintQuantity > 0;
            $flowPrinted = $flowOrder && in_array($flowOrder->status, ['printed', 'shipped', 'delivered'], true);
            $flowDelivered = $flowOrder && in_array($flowOrder->status, ['shipped', 'delivered'], true);
            $flowStages = collect([
                [
                    'label' => 'Order',
                    'title' => 'Select print order',
                    'detail' => $flowOrder ? ($flowOrder->label_type.' · '.number_format($flowPrintQuantity).' labels') : 'Filter by one print order.',
                    'done' => $flowOrder !== null,
                ],
                [
                    'label' => 'Codes',
                    'title' => 'Review generated codes',
                    'detail' => $flowOrder ? number_format($flowCodeCount).' of '.number_format($flowPrintQuantity).' codes ready.' : 'Codes are created from print quantity.',
                    'done' => $flowCodesReady,
                ],
                [
                    'label' => 'Print',
                    'title' => 'Export and print',
                    'detail' => 'Send CSV to printer and confirm production.',
                    'done' => $flowPrinted,
                ],
                [
                    'label' => 'Release',
                    'title' => 'Pack and test',
                    'detail' => 'Apply labels, then test a sample code.',
                    'done' => $flowDelivered,
                ],
            ]);
            $flowProgress = $flowStages->where('done', true)->count();
            $flowCurrentIndex = min($flowProgress, $flowStages->count() - 1);
            $flowProgressWidth = $flowStages->count() > 1 ? (($flowProgress - 1) / ($flowStages->count() - 1)) * 100 : 0;
            $flowProgressWidth = $flowProgress === 0 ? 0 : max(0, min(100, $flowProgressWidth));
            $flowNextAction = match (true) {
                ! $flowOrder => 'Select an order from the filter to start the print run.',
                ! $flowCodesReady => 'Regenerate or sync this order until code count matches print quantity.',
                ! $flowPrinted => 'Download the CSV, send it to print, then mark this order printed.',
                ! $flowDelivered => 'Pack labels onto product boxes, test one sample, then ship or deliver the order.',
                default => 'This print flow is complete and ready for customer verification.',
            };
        @endphp

        <div class="print-flow-modal" id="print-flow-modal" aria-hidden="true">
            <div class="print-flow-modal-backdrop" data-print-flow-close></div>
            <div class="print-flow-modal-card" role="dialog" aria-modal="true" aria-labelledby="print-flow-title">
                <div class="print-flow-modal-head">
                    <div>
                        <div class="admin-card-title" id="print-flow-title">Printing Flow</div>
                        <div class="admin-card-sub">Move scratch labels from generated USSD codes to boxed products.</div>
                    </div>
                    <button class="drawer-close" type="button" data-print-flow-close aria-label="Close printing flow">×</button>
                </div>

                <div class="print-flow-modal-body">
                    @if ($flowOrder)
                        <div class="print-flow-summary">
                            <div>
                                <div class="company-main-name">Order #{{ $flowOrder->id }}</div>
                                <div class="company-sub-name">{{ $flowOrder->company?->name ?? 'No company' }} · {{ $flowOrder->batch?->batch_number ?? 'General order' }}</div>
                            </div>
                            <span class="status-pill {{ $flowOrder->status }}">{{ $flowOrder->status }}</span>
                        </div>
                        <div class="print-flow-progress-copy">{{ $flowProgress }} of {{ $flowStages->count() }} stages complete</div>
                    @else
                        <div class="print-flow-empty">
                            <div class="company-main-name">Choose a label order</div>
                            <div class="company-sub-name">Use the order filter above to inspect one print job and unlock its production actions.</div>
                        </div>
                    @endif

                    <div class="print-flow-bar" aria-label="Scratch label production flow">
                        <div class="print-flow-track"><span style="width: {{ $flowProgressWidth }}%"></span></div>
                        @foreach ($flowStages as $index => $stage)
                            @php
                                $isCurrent = $index === $flowCurrentIndex && ! $stage['done'];
                                $isFuture = ! $stage['done'] && ! $isCurrent;
                            @endphp
                            <div class="print-flow-stage {{ $stage['done'] ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }} {{ $isFuture ? 'future' : '' }}">
                                <div class="print-flow-node">{{ $stage['done'] ? '✓' : $index + 1 }}</div>
                                <div class="print-flow-label">{{ $stage['label'] }}</div>
                            </div>
                        @endforeach
                    </div>

                    <div class="print-flow-stage-list">
                        @foreach ($flowStages as $index => $stage)
                            @php
                                $isCurrent = $index === $flowCurrentIndex && ! $stage['done'];
                            @endphp
                            <div class="print-flow-stage-row {{ $stage['done'] ? 'done' : '' }} {{ $isCurrent ? 'current' : '' }}">
                                <span>{{ $index + 1 }}</span>
                                <div>
                                    <strong>{{ $stage['title'] }}</strong>
                                    <small>{{ $stage['detail'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="print-flow-next">
                        <div class="company-detail-label">Next action</div>
                        <div class="company-main-name">{{ $flowNextAction }}</div>
                    </div>
                </div>

                <div class="print-flow-modal-actions">
                    @if ($flowOrder)
                        <a class="btn-share" href="{{ route('label-orders.codes.export', $flowOrder) }}">
                            <svg width="14" height="14"><use href="#i-download"></use></svg>
                            Download Print CSV
                        </a>
                        @if (! $flowPrinted)
                            <form method="POST" action="{{ route('label-orders.bulk') }}">
                                @csrf
                                <input type="hidden" name="action" value="print">
                                <input type="hidden" name="order_ids[]" value="{{ $flowOrder->id }}">
                                <button class="btn-learn" type="submit">Mark Printed</button>
                            </form>
                        @endif
                        <a class="btn-learn" href="{{ route('ussd.test') }}">Test Sample Code</a>
                    @else
                        <button class="btn-learn" type="button" data-print-flow-close>Close</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
