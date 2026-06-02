@php
    $activitySummary = $activitySummary ?? ['total' => 0, 'today' => 0, 'qr_events' => 0, 'actors' => 0];
    $activityEventBreakdown = $activityEventBreakdown ?? collect();
    $topEventTotal = max((int) ($activityEventBreakdown->max('total') ?? 0), 1);
    $selectedCompanyName = $activityFilters['company'] !== 'all'
        ? $companies->firstWhere('id', (int) $activityFilters['company'])?->name
        : 'All companies';
    $selectedBatchName = $activityFilters['batch'] !== 'all'
        ? $batches->firstWhere('id', (int) $activityFilters['batch'])?->batch_number
        : 'All batches';
    $selectedEventName = $activityFilters['event'] !== 'all'
        ? Str::headline(str_replace('.', ' ', $activityFilters['event']))
        : 'All events';
@endphp

<section class="screen {{ $activePage === 'audit-trail' ? 'active' : '' }}" id="screen-audit-trail">
    <div class="topbar center">
        <div>
            <div class="page-title">Audit Trail</div>
            <div class="page-subtitle">Investigate admin actions, QR lifecycle changes, and batch-level operations.</div>
        </div>
        <div class="topbar-actions">
            <a class="btn-learn" href="{{ route('reports.verification.export', request()->only(['status', 'company', 'batch'])) }}">
                <svg width="14" height="14"><use href="#i-download"></use></svg>
                Export Verification CSV
            </a>
            <a class="btn-share" href="{{ route('active8.page', 'scan-logs') }}">
                <svg width="14" height="14"><use href="#i-eye"></use></svg>
                Scan Logs
            </a>
        </div>
    </div>

    <div class="audit-page-scroll">
        <div class="audit-hero">
            <div class="audit-panel soft">
                <div class="audit-hero-copy">
                    <div class="audit-eyebrow">Investigation Console</div>
                    <div class="audit-title">A clean command center for every sensitive change.</div>
                    <div class="audit-copy">
                        Follow the story behind each admin action, QR lifecycle update, and production change with enough
                        context to investigate quickly and confidently.
                    </div>
                    <div class="audit-selected" aria-label="Current audit trail filters">
                        <span>{{ $selectedCompanyName }}</span>
                        <span>{{ $selectedBatchName }}</span>
                        <span>{{ $selectedEventName }}</span>
                    </div>
                    <div class="audit-kpis">
                        <div class="audit-kpi"><span>Matching Events</span><strong>{{ number_format($activitySummary['total']) }}</strong></div>
                        <div class="audit-kpi"><span>Today</span><strong>{{ number_format($activitySummary['today']) }}</strong></div>
                        <div class="audit-kpi"><span>QR Events</span><strong>{{ number_format($activitySummary['qr_events']) }}</strong></div>
                        <div class="audit-kpi"><span>Actors</span><strong>{{ number_format($activitySummary['actors']) }}</strong></div>
                    </div>
                </div>
            </div>

            <form class="audit-panel audit-filter-card" method="GET" action="{{ route('active8.page', 'audit-trail') }}">
                <div class="audit-filter-title">Filter Timeline</div>
                <div class="audit-filter-copy">Narrow the trail by company, batch, or event type.</div>
                <div class="audit-filter-grid">
                    <label>
                        Company
                        <select class="font-select" name="activity_company" aria-label="Filter audit by company">
                            <option value="all">All companies</option>
                            @foreach ($companies as $item)
                                <option value="{{ $item->id }}" @selected((string) $activityFilters['company'] === (string) $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Batch
                        <select class="font-select" name="activity_batch" aria-label="Filter audit by batch">
                            <option value="all">All batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected((string) $activityFilters['batch'] === (string) $batch->id)>{{ $batch->batch_number }} · {{ $batch->product?->company?->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Event
                        <select class="font-select" name="activity_event" aria-label="Filter audit by event">
                            <option value="all">All events</option>
                            @foreach ($activityEvents as $event)
                                <option value="{{ $event }}" @selected($activityFilters['event'] === $event)>{{ Str::headline(str_replace('.', ' ', $event)) }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="audit-filter-actions">
                    <button class="btn-share" type="submit">Apply Filters</button>
                    <a class="btn-learn" href="{{ route('active8.page', 'audit-trail') }}">Reset</a>
                </div>
            </form>
        </div>

        <div class="audit-layout">
            <div class="audit-panel">
                <div class="admin-card-header audit-card-header">
                    <div>
                        <div class="admin-card-title">Operational Timeline</div>
                        <div class="admin-card-sub">
                            {{ $selectedCompanyName }} · {{ $selectedBatchName }} · {{ $selectedEventName }}
                        </div>
                    </div>
                    <span class="health-pill info">
                        @if ($recentActivityLogs instanceof \Illuminate\Contracts\Pagination\Paginator)
                            {{ number_format($recentActivityLogs->firstItem() ?? 0) }}-{{ number_format($recentActivityLogs->lastItem() ?? 0) }} shown
                        @else
                            {{ number_format($recentActivityLogs->count()) }} shown
                        @endif
                    </span>
                </div>
                @include('partials.admin-audit-list', [
                    'logs' => $recentActivityLogs,
                    'emptyTitle' => 'No audit events match those filters',
                    'emptyCopy' => 'Try another company, batch, or event type.',
                ])
                @if ($recentActivityLogs instanceof \Illuminate\Contracts\Pagination\Paginator && $recentActivityLogs->hasPages())
                    <div class="audit-pagination">
                        <div class="audit-pagination-meta">
                            Showing {{ number_format($recentActivityLogs->firstItem() ?? 0) }} to {{ number_format($recentActivityLogs->lastItem() ?? 0) }}
                            of {{ number_format($recentActivityLogs->total()) }} audit events
                        </div>
                        <div class="audit-pagination-actions">
                            @if ($recentActivityLogs->onFirstPage())
                                <span class="pager-btn disabled">‹</span>
                            @else
                                <a class="pager-btn" href="{{ $recentActivityLogs->previousPageUrl() }}" aria-label="Previous audit events">‹</a>
                            @endif

                            <span class="audit-page-count">Page {{ number_format($recentActivityLogs->currentPage()) }} of {{ number_format($recentActivityLogs->lastPage()) }}</span>

                            @if ($recentActivityLogs->hasMorePages())
                                <a class="pager-btn" href="{{ $recentActivityLogs->nextPageUrl() }}" aria-label="Next audit events">›</a>
                            @else
                                <span class="pager-btn disabled">›</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <aside class="audit-sidebar">
                <div class="audit-note-card">
                    <div class="audit-note-title">Investigation mode</div>
                    <div class="audit-note-copy">
                        Start with revoked or expiry-related QR events, then compare the actor, batch, and scan logs before
                        exporting evidence.
                    </div>
                </div>

                <div class="audit-panel">
                    <div class="admin-card-header audit-card-header">
                        <div>
                            <div class="admin-card-title">Event Mix</div>
                            <div class="admin-card-sub">Most common activity in this view.</div>
                        </div>
                    </div>
                    <div class="audit-breakdown">
                        @forelse ($activityEventBreakdown as $event)
                            @php
                                $percent = round(((int) $event->total / $topEventTotal) * 100);
                            @endphp
                            <div class="audit-breakdown-row">
                                <div class="audit-breakdown-head">
                                    <span>{{ Str::headline(str_replace('.', ' ', $event->event)) }}</span>
                                    <strong>{{ number_format($event->total) }}</strong>
                                </div>
                                <div class="audit-bar"><span style="width:{{ $percent }}%"></span></div>
                            </div>
                        @empty
                            <div class="company-sub-name">No event mix available for these filters.</div>
                        @endforelse
                    </div>
                </div>

                <div class="audit-panel">
                    <div class="admin-card-header audit-card-header">
                        <div>
                            <div class="admin-card-title">Review Checklist</div>
                            <div class="admin-card-sub">Useful when investigating a flagged batch.</div>
                        </div>
                    </div>
                    <div class="activity-meta audit-checklist">
                        <div class="activity-meta-row"><span>Check actor</span><strong>Confirm owner/admin</strong></div>
                        <div class="activity-meta-row"><span>Check QR state</span><strong>Active/revoked/expired</strong></div>
                        <div class="activity-meta-row"><span>Compare scans</span><strong>Open scan logs</strong></div>
                        <div class="activity-meta-row"><span>Escalate</span><strong>Export CSV if needed</strong></div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
