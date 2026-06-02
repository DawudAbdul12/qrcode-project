<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Company;
use App\Models\Product;
use App\Models\ScanLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ScanAnalytics
{
    /**
     * @return array<string, mixed>
     */
    public function report(
        string|int $range = '30',
        string $status = 'all',
        string $device = 'all',
        ?string $startDate = null,
        ?string $endDate = null,
        string|int|null $company = 'all',
        string|int|null $product = 'all',
        string|int|null $batch = 'all',
    ): array
    {
        $period = $this->periodFor((string) $range, $startDate, $endDate);
        $start = $period['start'];
        $end = $period['end'];
        $days = (int) $start->diffInDays($end) + 1;
        $status = array_key_exists($status, $this->statusFilters()) ? $status : 'all';
        $device = array_key_exists($device, $this->deviceFilters()) ? $device : 'all';
        $company = $this->validEntityFilter($company, Company::class);
        $product = $this->validEntityFilter($product, Product::class);
        $batch = $this->validEntityFilter($batch, Batch::class);

        $query = ScanLog::query()
            ->with('qrCode.batch.product.company')
            ->whereBetween('scanned_at', [$start, $end]);

        $this->applyFilters($query, $status, $device, $company, $product, $batch);

        $logs = $query->get();
        $previousStart = $start->copy()->subDays($days);
        $previousEnd = $start->copy()->subSecond();
        $previousQuery = ScanLog::query()
            ->whereBetween('scanned_at', [$previousStart, $previousEnd]);

        $this->applyFilters($previousQuery, $status, $device, $company, $product, $batch);

        $previousLogs = $previousQuery->get();

        $dailyCounts = collect(range(0, $days - 1))
            ->mapWithKeys(fn (int $offset) => [
                $start->copy()->addDays($offset)->toDateString() => 0,
            ]);

        $logs
            ->groupBy(fn (ScanLog $scan) => $scan->scanned_at?->toDateString())
            ->each(function (Collection $items, ?string $date) use (&$dailyCounts): void {
                if ($date && $dailyCounts->has($date)) {
                    $dailyCounts[$date] = $items->count();
                }
            });

        $statusCounts = $this->countsFor($logs, 'status_result', ['genuine', 'invalid', 'suspicious', 'revoked', 'expired', 'recalled']);
        $deviceCounts = $this->countsFor($logs, 'device_type', ['mobile', 'desktop', 'tablet']);
        $browserCounts = $this->countsFor($logs, 'browser', ['Chrome', 'Safari', 'Firefox', 'Edge', 'Unknown']);

        return [
            'range' => $period['key'],
            'label' => $period['label'],
            'ranges' => $this->ranges(),
            'status_filter' => $status,
            'status_filters' => $this->statusFilters(),
            'device_filter' => $device,
            'device_filters' => $this->deviceFilters(),
            'company_filter' => $company,
            'product_filter' => $product,
            'batch_filter' => $batch,
            'company_filters' => $this->companyFilters(),
            'product_filters' => $this->productFilters(),
            'batch_filters' => $this->batchFilters($company, $product),
            'period_start' => $start,
            'period_end' => $end,
            'period_display' => $start->format('M j, Y').' - '.$end->format('M j, Y'),
            'calendar_months' => $this->calendarMonths($start, $end),
            'days' => $days,
            'total' => $logs->count(),
            'previous_total' => $previousLogs->count(),
            'total_delta' => $this->percentChange($logs->count(), $previousLogs->count()),
            'genuine' => $statusCounts['genuine'] ?? 0,
            'flagged' => collect($statusCounts)->except('genuine')->sum(),
            'genuine_rate' => $logs->count() > 0 ? round((($statusCounts['genuine'] ?? 0) / $logs->count()) * 100, 1) : 0,
            'flagged_rate' => $logs->count() > 0 ? round((collect($statusCounts)->except('genuine')->sum() / $logs->count()) * 100, 1) : 0,
            'unique_tokens' => $logs->pluck('token_scanned')->filter()->unique()->count(),
            'repeat_scans' => $logs->filter(fn (ScanLog $scan) => ($scan->metadata['is_repeat_scan'] ?? false) === true)->count(),
            'daily_counts' => $dailyCounts,
            'chart_path' => $this->linePath($dailyCounts->values()),
            'chart_y_labels' => $this->yLabels($dailyCounts->values()),
            'x_labels' => $this->xLabels($dailyCounts->keys()),
            'status_counts' => $statusCounts,
            'device_counts' => $deviceCounts,
            'browser_counts' => $browserCounts,
            'device_dash' => $this->donutDash($deviceCounts),
            'top_batches' => $this->topBatches($logs),
            'top_products' => $this->topProducts($logs),
            'top_locations' => $this->topLocations($logs),
            'top_browsers' => $this->topBrowsers($logs),
            'suspicious_scans' => $this->suspiciousScans($logs),
        ];
    }

    private function validEntityFilter(string|int|null $value, string $model): string
    {
        if ($value === null || $value === '' || $value === 'all') {
            return 'all';
        }

        $id = (int) $value;

        return $id > 0 && $model::query()->whereKey($id)->exists() ? (string) $id : 'all';
    }

    private function applyFilters($query, string $status, string $device, string $company, string $product, string $batch): void
    {
        if ($status !== 'all') {
            $query->where('status_result', $status);
        }

        if ($device !== 'all') {
            $query->where('device_type', $device);
        }

        if ($batch !== 'all') {
            $query->whereHas('qrCode.batch', fn ($batchQuery) => $batchQuery->whereKey($batch));

            return;
        }

        if ($product !== 'all') {
            $query->whereHas('qrCode.batch.product', fn ($productQuery) => $productQuery->whereKey($product));
        }

        if ($company !== 'all') {
            $query->whereHas('qrCode.batch.product.company', fn ($companyQuery) => $companyQuery->whereKey($company));
        }
    }

    /**
     * @return array<string, string>
     */
    public function statusFilters(): array
    {
        return [
            'all' => 'All statuses',
            'genuine' => 'Genuine',
            'invalid' => 'Invalid',
            'suspicious' => 'Suspicious',
            'revoked' => 'Revoked',
            'expired' => 'Expired',
            'recalled' => 'Recalled',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function deviceFilters(): array
    {
        return [
            'all' => 'All devices',
            'mobile' => 'Mobile',
            'desktop' => 'Desktop',
            'tablet' => 'Tablet',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function companyFilters(): array
    {
        return Company::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(fn (string $name, int $id) => [(string) $id => $name])
            ->prepend('All companies', 'all')
            ->toArray();
    }

    /**
     * @return array<string, string>
     */
    public function productFilters(): array
    {
        return Product::query()
            ->with('company')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Product $product) => [
                (string) $product->id => $product->name.($product->company?->name ? ' · '.$product->company->name : ''),
            ])
            ->prepend('All products', 'all')
            ->toArray();
    }

    /**
     * @return array<string, string>
     */
    public function batchFilters(string $company = 'all', string $product = 'all'): array
    {
        return Batch::query()
            ->with('product.company')
            ->when($product !== 'all', fn ($query) => $query->where('product_id', $product))
            ->when($company !== 'all', fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('company_id', $company)))
            ->orderBy('batch_number')
            ->get()
            ->mapWithKeys(fn (Batch $batch) => [
                (string) $batch->id => $batch->batch_number.($batch->product?->name ? ' · '.$batch->product->name : ''),
            ])
            ->prepend('All batches', 'all')
            ->toArray();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function ranges(): array
    {
        return [
            '30' => ['label' => 'Last 30 days'],
            '90' => ['label' => 'Last 90 days'],
            '365' => ['label' => 'Last 365 days'],
            'last_year' => ['label' => 'Last Year'],
            'this_year' => ['label' => 'This year'],
        ];
    }

    /**
     * @return array{key: string, label: string, start: Carbon, end: Carbon}
     */
    private function periodFor(string $range, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($range === 'custom' && $startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [
                'key' => 'custom',
                'label' => $start->format('M j, Y').' - '.$end->format('M j, Y'),
                'start' => $start,
                'end' => $end,
            ];
        }

        $key = array_key_exists($range, $this->ranges()) ? $range : '30';
        $now = now();

        return match ($key) {
            '90' => [
                'key' => $key,
                'label' => $this->ranges()[$key]['label'],
                'start' => $now->copy()->subDays(89)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            '365' => [
                'key' => $key,
                'label' => $this->ranges()[$key]['label'],
                'start' => $now->copy()->subDays(364)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
            'last_year' => [
                'key' => $key,
                'label' => $this->ranges()[$key]['label'],
                'start' => $now->copy()->subYear()->startOfYear(),
                'end' => $now->copy()->subYear()->endOfYear(),
            ],
            'this_year' => [
                'key' => $key,
                'label' => $this->ranges()[$key]['label'],
                'start' => $now->copy()->startOfYear(),
                'end' => $now->copy()->endOfDay(),
            ],
            default => [
                'key' => '30',
                'label' => $this->ranges()['30']['label'],
                'start' => $now->copy()->subDays(29)->startOfDay(),
                'end' => $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * @param array<int, string> $allowed
     * @return array<string, int>
     */
    private function countsFor(Collection $logs, string $field, array $allowed): array
    {
        $counts = collect($allowed)->mapWithKeys(fn (string $key) => [$key => 0]);

        $logs
            ->groupBy(fn (ScanLog $scan) => $scan->{$field} ?: 'Unknown')
            ->each(function (Collection $items, string $key) use (&$counts): void {
                $counts[$key] = $items->count();
            });

        return $counts->toArray();
    }

    private function linePath(Collection $counts): string
    {
        $max = max(1, $counts->max() ?? 0);
        $last = max(1, $counts->count() - 1);
        $points = $counts->values()->map(function (int $count, int $index) use ($max, $last): string {
            $x = round(($index / $last) * 900, 2);
            $y = round(240 - (($count / $max) * 220), 2);

            return "{$x},{$y}";
        });

        return 'M'.$points->implode(' L');
    }

    private function yLabels(Collection $counts): array
    {
        $max = max(1, (int) ($counts->max() ?? 0));
        $values = $max <= 3
            ? range($max, 0)
            : [$max, (int) ceil($max * 0.66), (int) ceil($max * 0.33), 0];

        return collect($values)
            ->unique()
            ->values()
            ->map(fn (int $value) => [
                'value' => $value,
                'y' => round(240 - (($value / $max) * 220), 2),
            ])
            ->toArray();
    }

    private function xLabels(Collection $dates): array
    {
        $step = max(1, (int) floor($dates->count() / 7));

        return $dates
            ->values()
            ->filter(fn (string $date, int $index) => $index % $step === 0 || $index === $dates->count() - 1)
            ->map(fn (string $date) => Carbon::parse($date)->format('M j'))
            ->values()
            ->toArray();
    }

    private function calendarMonths(Carbon $start, Carbon $end): Collection
    {
        $firstMonth = $start->copy()->startOfMonth();
        $secondMonth = $end->copy()->startOfMonth();

        if ($firstMonth->isSameMonth($secondMonth)) {
            $secondMonth = $firstMonth->copy()->addMonth()->startOfMonth();
        }

        return collect([$firstMonth, $secondMonth])
            ->unique(fn (Carbon $month) => $month->format('Y-m'))
            ->values()
            ->map(function (Carbon $month) use ($start, $end): array {
                $days = collect(range(1, $month->daysInMonth))->map(function (int $day) use ($month, $start, $end): array {
                    $date = $month->copy()->day($day);
                    $class = '';

                    if ($date->isSameDay($start)) {
                        $class = 'range-start';
                    } elseif ($date->isSameDay($end)) {
                        $class = 'range-end';
                    } elseif ($date->betweenIncluded($start, $end)) {
                        $class = 'in-range';
                    }

                    return [
                        'day' => $day,
                        'date' => $date->toDateString(),
                        'class' => $class,
                    ];
                });

                return [
                    'title' => $month->format('F Y'),
                    'offset' => $month->dayOfWeek,
                    'days' => $days,
                ];
            });
    }

    /**
     * @param array<string, int> $deviceCounts
     */
    private function donutDash(array $deviceCounts): string
    {
        $total = max(1, array_sum($deviceCounts));
        $mobile = $deviceCounts['mobile'] ?? 0;
        $mobileArc = round(($mobile / $total) * 430);

        return $mobileArc.' '.max(1, 430 - $mobileArc);
    }

    private function topBatches(Collection $logs): Collection
    {
        return $logs
            ->filter(fn (ScanLog $scan) => $scan->qrCode?->batch)
            ->groupBy(fn (ScanLog $scan) => $scan->qrCode->batch->batch_number)
            ->map(fn (Collection $items, string $batch) => [
                'label' => $batch,
                'sub' => $items->first()->qrCode?->batch?->product?->name,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function topProducts(Collection $logs): Collection
    {
        return $logs
            ->filter(fn (ScanLog $scan) => $scan->qrCode?->batch?->product)
            ->groupBy(fn (ScanLog $scan) => $scan->qrCode->batch->product->name)
            ->map(fn (Collection $items, string $product) => [
                'label' => $product,
                'sub' => $items->first()->qrCode?->batch?->product?->company?->name,
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function topLocations(Collection $logs): Collection
    {
        return $logs
            ->groupBy(fn (ScanLog $scan) => collect([$scan->city, $scan->country])->filter()->implode(', ') ?: 'Unknown location')
            ->map(fn (Collection $items, string $location) => [
                'label' => $location,
                'sub' => $items->whereIn('status_result', ['invalid', 'suspicious', 'revoked', 'expired', 'recalled'])->count().' flagged',
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function topBrowsers(Collection $logs): Collection
    {
        return $logs
            ->groupBy(fn (ScanLog $scan) => $scan->browser ?: 'Unknown browser')
            ->map(fn (Collection $items, string $browser) => [
                'label' => $browser,
                'sub' => $items->pluck('device_type')->filter()->unique()->implode(', ') ?: 'Unknown device',
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function suspiciousScans(Collection $logs): Collection
    {
        return $logs
            ->filter(fn (ScanLog $scan) => in_array($scan->status_result, ['invalid', 'suspicious', 'revoked', 'expired', 'recalled'], true))
            ->sortByDesc('scanned_at')
            ->take(8)
            ->map(fn (ScanLog $scan) => [
                'id' => $scan->id,
                'status' => $scan->status_result,
                'batch' => $scan->qrCode?->batch?->batch_number ?? 'Unknown batch',
                'product' => $scan->qrCode?->batch?->product?->name ?? 'Unknown product',
                'token' => $scan->token_scanned ?? 'No token',
                'location' => collect([$scan->city, $scan->country])->filter()->implode(', ') ?: 'Unknown location',
                'device' => ucfirst($scan->device_type ?? 'unknown'),
                'time' => $scan->scanned_at?->diffForHumans() ?? 'Unknown time',
            ])
            ->values();
    }

    private function percentChange(int $current, int $previous): float
    {
        if ($previous === 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
