<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Company;
use App\Models\ActivityLog;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\ScanLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CompanyDashboardController extends Controller
{
    private const PER_PAGE_OPTIONS = [15, 25, 50, 100];
    private const FLAGGED_STATUSES = ['invalid', 'suspicious', 'revoked', 'expired', 'recalled'];

    public function index(Request $request): View
    {
        $company = $request->user()->company;

        $scanLogQuery = $this->scanLogQuery($company);
        $chartRange = $this->chartRange($request);

        return view('company.dashboard', [
            'company' => $company,
            'stats' => [
                'products' => Product::query()->where('company_id', $company->id)->count(),
                'active_batches' => Batch::query()
                    ->whereHas('product', fn ($query) => $query->where('company_id', $company->id))
                    ->whereIn('status', ['approved', 'printed', 'distributed'])
                    ->count(),
                'label_orders' => LabelOrder::query()->where('company_id', $company->id)->count(),
                'qr_scans' => (clone $scanLogQuery)->whereNotNull('qr_code_id')->count(),
                'ussd_checks' => (clone $scanLogQuery)->whereNotNull('label_code_id')->count(),
                'genuine' => (clone $scanLogQuery)->where('status_result', 'genuine')->count(),
                'flagged' => (clone $scanLogQuery)->whereIn('status_result', self::FLAGGED_STATUSES)->count(),
            ],
            'recentScans' => (clone $scanLogQuery)
                ->with(['qrCode.batch.product', 'labelCode.batch.product'])
                ->latest('scanned_at')
                ->take(10)
                ->get(),
            'suspiciousScans' => (clone $scanLogQuery)
                ->with(['qrCode.batch.product', 'labelCode.batch.product'])
                ->whereIn('status_result', self::FLAGGED_STATUSES)
                ->latest('scanned_at')
                ->take(6)
                ->get(),
            'chartData' => $this->dashboardChartData($company, $chartRange['start'], $chartRange['end']),
            'chartFilters' => $chartRange,
            'topProducts' => $this->topCheckedProducts($company),
            'recentOrders' => LabelOrder::query()
                ->with(['batch.product'])
                ->withCount('labelCodes')
                ->where('company_id', $company->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    public function products(Request $request): View
    {
        $company = $request->user()->company;
        $search = $this->searchTerm($request);
        $status = $this->selectedOption($request, 'status', ['all', 'draft', 'active', 'discontinued', 'recalled']);
        $category = $request->query('category', 'all');
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'name', 'category', 'status', 'batches']);
        $direction = $this->direction($request);

        $products = Product::query()
            ->with('productCategory')
            ->withCount('batches')
            ->where('company_id', $company->id)
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('sku', 'like', '%'.$search.'%')
                    ->orWhere('category', 'like', '%'.$search.'%')
                    ->orWhere('manufacturer', 'like', '%'.$search.'%')
                    ->orWhere('country_of_origin', 'like', '%'.$search.'%')
                    ->orWhereHas('productCategory', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($category !== 'all', fn ($query) => $query->where(function ($builder) use ($category): void {
                $builder
                    ->where('category', $category)
                    ->orWhereHas('productCategory', fn ($categoryQuery) => $categoryQuery->where('name', $category));
            }))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name', $direction))
            ->when($sort === 'category', fn ($query) => $query->orderBy('category', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status', $direction))
            ->when($sort === 'batches', fn ($query) => $query->orderBy('batches_count', $direction))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Product $product) => [
                'href' => route('company.products.show', $product),
                'detailTitle' => $product->name,
                'detailSubtitle' => 'Product Details',
                'details' => $this->productDetails($product),
                'cells' => [
                    $product->name,
                    $product->sku ?? 'No SKU',
                    $product->productCategory?->name ?? $product->category ?? 'Uncategorized',
                    number_format($product->batches_count),
                    $product->status,
                ],
            ]);

        return $this->resourceView($request, 'Products', 'Products registered under '.$company->name.'.', [
            'headers' => [
                ['label' => 'Product', 'sort' => 'name'],
                ['label' => 'SKU'],
                ['label' => 'Category', 'sort' => 'category'],
                ['label' => 'Batches', 'sort' => 'batches'],
                ['label' => 'Status', 'sort' => 'status'],
            ],
            'rows' => $products,
            'empty' => 'No products have been registered for this company yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search product, SKU, category, origin'],
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'draft' => 'Draft', 'active' => 'Active', 'discontinued' => 'Discontinued', 'recalled' => 'Recalled'])],
                ['name' => 'category', 'label' => 'Category', 'value' => $category, 'options' => $this->optionList($this->companyProductCategories($company))],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'products'], $request->query())),
        ]);
    }

    public function product(Request $request, Product $product): View
    {
        return $this->detailView($request, 'Product Details', $product->name, $this->productDetails($product));
    }

    public function batches(Request $request): View
    {
        $company = $request->user()->company;
        $search = $this->searchTerm($request);
        $qa = $this->selectedOption($request, 'qa', ['all', 'pending', 'approved', 'failed', 'review']);
        $status = $this->selectedOption($request, 'status', ['all', 'draft', 'approved', 'printed', 'distributed', 'recalled']);
        $qr = $this->selectedOption($request, 'qr', ['all', 'active', 'expired', 'revoked', 'missing']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'batch', 'product', 'quantity', 'qa', 'status']);
        $direction = $this->direction($request);

        $batches = Batch::query()
            ->with(['product', 'qrCode'])
            ->whereHas('product', fn ($query) => $query->where('company_id', $company->id))
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('batch_number', 'like', '%'.$search.'%')
                    ->orWhere('production_location', 'like', '%'.$search.'%')
                    ->orWhereHas('product', fn ($productQuery) => $productQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%'))
                    ->orWhereHas('qrCode', fn ($qrQuery) => $qrQuery->where('token', 'like', '%'.$search.'%'));
            }))
            ->when($qa !== 'all', fn ($query) => $query->where('qa_status', $qa))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($qr === 'missing', fn ($query) => $query->doesntHave('qrCode'))
            ->when(in_array($qr, ['active', 'expired', 'revoked'], true), fn ($query) => $query->whereHas('qrCode', fn ($qrQuery) => $qrQuery->where('status', $qr)))
            ->when($sort === 'batch', fn ($query) => $query->orderBy('batch_number', $direction))
            ->when($sort === 'product', fn ($query) => $query->orderBy(Product::query()->select('name')->whereColumn('products.id', 'batches.product_id'), $direction))
            ->when($sort === 'quantity', fn ($query) => $query->orderBy('quantity', $direction))
            ->when($sort === 'qa', fn ($query) => $query->orderBy('qa_status', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status', $direction))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Batch $batch) => [
                'href' => route('company.batches.show', $batch),
                'detailTitle' => $batch->batch_number,
                'detailSubtitle' => 'Batch Details',
                'details' => $this->batchDetails($batch),
                'cells' => [
                    $batch->batch_number,
                    $batch->product?->name ?? 'No product',
                    number_format($batch->quantity),
                    $batch->qa_status,
                    $batch->qrCode?->status ?? 'No QR',
                ],
            ]);

        return $this->resourceView($request, 'Batches', 'Production batches and QR status for '.$company->name.'.', [
            'headers' => [
                ['label' => 'Batch', 'sort' => 'batch'],
                ['label' => 'Product', 'sort' => 'product'],
                ['label' => 'Quantity', 'sort' => 'quantity'],
                ['label' => 'QA', 'sort' => 'qa'],
                ['label' => 'QR'],
            ],
            'rows' => $batches,
            'empty' => 'No batches have been registered for this company yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search batch, product, SKU, QR token'],
            'filters' => [
                ['name' => 'qa', 'label' => 'QA', 'value' => $qa, 'options' => $this->optionList(['all' => 'All QA', 'pending' => 'Pending', 'approved' => 'Approved', 'failed' => 'Failed', 'review' => 'Review'])],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'draft' => 'Draft', 'approved' => 'Approved', 'printed' => 'Printed', 'distributed' => 'Distributed', 'recalled' => 'Recalled'])],
                ['name' => 'qr', 'label' => 'QR', 'value' => $qr, 'options' => $this->optionList(['all' => 'All QR', 'active' => 'Active', 'expired' => 'Expired', 'revoked' => 'Revoked', 'missing' => 'Missing'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'batches'], $request->query())),
        ]);
    }

    public function batch(Request $request, Batch $batch): View
    {
        return $this->detailView($request, 'Batch Details', $batch->batch_number, $this->batchDetails($batch));
    }

    public function labelOrders(Request $request): View
    {
        $company = $request->user()->company;
        $search = $this->searchTerm($request);
        $type = $this->selectedOption($request, 'type', ['all', 'hologram', 'tamper-proof', 'security seal', 'combo']);
        $status = $this->selectedOption($request, 'status', ['all', 'pending', 'approved', 'printed', 'shipped', 'delivered', 'cancelled']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'order', 'product', 'quantity', 'codes', 'status']);
        $direction = $this->direction($request);

        $orders = LabelOrder::query()
            ->with(['batch.product'])
            ->withCount('labelCodes')
            ->where('company_id', $company->id)
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('printer_vendor', 'like', '%'.$search.'%')
                    ->orWhere('label_type', 'like', '%'.$search.'%')
                    ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($type !== 'all', fn ($query) => $query->where('label_type', $type))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($sort === 'order', fn ($query) => $query->orderBy('id', $direction))
            ->when($sort === 'product', fn ($query) => $query->orderBy(Batch::query()->select('batch_number')->whereColumn('batches.id', 'label_orders.batch_id'), $direction))
            ->when($sort === 'quantity', fn ($query) => $query->orderBy('print_quantity', $direction))
            ->when($sort === 'codes', fn ($query) => $query->orderBy('label_codes_count', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status', $direction))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (LabelOrder $order) => [
                'href' => route('company.label-orders.show', $order),
                'detailTitle' => 'Order #'.$order->id,
                'detailSubtitle' => 'Label Order Details',
                'details' => $this->labelOrderDetails($order),
                'cells' => [
                    '#'.$order->id.' · '.$order->label_type,
                    $order->batch?->product?->name ?? 'General order',
                    number_format($order->print_quantity),
                    number_format($order->label_codes_count),
                    $order->status,
                ],
            ]);

        return $this->resourceView($request, 'Label Orders', 'Print jobs and code generation for '.$company->name.'.', [
            'headers' => [
                ['label' => 'Order', 'sort' => 'order'],
                ['label' => 'Product', 'sort' => 'product'],
                ['label' => 'Quantity', 'sort' => 'quantity'],
                ['label' => 'USSD Codes', 'sort' => 'codes'],
                ['label' => 'Status', 'sort' => 'status'],
            ],
            'rows' => $orders,
            'empty' => 'No label orders have been created for this company yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search order, product, batch, vendor'],
            'filters' => [
                ['name' => 'type', 'label' => 'Type', 'value' => $type, 'options' => $this->optionList(['all' => 'All types', 'hologram' => 'Hologram', 'tamper-proof' => 'Tamper-proof', 'security seal' => 'Security seal', 'combo' => 'Combo'])],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'pending' => 'Pending', 'approved' => 'Approved', 'printed' => 'Printed', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'label-orders'], $request->query())),
        ]);
    }

    public function labelOrder(Request $request, LabelOrder $labelOrder): View
    {
        return $this->detailView($request, 'Label Order Details', '#'.$labelOrder->id.' · '.$labelOrder->label_type, $this->labelOrderDetails($labelOrder));
    }

    public function labelCodes(Request $request): View
    {
        $company = $request->user()->company;
        $search = $this->searchTerm($request);
        $status = $this->selectedOption($request, 'status', ['all', 'unused', 'used', 'suspicious', 'revoked', 'expired', 'recalled']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'code', 'product', 'order', 'checks', 'status']);
        $direction = $this->direction($request);

        $codes = LabelCode::query()
            ->with(['batch.product', 'labelOrder'])
            ->where('company_id', $company->id)
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('code', 'like', '%'.strtoupper($search).'%')
                    ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($sort === 'code', fn ($query) => $query->orderBy('code', $direction))
            ->when($sort === 'product', fn ($query) => $query->orderBy(Batch::query()->select('batch_number')->whereColumn('batches.id', 'label_codes.batch_id'), $direction))
            ->when($sort === 'order', fn ($query) => $query->orderBy('label_order_id', $direction))
            ->when($sort === 'checks', fn ($query) => $query->orderBy('verification_count', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status', $direction))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (LabelCode $labelCode) => [
                'href' => route('company.ussd-codes.show', $labelCode),
                'detailTitle' => $labelCode->formattedCode(),
                'detailSubtitle' => 'USSD Code Details',
                'details' => $this->labelCodeDetails($labelCode),
                'cells' => [
                    $labelCode->formattedCode(),
                    $labelCode->batch?->product?->name ?? 'General code',
                    $labelCode->label_order_id ? '#'.$labelCode->label_order_id : 'No order',
                    number_format($labelCode->verification_count),
                    $labelCode->status,
                ],
            ]);

        return $this->resourceView($request, 'USSD Codes', 'Scratch codes generated for '.$company->name.'.', [
            'headers' => [
                ['label' => 'Code', 'sort' => 'code'],
                ['label' => 'Product', 'sort' => 'product'],
                ['label' => 'Order', 'sort' => 'order'],
                ['label' => 'Checks', 'sort' => 'checks'],
                ['label' => 'Status', 'sort' => 'status'],
            ],
            'rows' => $codes,
            'empty' => 'No USSD codes have been generated for this company yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search code, product, batch'],
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'unused' => 'Unused', 'used' => 'Used', 'suspicious' => 'Suspicious', 'revoked' => 'Revoked', 'expired' => 'Expired', 'recalled' => 'Recalled'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'ussd-codes'], $request->query())),
        ]);
    }

    public function labelCode(Request $request, LabelCode $labelCode): View
    {
        return $this->detailView($request, 'USSD Code Details', $labelCode->formattedCode(), $this->labelCodeDetails($labelCode));
    }

    public function verifications(Request $request): View
    {
        $search = $this->searchTerm($request);
        $status = $this->selectedOption($request, 'status', ['all', 'genuine', 'invalid', 'suspicious', 'revoked', 'expired', 'recalled']);
        $channel = $this->selectedOption($request, 'channel', ['all', 'qr', 'ussd']);
        $device = $this->selectedOption($request, 'device', ['all', 'mobile', 'desktop', 'tablet', 'unknown']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'token', 'product', 'channel', 'location', 'status']);
        $direction = $this->direction($request);

        $logs = $this->scanLogQuery($request->user()->company)
            ->with(['qrCode.batch.product', 'labelCode.batch.product'])
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('token_scanned', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%')
                    ->orWhereHas('qrCode.batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('qrCode.batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('labelCode.batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($status !== 'all', fn ($query) => $query->where('status_result', $status))
            ->when($channel === 'qr', fn ($query) => $query->whereNotNull('qr_code_id'))
            ->when($channel === 'ussd', fn ($query) => $query->whereNotNull('label_code_id'))
            ->when($device !== 'all' && $device !== 'unknown', fn ($query) => $query->where('device_type', $device))
            ->when($device === 'unknown', fn ($query) => $query->whereNull('device_type'))
            ->when($sort === 'token', fn ($query) => $query->orderBy('token_scanned', $direction))
            ->when($sort === 'channel', fn ($query) => $query->orderBy('device_type', $direction))
            ->when($sort === 'location', fn ($query) => $query->orderBy('country', $direction)->orderBy('city', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status_result', $direction))
            ->when(in_array($sort, ['latest', 'product'], true), fn ($query) => $query->orderBy('scanned_at', $sort === 'latest' ? 'desc' : $direction))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (ScanLog $scanLog) {
                $productName = $scanLog->qrCode?->batch?->product?->name
                    ?? $scanLog->labelCode?->batch?->product?->name
                    ?? 'Unknown product';

                return [
                    'href' => route('company.verifications.show', $scanLog),
                    'detailTitle' => $scanLog->token_scanned ?? 'Verification #'.$scanLog->id,
                    'detailSubtitle' => 'Verification Details',
                    'details' => $this->scanLogDetails($scanLog),
                    'cells' => [
                        $scanLog->token_scanned ?? 'No token',
                        $productName,
                        strtoupper($scanLog->device_type ?? 'web'),
                        collect([$scanLog->city, $scanLog->country])->filter()->join(', ') ?: 'Unknown',
                        $scanLog->status_result,
                    ],
                ];
            });

        return $this->resourceView($request, 'Verification Logs', 'Recent QR and USSD checks for '.$request->user()->company->name.'.', [
            'headers' => [
                ['label' => 'Check', 'sort' => 'token'],
                ['label' => 'Product', 'sort' => 'product'],
                ['label' => 'Channel', 'sort' => 'channel'],
                ['label' => 'Location', 'sort' => 'location'],
                ['label' => 'Status', 'sort' => 'status'],
            ],
            'rows' => $logs,
            'empty' => 'No verification activity has been recorded for this company yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search token, product, batch, IP, location'],
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'genuine' => 'Genuine', 'invalid' => 'Invalid', 'suspicious' => 'Suspicious', 'revoked' => 'Revoked', 'expired' => 'Expired', 'recalled' => 'Recalled'])],
                ['name' => 'channel', 'label' => 'Channel', 'value' => $channel, 'options' => $this->optionList(['all' => 'All channels', 'qr' => 'QR', 'ussd' => 'USSD'])],
                ['name' => 'device', 'label' => 'Device', 'value' => $device, 'options' => $this->optionList(['all' => 'All devices', 'mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'unknown' => 'Unknown'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'verifications'], $request->query())),
        ]);
    }

    public function suspicious(Request $request): View
    {
        $search = $this->searchTerm($request);
        $status = $this->selectedOption($request, 'status', ['all', ...self::FLAGGED_STATUSES]);
        $device = $this->selectedOption($request, 'device', ['all', 'mobile', 'desktop', 'tablet', 'unknown']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'token', 'product', 'channel', 'when', 'status']);
        $direction = $this->direction($request);

        $logs = $this->scanLogQuery($request->user()->company)
            ->with(['qrCode.batch.product', 'labelCode.batch.product'])
            ->whereIn('status_result', ['invalid', 'suspicious', 'revoked', 'expired', 'recalled'])
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('token_scanned', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhere('city', 'like', '%'.$search.'%')
                    ->orWhere('country', 'like', '%'.$search.'%')
                    ->orWhereHas('qrCode.batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$search.'%'))
                    ->orWhereHas('qrCode.batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('labelCode.batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
            }))
            ->when($status !== 'all', fn ($query) => $query->where('status_result', $status))
            ->when($device !== 'all' && $device !== 'unknown', fn ($query) => $query->where('device_type', $device))
            ->when($device === 'unknown', fn ($query) => $query->whereNull('device_type'))
            ->when($sort === 'token', fn ($query) => $query->orderBy('token_scanned', $direction))
            ->when($sort === 'channel', fn ($query) => $query->orderBy('device_type', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status_result', $direction))
            ->when(in_array($sort, ['latest', 'when', 'product'], true), fn ($query) => $query->orderBy('scanned_at', $sort === 'latest' ? 'desc' : $direction))
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (ScanLog $scanLog) {
                $productName = $scanLog->qrCode?->batch?->product?->name
                    ?? $scanLog->labelCode?->batch?->product?->name
                    ?? 'Unknown product';

                return [
                    'href' => route('company.verifications.show', $scanLog),
                    'detailTitle' => $scanLog->token_scanned ?? 'Verification #'.$scanLog->id,
                    'detailSubtitle' => 'Verification Details',
                    'details' => $this->scanLogDetails($scanLog),
                    'cells' => [
                        $scanLog->token_scanned ?? 'No token',
                        $productName,
                        strtoupper($scanLog->device_type ?? 'web'),
                        $scanLog->scanned_at?->diffForHumans() ?? 'Not recorded',
                        $scanLog->status_result,
                    ],
                ];
            });

        return $this->resourceView($request, 'Suspicious Activity', 'Invalid, suspicious, revoked, expired, and recalled checks for '.$request->user()->company->name.'.', [
            'headers' => [
                ['label' => 'Check', 'sort' => 'token'],
                ['label' => 'Product', 'sort' => 'product'],
                ['label' => 'Channel', 'sort' => 'channel'],
                ['label' => 'When', 'sort' => 'when'],
                ['label' => 'Status', 'sort' => 'status'],
            ],
            'rows' => $logs,
            'empty' => 'No suspicious verification activity has been recorded for this company.',
            'search' => ['value' => $search, 'placeholder' => 'Search token, product, batch, IP, location'],
            'filters' => [
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All flagged', 'invalid' => 'Invalid', 'suspicious' => 'Suspicious', 'revoked' => 'Revoked', 'expired' => 'Expired', 'recalled' => 'Recalled'])],
                ['name' => 'device', 'label' => 'Device', 'value' => $device, 'options' => $this->optionList(['all' => 'All devices', 'mobile' => 'Mobile', 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'unknown' => 'Unknown'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'suspicious'], $request->query())),
        ]);
    }

    public function verification(Request $request, ScanLog $scanLog): View
    {
        return $this->detailView($request, 'Verification Details', $scanLog->token_scanned ?? 'Verification #'.$scanLog->id, $this->scanLogDetails($scanLog));
    }

    public function profile(Request $request): View
    {
        $company = $request->user()->company->load('profile');
        $profile = $company->profile;

        return $this->detailView($request, 'Company Profile', $company->name, [
            'Industry' => $company->industry ?? 'Not recorded',
            'Contact' => $company->contact_person ?? 'Not recorded',
            'Email' => $company->email ?? 'Not recorded',
            'Phone' => $company->phone ?? 'Not recorded',
            'Website' => $company->website ?? 'Not recorded',
            'Profile Status' => $profile?->publication_status ?? 'draft',
            'Support Phone' => $profile?->support_phone ?? 'Not recorded',
        ]);
    }

    public function account(Request $request, string $tab = 'profile'): View
    {
        $tabs = ['profile', 'users', 'password', 'log'];
        $activeTab = in_array($tab, $tabs, true) ? $tab : 'profile';
        $company = $request->user()->company->load('profile');
        $companyUsers = User::query()
            ->where('company_id', $company->id)
            ->orderByRaw("role = 'company_owner' desc")
            ->orderBy('name')
            ->get();

        return view('company.account', [
            'company' => $company,
            'accountUser' => $request->user(),
            'activeTab' => $activeTab,
            'accountTabs' => [
                ['key' => 'profile', 'label' => 'Company profile'],
                ['key' => 'users', 'label' => 'Users'],
                ['key' => 'password', 'label' => 'Change Password'],
                ['key' => 'log', 'label' => 'Log'],
            ],
            'companyUsers' => $companyUsers,
            'accountLogs' => $this->companyActivityLogs($company),
        ]);
    }

    public function updateAccountPassword(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user->forceFill([
            'password' => Hash::make($data['password']),
            'must_reset_password' => false,
        ])->save();

        return redirect()
            ->route('company.account', ['tab' => 'password'])
            ->with('status', 'Password changed successfully.');
    }

    public function updateAccountProfile(Request $request): RedirectResponse
    {
        $company = $request->user()->company;

        $data = $request->validate([
            'company.contact_person' => ['nullable', 'string', 'max:255'],
            'company.email' => ['nullable', 'email', 'max:255'],
            'company.phone' => ['nullable', 'string', 'max:255'],
            'company.website' => ['nullable', 'url', 'max:255'],
            'profile.verification_headline' => ['nullable', 'string', 'max:255'],
            'profile.support_email' => ['nullable', 'email', 'max:255'],
            'profile.support_phone' => ['nullable', 'string', 'max:255'],
            'profile.website_url' => ['nullable', 'url', 'max:255'],
            'profile.company_description' => ['nullable', 'string', 'max:3000'],
            'profile.manufacturer_name' => ['nullable', 'string', 'max:255'],
            'profile.factory_address' => ['nullable', 'string', 'max:1000'],
            'profile.certification_number' => ['nullable', 'string', 'max:255'],
        ]);

        $company->update($data['company'] ?? []);
        $company->profile()->firstOrCreate()->update($data['profile'] ?? []);

        if (Schema::hasTable('activity_logs')) {
            ActivityLog::query()->create([
                'user_id' => $request->user()->id,
                'event' => 'company.profile.updated',
                'subject_type' => Company::class,
                'subject_id' => $company->id,
                'description' => $request->user()->name.' updated the company profile.',
            ]);
        }

        return redirect()
            ->route('company.account', ['tab' => 'profile'])
            ->with('status', 'Company profile updated successfully.');
    }

    public function users(Request $request): View
    {
        $company = $request->user()->company;
        $search = $this->searchTerm($request);
        $role = $this->selectedOption($request, 'role', ['all', 'company_owner', 'company_staff']);
        $status = $this->selectedOption($request, 'status', ['all', 'active', 'invited', 'suspended']);
        $perPage = $this->perPage($request);
        $sort = $this->selectedOption($request, 'sort', ['latest', 'name', 'email', 'role', 'status', 'login']);
        $direction = $this->direction($request);

        $users = User::query()
            ->where('company_id', $company->id)
            ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('role', 'like', '%'.$search.'%');
            }))
            ->when($role !== 'all', fn ($query) => $query->where('role', $role))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($sort === 'name', fn ($query) => $query->orderBy('name', $direction))
            ->when($sort === 'email', fn ($query) => $query->orderBy('email', $direction))
            ->when($sort === 'role', fn ($query) => $query->orderBy('role', $direction))
            ->when($sort === 'status', fn ($query) => $query->orderBy('status', $direction))
            ->when($sort === 'login', fn ($query) => $query->orderBy('last_login_at', $direction))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (User $user) => [
                'href' => route('company.users.show', $user),
                'detailTitle' => $user->name,
                'detailSubtitle' => 'User Details',
                'details' => $this->userDetails($user),
                'cells' => [
                    $user->name,
                    $user->email,
                    str_replace('_', ' ', $user->role),
                    $user->status,
                    $user->last_login_at?->diffForHumans() ?? 'No login recorded',
                ],
            ]);

        return $this->resourceView($request, 'Users', 'Company dashboard users for '.$company->name.'.', [
            'headers' => [
                ['label' => 'User', 'sort' => 'name'],
                ['label' => 'Email', 'sort' => 'email'],
                ['label' => 'Role', 'sort' => 'role'],
                ['label' => 'Status', 'sort' => 'status'],
                ['label' => 'Last Login', 'sort' => 'login'],
            ],
            'rows' => $users,
            'empty' => 'No company users have been assigned yet.',
            'search' => ['value' => $search, 'placeholder' => 'Search user, email, role'],
            'filters' => [
                ['name' => 'role', 'label' => 'Role', 'value' => $role, 'options' => $this->optionList(['all' => 'All roles', 'company_owner' => 'Company Owner', 'company_staff' => 'Company Staff'])],
                ['name' => 'status', 'label' => 'Status', 'value' => $status, 'options' => $this->optionList(['all' => 'All statuses', 'active' => 'Active', 'invited' => 'Invited', 'suspended' => 'Suspended'])],
            ],
            'sort' => ['value' => $sort, 'direction' => $direction],
            'perPage' => $perPage,
            'exportRoute' => route('company.exports', array_merge(['resource' => 'users'], $request->query())),
            'actions' => $request->user()->isCompanyOwner()
                ? [['label' => 'Add user', 'target' => 'company-user-drawer']]
                : [],
            'userCreate' => [
                'canCreate' => $request->user()->isCompanyOwner(),
                'storeRoute' => route('company.users.store'),
            ],
        ]);
    }

    public function storeUser(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isCompanyOwner(), 403);

        $company = $request->user()->company;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'role' => ['required', Rule::in(['company_owner', 'company_staff'])],
            'status' => ['required', Rule::in(['active', 'invited'])],
            'password' => ['required', 'string', Password::min(8)],
        ]);

        $user = User::query()->create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'status' => $data['status'],
            'must_reset_password' => true,
        ]);

        if (Schema::hasTable('activity_logs')) {
            ActivityLog::query()->create([
                'user_id' => $request->user()->id,
                'event' => 'company.user.created',
                'subject_type' => User::class,
                'subject_id' => $user->id,
                'description' => $request->user()->name.' added '.$user->name.' to '.$company->name.'.',
            ]);
        }

        $redirectRoute = $request->input('return_to') === 'account-users'
            ? route('company.account', ['tab' => 'users'])
            : route('company.users');

        return redirect()
            ->to($redirectRoute)
            ->with('status', 'User added successfully.');
    }

    public function user(Request $request, User $user): View
    {
        return $this->detailView($request, 'User Details', $user->name, $this->userDetails($user), route('company.users'));
    }

    public function export(Request $request, string $resource)
    {
        $company = $request->user()->company;
        $filename = 'company-'.$resource.'-'.now()->format('Ymd-His').'.csv';
        $rows = $this->exportRows($request, $company, $resource);

        abort_unless($rows !== null, 404);

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function scanLogQuery(Company $company)
    {
        return ScanLog::query()
            ->where(function ($query) use ($company): void {
                $query
                    ->whereHas('qrCode.batch.product', fn ($productQuery) => $productQuery->where('company_id', $company->id))
                    ->orWhereHas('labelCode', fn ($labelCodeQuery) => $labelCodeQuery->where('company_id', $company->id));
            });
    }

    private function companyActivityLogs(Company $company): Collection
    {
        if (! Schema::hasTable('activity_logs')) {
            return collect();
        }

        return ActivityLog::query()
            ->with('user')
            ->where(function ($query) use ($company): void {
                $query
                    ->whereIn('user_id', User::query()->where('company_id', $company->id)->select('id'))
                    ->orWhere(function ($subjectQuery) use ($company): void {
                        $subjectQuery
                            ->where('subject_type', Batch::class)
                            ->whereIn('subject_id', Batch::query()
                                ->whereHas('product', fn ($productQuery) => $productQuery->where('company_id', $company->id))
                                ->select('id'));
                    });
            })
            ->latest()
            ->take(30)
            ->get();
    }

    /**
     * @return array<string, string>
     */
    private function productDetails(Product $product): array
    {
        return [
            'Company' => $product->company?->name ?? 'Not recorded',
            'SKU' => $product->sku ?? 'No SKU',
            'Category' => $product->productCategory?->name ?? $product->category ?? 'Uncategorized',
            'Manufacturer' => $product->manufacturer ?? 'Not recorded',
            'Status' => ucfirst($product->status),
            'Batches' => number_format($product->batches_count ?? $product->batches()->count()),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function batchDetails(Batch $batch): array
    {
        return [
            'Product' => $batch->product?->name ?? 'No product',
            'Quantity' => number_format($batch->quantity),
            'Manufactured' => $batch->manufacture_date?->format('M j, Y') ?? 'Not recorded',
            'Expires' => $batch->expiry_date?->format('M j, Y') ?? 'Not recorded',
            'QA Status' => ucfirst($batch->qa_status),
            'Batch Status' => ucfirst($batch->status),
            'QR Status' => $batch->qrCode?->status ?? 'No QR',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labelOrderDetails(LabelOrder $labelOrder): array
    {
        return [
            'Product' => $labelOrder->batch?->product?->name ?? 'General order',
            'Batch' => $labelOrder->batch?->batch_number ?? 'No batch',
            'Print Quantity' => number_format($labelOrder->print_quantity),
            'USSD Codes' => number_format($labelOrder->label_codes_count ?? $labelOrder->labelCodes()->count()),
            'Vendor' => $labelOrder->printer_vendor ?? 'Not assigned',
            'Delivery' => $labelOrder->delivery_date?->format('M j, Y') ?? 'No date',
            'Status' => ucfirst($labelOrder->status),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labelCodeDetails(LabelCode $labelCode): array
    {
        return [
            'Product' => $labelCode->batch?->product?->name ?? 'General code',
            'Order' => $labelCode->label_order_id ? '#'.$labelCode->label_order_id : 'No order',
            'Status' => ucfirst($labelCode->status),
            'Verification Count' => number_format($labelCode->verification_count),
            'First Verified' => $labelCode->first_verified_at?->format('M j, Y g:i A') ?? 'Not checked',
            'Last Verified' => $labelCode->last_verified_at?->format('M j, Y g:i A') ?? 'Not checked',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function scanLogDetails(ScanLog $scanLog): array
    {
        $productName = $scanLog->qrCode?->batch?->product?->name
            ?? $scanLog->labelCode?->batch?->product?->name
            ?? 'Unknown product';

        return [
            'Product' => $productName,
            'Status' => ucfirst($scanLog->status_result),
            'Channel' => strtoupper($scanLog->device_type ?? 'web'),
            'Location' => collect([$scanLog->city, $scanLog->country])->filter()->join(', ') ?: 'Unknown',
            'Checked At' => $scanLog->scanned_at?->format('M j, Y g:i A') ?? 'Not recorded',
            'IP Address' => $scanLog->ip_address ?? 'Not recorded',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function userDetails(User $user): array
    {
        return [
            'Name' => $user->name,
            'Email' => $user->email,
            'Role' => str_replace('_', ' ', $user->role),
            'Status' => ucfirst($user->status),
            'Last Login' => $user->last_login_at?->format('M j, Y g:i A') ?? 'No login recorded',
        ];
    }

    private function searchTerm(Request $request): string
    {
        return trim((string) $request->query('q', ''));
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function selectedOption(Request $request, string $key, array $allowed): string
    {
        $value = (string) $request->query($key, $allowed[0] ?? 'all');

        return in_array($value, $allowed, true) ? $value : ($allowed[0] ?? 'all');
    }

    private function perPage(Request $request): int
    {
        $perPage = (int) $request->query('per_page', 15);

        return in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 15;
    }

    private function direction(Request $request): string
    {
        return $request->query('direction') === 'asc' ? 'asc' : 'desc';
    }

    /**
     * @param  array<string, string>  $options
     * @return array<int, array{value: string, label: string}>
     */
    private function optionList(array $options): array
    {
        return collect($options)
            ->map(fn (string $label, string $value) => ['value' => $value, 'label' => $label])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function companyProductCategories(Company $company): array
    {
        $categories = Product::query()
            ->where('company_id', $company->id)
            ->with('productCategory')
            ->get()
            ->map(fn (Product $product) => $product->productCategory?->name ?? $product->category)
            ->filter()
            ->unique()
            ->sort()
            ->mapWithKeys(fn (string $category) => [$category => $category])
            ->all();

        return ['all' => 'All categories'] + $categories;
    }

    /**
     * @return array{range: string, start: Carbon, end: Carbon, start_date: string, end_date: string}
     */
    private function chartRange(Request $request): array
    {
        $range = $this->selectedOption($request, 'range', ['30', '90', '365', 'custom']);

        if ($range === 'custom' && $request->filled(['start_date', 'end_date'])) {
            $start = Carbon::parse($request->query('start_date'))->startOfDay();
            $end = Carbon::parse($request->query('end_date'))->endOfDay();

            if ($start->greaterThan($end)) {
                [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            }

            return [
                'range' => 'custom',
                'start' => $start,
                'end' => $end,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
            ];
        }

        $days = (int) $range;
        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        return [
            'range' => $range,
            'start' => $start,
            'end' => $end,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    /**
     * @return array<int, array<int, mixed>>|null
     */
    private function exportRows(Request $request, Company $company, string $resource): ?array
    {
        $search = $this->searchTerm($request);

        return match ($resource) {
            'products' => array_merge(
                [['Product', 'SKU', 'Category', 'Batches', 'Status']],
                Product::query()
                    ->with('productCategory')
                    ->withCount('batches')
                    ->where('company_id', $company->id)
                    ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                    ->latest()
                    ->get()
                    ->map(fn (Product $product) => [
                        $product->name,
                        $product->sku,
                        $product->productCategory?->name ?? $product->category,
                        $product->batches_count,
                        $product->status,
                    ])
                    ->all()
            ),
            'batches' => array_merge(
                [['Batch', 'Product', 'Quantity', 'QA', 'Status', 'QR']],
                Batch::query()
                    ->with(['product', 'qrCode'])
                    ->whereHas('product', fn ($query) => $query->where('company_id', $company->id))
                    ->when($search !== '', fn ($query) => $query->where('batch_number', 'like', '%'.$search.'%'))
                    ->latest()
                    ->get()
                    ->map(fn (Batch $batch) => [
                        $batch->batch_number,
                        $batch->product?->name,
                        $batch->quantity,
                        $batch->qa_status,
                        $batch->status,
                        $batch->qrCode?->status ?? 'No QR',
                    ])
                    ->all()
            ),
            'label-orders' => array_merge(
                [['Order', 'Product', 'Quantity', 'USSD Codes', 'Status']],
                LabelOrder::query()
                    ->with('batch.product')
                    ->withCount('labelCodes')
                    ->where('company_id', $company->id)
                    ->when($search !== '', fn ($query) => $query->where('printer_vendor', 'like', '%'.$search.'%'))
                    ->latest()
                    ->get()
                    ->map(fn (LabelOrder $order) => [
                        '#'.$order->id.' '.$order->label_type,
                        $order->batch?->product?->name ?? 'General order',
                        $order->print_quantity,
                        $order->label_codes_count,
                        $order->status,
                    ])
                    ->all()
            ),
            'ussd-codes' => array_merge(
                [['Code', 'Product', 'Order', 'Checks', 'Status']],
                LabelCode::query()
                    ->with(['batch.product'])
                    ->where('company_id', $company->id)
                    ->when($search !== '', fn ($query) => $query->where('code', 'like', '%'.strtoupper($search).'%'))
                    ->latest()
                    ->get()
                    ->map(fn (LabelCode $code) => [
                        $code->formattedCode(),
                        $code->batch?->product?->name ?? 'General code',
                        $code->label_order_id ? '#'.$code->label_order_id : 'No order',
                        $code->verification_count,
                        $code->status,
                    ])
                    ->all()
            ),
            'verifications', 'suspicious' => array_merge(
                [['Token', 'Product', 'Channel', 'Location', 'Status', 'Checked At']],
                $this->scanLogQuery($company)
                    ->with(['qrCode.batch.product', 'labelCode.batch.product'])
                    ->when($resource === 'suspicious', fn ($query) => $query->whereIn('status_result', self::FLAGGED_STATUSES))
                    ->when($search !== '', fn ($query) => $query->where('token_scanned', 'like', '%'.$search.'%'))
                    ->latest('scanned_at')
                    ->get()
                    ->map(fn (ScanLog $scan) => [
                        $scan->token_scanned,
                        $scan->qrCode?->batch?->product?->name ?? $scan->labelCode?->batch?->product?->name ?? 'Unknown product',
                        strtoupper($scan->device_type ?? 'web'),
                        collect([$scan->city, $scan->country])->filter()->join(', '),
                        $scan->status_result,
                        $scan->scanned_at?->toDateTimeString(),
                    ])
                    ->all()
            ),
            'users' => array_merge(
                [['Name', 'Email', 'Role', 'Status', 'Last Login']],
                User::query()
                    ->where('company_id', $company->id)
                    ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                        $builder
                            ->where('name', 'like', '%'.$search.'%')
                            ->orWhere('email', 'like', '%'.$search.'%');
                    }))
                    ->latest()
                    ->get()
                    ->map(fn (User $user) => [
                        $user->name,
                        $user->email,
                        str_replace('_', ' ', $user->role),
                        $user->status,
                        $user->last_login_at?->toDateTimeString(),
                    ])
                    ->all()
            ),
            default => null,
        };
    }

    private function topCheckedProducts(Company $company): Collection
    {
        return $this->scanLogQuery($company)
            ->with(['qrCode.batch.product', 'labelCode.batch.product'])
            ->latest('scanned_at')
            ->get()
            ->map(function (ScanLog $scanLog): ?Product {
                return $scanLog->qrCode?->batch?->product
                    ?? $scanLog->labelCode?->batch?->product;
            })
            ->filter()
            ->groupBy('id')
            ->map(fn (Collection $products) => [
                'name' => $products->first()->name,
                'checks' => $products->count(),
                'status' => $products->first()->status,
            ])
            ->sortByDesc('checks')
            ->take(5)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardChartData(Company $company, Carbon $start, Carbon $end): array
    {
        $scans = $this->scanLogQuery($company)
            ->whereBetween('scanned_at', [$start, $end])
            ->get(['qr_code_id', 'label_code_id', 'status_result', 'scanned_at']);

        $dayCount = min(366, (int) $start->diffInDays($end) + 1);
        $days = collect(range(0, $dayCount - 1))->map(fn (int $offset) => $start->copy()->addDays($offset));

        return [
            'trend' => [
                'labels' => $days->map(fn ($date) => $date->format('M j'))->values(),
                'total' => $days->map(fn ($date) => $scans->filter(fn (ScanLog $scan) => $scan->scanned_at?->isSameDay($date))->count())->values(),
                'qr' => $days->map(fn ($date) => $scans->filter(fn (ScanLog $scan) => $scan->qr_code_id && $scan->scanned_at?->isSameDay($date))->count())->values(),
                'ussd' => $days->map(fn ($date) => $scans->filter(fn (ScanLog $scan) => $scan->label_code_id && $scan->scanned_at?->isSameDay($date))->count())->values(),
            ],
            'channels' => [
                'labels' => ['QR scans', 'USSD checks', 'Other'],
                'values' => [
                    $scans->whereNotNull('qr_code_id')->count(),
                    $scans->whereNotNull('label_code_id')->count(),
                    $scans->filter(fn (ScanLog $scan) => blank($scan->qr_code_id) && blank($scan->label_code_id))->count(),
                ],
            ],
            'outcomes' => [
                'labels' => ['Genuine', 'Invalid', 'Suspicious', 'Revoked', 'Expired', 'Recalled'],
                'values' => collect(['genuine', 'invalid', 'suspicious', 'revoked', 'expired', 'recalled'])
                    ->map(fn (string $status) => $scans->where('status_result', $status)->count())
                    ->values(),
            ],
        ];
    }

    private function resourceView(Request $request, string $title, string $subtitle, array $table): View
    {
        return view('company.resource', [
            'company' => $request->user()->company,
            'title' => $title,
            'subtitle' => $subtitle,
            'table' => $table,
            'details' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function detailView(Request $request, string $title, string $subtitle, array $details, ?string $backUrl = null): View
    {
        return view('company.resource', [
            'company' => $request->user()->company,
            'title' => $title,
            'subtitle' => $subtitle,
            'table' => null,
            'details' => $details,
            'backUrl' => $backUrl,
        ]);
    }
}
