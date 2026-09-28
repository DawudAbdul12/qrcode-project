<section class="screen {{ $activePage === 'scan-analytics' ? 'active' : '' }}" id="screen-scan-analytics">
            @php
                $analyticsQueryParams = [
                    'range' => $analyticsReport['range'],
                    'status' => $analyticsReport['status_filter'],
                    'device' => $analyticsReport['device_filter'],
                    'company' => $analyticsReport['company_filter'],
                    'product' => $analyticsReport['product_filter'],
                    'batch' => $analyticsReport['batch_filter'],
                ];

                if ($analyticsReport['range'] === 'custom') {
                    $analyticsQueryParams['start_date'] = $analyticsReport['period_start']->toDateString();
                    $analyticsQueryParams['end_date'] = $analyticsReport['period_end']->toDateString();
                }

                $analyticsActiveFilters = collect([
                    'status' => ['label' => 'Status', 'value' => $analyticsReport['status_filter'], 'options' => $analyticsReport['status_filters']],
                    'device' => ['label' => 'Device', 'value' => $analyticsReport['device_filter'], 'options' => $analyticsReport['device_filters']],
                    'company' => ['label' => 'Company', 'value' => $analyticsReport['company_filter'], 'options' => $analyticsReport['company_filters']],
                    'product' => ['label' => 'Product', 'value' => $analyticsReport['product_filter'], 'options' => $analyticsReport['product_filters']],
                    'batch' => ['label' => 'Batch', 'value' => $analyticsReport['batch_filter'], 'options' => $analyticsReport['batch_filters']],
                ])->filter(fn ($filter) => $filter['value'] !== 'all');
            @endphp
            <div class="topbar center">
                <div>
                    <div class="page-title">Scan Intelligence</div>
                    <div class="page-subtitle">Monitor verification trends, suspicious scans, high-traffic products, and customer device patterns.</div>
                </div>
                <div class="topbar-actions">
                    <a class="btn-learn" href="{{ route('active8.page', 'scan-logs') }}">Scan Logs</a>
                    <a class="btn-share" href="{{ route('active8.page', array_merge(['page' => 'scan-analytics'], $analyticsQueryParams)) }}">
                        <svg width="14" height="14"><use href="#i-download"></use></svg>
                        Report
                    </a>
                </div>
            </div>

            <div class="company-page-layout analytics-layout">
                <div>
                    <div class="admin-card">
                        <div class="admin-card-header" style="margin-bottom:0">
                            <div>
                                <div class="admin-card-title">Analytics Controls</div>
                                <div class="admin-card-sub">{{ $analyticsReport['period_display'] }} · {{ $analyticsReport['status_filters'][$analyticsReport['status_filter']] }} · {{ $analyticsReport['device_filters'][$analyticsReport['device_filter']] }}</div>
                            </div>
                            <div class="analytics-date-wrap" data-date-dropdown>
                                <button class="date-filter-btn" type="button" data-date-toggle aria-expanded="false">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                    <span class="date-filter-copy">
                                        <span class="date-filter-label">Date range</span>
                                        <span class="date-filter-value">{{ $analyticsReport['label'] }}</span>
                                    </span>
                                    <svg class="chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div class="date-dropdown" role="dialog" aria-label="Choose analytics date range">
                                    <div class="range-list">
                                        <div class="date-range-summary">
                                            <div class="date-range-label">Selected range</div>
                                            <div class="date-range-value">{{ $analyticsReport['period_display'] }}</div>
                                        </div>
                                        @foreach ($analyticsReport['ranges'] as $rangeKey => $range)
                                            <a class="range-item {{ $analyticsReport['range'] === (string) $rangeKey ? 'active' : '' }}" href="{{ route('active8.page', array_merge(['page' => 'scan-analytics'], Illuminate\Support\Arr::except($analyticsQueryParams, ['start_date', 'end_date']), ['range' => (string) $rangeKey])) }}">{{ $range['label'] }}</a>
                                        @endforeach
                                    </div>
                                    <div class="calendars">
                                        @foreach ($analyticsReport['calendar_months'] as $month)
                                            <div class="cal">
                                                <div class="cal-header">
                                                    <span class="cal-nav">{{ $loop->first ? '‹' : '' }}</span>
                                                    <span>{{ $month['title'] }}</span>
                                                    <span class="cal-nav">{{ $loop->last ? '›' : '' }}</span>
                                                </div>
                                                <div class="cal-grid">
                                                    @foreach (['Su','Mo','Tu','We','Th','Fr','Sa'] as $day)
                                                        <div class="cal-day-header">{{ $day }}</div>
                                                    @endforeach
                                                    @for ($i = 0; $i < $month['offset']; $i++)
                                                        <div class="cal-day empty"></div>
                                                    @endfor
                                                    @foreach ($month['days'] as $day)
                                                        <button class="cal-day {{ $day['class'] }}" type="button" data-calendar-day="{{ $day['date'] }}">{{ $day['day'] }}</button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form class="analytics-filter-form" method="GET" action="{{ route('active8.page', 'scan-analytics') }}">
                            <input type="hidden" name="range" value="{{ $analyticsReport['range'] }}">
                            @if ($analyticsReport['range'] === 'custom')
                                <input type="hidden" name="start_date" value="{{ $analyticsReport['period_start']->toDateString() }}">
                                <input type="hidden" name="end_date" value="{{ $analyticsReport['period_end']->toDateString() }}">
                            @endif
                            <div class="analytics-filter-grid">
                                <label class="premium-filter">
                                    <span class="premium-filter-label">Status</span>
                                    <select name="status">
                                        @foreach ($analyticsReport['status_filters'] as $value => $label)
                                            <option value="{{ $value }}" @selected($analyticsReport['status_filter'] === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="premium-filter">
                                    <span class="premium-filter-label">Device</span>
                                    <select name="device">
                                        @foreach ($analyticsReport['device_filters'] as $value => $label)
                                            <option value="{{ $value }}" @selected($analyticsReport['device_filter'] === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="premium-filter">
                                    <span class="premium-filter-label">Company</span>
                                    <select name="company">
                                        @foreach ($analyticsReport['company_filters'] as $value => $label)
                                            <option value="{{ $value }}" @selected($analyticsReport['company_filter'] === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="premium-filter">
                                    <span class="premium-filter-label">Product</span>
                                    <select name="product">
                                        @foreach ($analyticsReport['product_filters'] as $value => $label)
                                            <option value="{{ $value }}" @selected($analyticsReport['product_filter'] === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="premium-filter">
                                    <span class="premium-filter-label">Batch</span>
                                    <select name="batch">
                                        @foreach ($analyticsReport['batch_filters'] as $value => $label)
                                            <option value="{{ $value }}" @selected($analyticsReport['batch_filter'] === (string) $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <button class="analytics-filter-submit" type="submit" title="Apply filters" aria-label="Apply analytics filters">
                                    <svg width="15" height="15"><use href="#i-filter"></use></svg>
                                </button>
                            </div>
                            @if ($analyticsActiveFilters->isNotEmpty())
                                <div class="analytics-active-chips">
                                    @foreach ($analyticsActiveFilters as $key => $filter)
                                        <a class="analytics-chip" href="{{ route('active8.page', array_merge(['page' => 'scan-analytics'], $analyticsQueryParams, [$key => 'all'])) }}">
                                            {{ $filter['label'] }} <span>{{ $filter['options'][$filter['value']] ?? $filter['value'] }}</span> &times;
                                        </a>
                                    @endforeach
                                    <a class="analytics-chip clear" href="{{ route('active8.page', array_merge(['page' => 'scan-analytics'], Illuminate\Support\Arr::only($analyticsQueryParams, ['range', 'start_date', 'end_date']))) }}">Clear filters</a>
                                </div>
                            @endif
                        </form>
                    </div>

                    <div class="company-detail-grid">
                        <div class="company-detail-card">
                            <div class="company-detail-label">Total Scans</div>
                            <div class="company-detail-value">{{ number_format($analyticsReport['total']) }}</div>
                            <div style="margin-top:8px"><span class="delta-pill {{ $analyticsReport['total_delta'] < 0 ? 'down' : '' }}">{{ $analyticsReport['total_delta'] >= 0 ? '+' : '' }}{{ $analyticsReport['total_delta'] }}%</span></div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Genuine Rate</div>
                            <div class="company-detail-value">{{ $analyticsReport['genuine_rate'] }}%</div>
                            <div class="company-sub-name">{{ number_format($analyticsReport['genuine']) }} genuine scans</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Flagged Rate</div>
                            <div class="company-detail-value">{{ $analyticsReport['flagged_rate'] }}%</div>
                            <div class="company-sub-name">{{ number_format($analyticsReport['flagged']) }} flagged scans</div>
                        </div>
                        <div class="company-detail-card">
                            <div class="company-detail-label">Unique Tokens</div>
                            <div class="company-detail-value">{{ number_format($analyticsReport['unique_tokens']) }}</div>
                            <div class="company-sub-name">{{ number_format($analyticsReport['repeat_scans']) }} repeat scans</div>
                        </div>
                    </div>

                    <div class="insight-grid">
                        <div class="chart-card">
                            <div class="line-chart-canvas-area">
                                <div class="chart-y-axis">
                                    @foreach ($analyticsReport['chart_y_labels'] as $tick)
                                        <span class="chart-y-label" style="top: {{ ($tick['y'] / 260) * 100 }}%">{{ number_format($tick['value']) }}</span>
                                    @endforeach
                                </div>
                                <svg class="chart-svg" viewBox="0 0 900 260" preserveAspectRatio="none">
                                    @foreach ($analyticsReport['chart_y_labels'] as $tick)
                                        <line x1="0" y1="{{ $tick['y'] }}" x2="900" y2="{{ $tick['y'] }}" stroke="#e5e7eb"/>
                                    @endforeach
                                    <path d="{{ $analyticsReport['chart_path'] }}" fill="none" stroke="#2563eb" stroke-width="2.5"/>
                                </svg>
                            </div>
                            <div id="a-x-labels" style="display:flex;padding:2px 20px 18px 68px;overflow:hidden;">
                                @foreach ($analyticsReport['x_labels'] as $label)
                                    <span style="flex:1;min-width:64px;font-size:11px;color:var(--gray-400)">{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                        <div class="insight-card">
                            <div class="insight-title">Current Readout</div>
                            <div class="insight-copy">
                                {{ number_format($analyticsReport['total']) }} verification scans were recorded in this period. Genuine scans account for {{ $analyticsReport['genuine_rate'] }}%, while flagged scans account for {{ $analyticsReport['flagged_rate'] }}%.
                            </div>
                            <div style="display:grid;gap:10px;margin-top:16px">
                                <div class="activity-meta-row"><span>Previous period</span><strong>{{ number_format($analyticsReport['previous_total']) }} scans</strong></div>
                                <div class="activity-meta-row"><span>Repeat scans</span><strong>{{ number_format($analyticsReport['repeat_scans']) }}</strong></div>
                                <div class="activity-meta-row"><span>Top location</span><strong>{{ data_get($analyticsReport['top_locations']->first(), 'label', 'No location') }}</strong></div>
                                <div class="activity-meta-row"><span>Top product</span><strong>{{ data_get($analyticsReport['top_products']->first(), 'label', 'No product') }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <div class="analytics-panel-grid">
                        @foreach ([
                            ['title' => 'Top Locations', 'items' => $analyticsReport['top_locations'], 'empty' => 'No location data yet.'],
                            ['title' => 'Top Products', 'items' => $analyticsReport['top_products'], 'empty' => 'No product scans yet.'],
                            ['title' => 'Top Batches', 'items' => $analyticsReport['top_batches'], 'empty' => 'No batch scans yet.'],
                        ] as $panel)
                            <div class="mini-card">
                                <div class="mini-card-title">{{ $panel['title'] }}</div>
                                <div class="analytics-list">
                                    @forelse ($panel['items'] as $item)
                                        <div class="analytics-list-item">
                                            <div><div class="company-main-name">{{ $item['label'] }}</div><div class="company-sub-name">{{ $item['sub'] ?? '' }}</div></div>
                                            <span class="status-pill active">{{ number_format($item['count']) }}</span>
                                        </div>
                                    @empty
                                        <div class="company-sub-name">{{ $panel['empty'] }}</div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="analytics-panel-grid">
                        <div class="mini-card">
                            <div class="mini-card-title">Device Mix</div>
                            <div class="donut-wrap">
                                <div class="donut-legend"><div class="legend-dot"></div><span style="color:var(--gray-700)">Mobile: {{ number_format($analyticsReport['device_counts']['mobile'] ?? 0) }}</span></div>
                                <svg width="160" height="160" viewBox="0 0 200 200"><circle cx="100" cy="100" r="70" fill="none" stroke="#e5e7eb" stroke-width="44"/><circle cx="100" cy="100" r="70" fill="none" stroke="#2563eb" stroke-width="44" stroke-dasharray="{{ $analyticsReport['device_dash'] }}" transform="rotate(-90 100 100)"/><circle cx="100" cy="100" r="49" fill="white"/></svg>
                                <div style="display:flex;gap:12px;color:var(--gray-500);font-size:12px;flex-wrap:wrap">
                                    <span>Desktop {{ number_format($analyticsReport['device_counts']['desktop'] ?? 0) }}</span>
                                    <span>Tablet {{ number_format($analyticsReport['device_counts']['tablet'] ?? 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mini-card">
                            <div class="mini-card-title">Browsers</div>
                            <div class="analytics-list">
                                @forelse ($analyticsReport['top_browsers'] as $item)
                                    <div class="analytics-list-item"><div><div class="company-main-name">{{ $item['label'] }}</div><div class="company-sub-name">{{ $item['sub'] }}</div></div><span class="status-pill">{{ number_format($item['count']) }}</span></div>
                                @empty
                                    <div class="company-sub-name">No browser data yet.</div>
                                @endforelse
                            </div>
                        </div>
                        <div class="mini-card">
                            <div class="mini-card-title">Status Breakdown</div>
                            @php
                                $statusCounts = collect($analyticsReport['status_counts']);
                            @endphp
                            <div class="analytics-list">
                                @foreach ($statusCounts as $status => $count)
                                    <div class="analytics-list-item"><span class="status-pill {{ $status }}">{{ $status }}</span><strong>{{ number_format($count) }}</strong></div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="admin-card suspicious-table-wrap">
                        <div class="admin-card-header">
                            <div>
                                <div class="admin-card-title">Suspicious Activity</div>
                                <div class="admin-card-sub">Invalid, suspicious, revoked, expired, and recalled scan attempts in the selected period.</div>
                            </div>
                            <a class="view-more" href="{{ route('active8.page', ['page' => 'scan-logs', 'status' => 'suspicious']) }}">View logs ›</a>
                        </div>
                        <div class="admin-table-wrap">
                            <table class="admin-table" data-auto-paginate data-auto-paginate-label="suspicious scans" data-auto-paginate-per-page="8">
                                <thead><tr><th>Status</th><th>Batch / Token</th><th>Product</th><th>Location</th><th>Device</th><th>Time</th></tr></thead>
                                <tbody>
                                    @forelse ($analyticsReport['suspicious_scans'] as $scan)
                                        <tr>
                                            <td><span class="status-pill {{ $scan['status'] }}">{{ $scan['status'] }}</span></td>
                                            <td><div class="company-main-name">{{ $scan['batch'] }}</div><div class="company-sub-name">{{ $scan['token'] }}</div></td>
                                            <td>{{ $scan['product'] }}</td>
                                            <td>{{ $scan['location'] }}</td>
                                            <td>{{ $scan['device'] }}</td>
                                            <td>{{ $scan['time'] }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6">No suspicious scans in this period.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
