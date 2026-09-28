<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\LabelOrder;
use App\Services\LabelCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

class LabelOrderController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $search = trim((string) $request->query('q', ''));

        $orders = LabelOrder::query()
            ->with(['company', 'batch.product'])
            ->withCount('labelCodes')
            ->when($search !== '', fn ($query) => $this->applySearch($query, $search))
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (LabelOrder $order) => [
                'id' => $order->id,
                'title' => '#'.$order->id.' · '.($order->company?->name ?? 'No company'),
                'subtitle' => ($order->batch?->product?->name ?? 'No product').' · '.($order->batch?->batch_number ?? 'General').' · '.($order->printer_vendor ?? 'No printer'),
                'codes' => number_format($order->label_codes_count).' codes',
            ]);

        return response()->json([
            'results' => $orders,
        ]);
    }

    public function store(Request $request, LabelCodeGenerator $codeGenerator): RedirectResponse
    {
        $labelOrder = LabelOrder::query()->create($this->validatedData($request));
        $codeGenerator->reconcile($labelOrder->load('company'));

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order created with '.number_format($labelOrder->print_quantity).' USSD codes.');
    }

    public function update(Request $request, LabelOrder $labelOrder, LabelCodeGenerator $codeGenerator): RedirectResponse
    {
        $labelOrder->update($this->validatedData($request));
        $codeGenerator->reconcile($labelOrder->refresh()->load('company'));

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order updated and USSD codes synced to print quantity.');
    }

    public function destroy(LabelOrder $labelOrder): RedirectResponse
    {
        $labelOrder->delete();

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order deleted successfully.');
    }

    public function exportCodes(LabelOrder $labelOrder): StreamedResponse
    {
        $labelOrder->load(['company', 'batch.product']);
        $filename = 'label-order-'.$labelOrder->id.'-ussd-codes.csv';

        return response()->streamDownload(function () use ($labelOrder): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'sequence',
                'scratch_code',
                'formatted_code',
                'status',
                'verification_count',
                'company',
                'product',
                'batch',
                'label_type',
            ]);

            $sequence = 1;
            $labelOrder->labelCodes()
                ->oldest('id')
                ->chunk(500, function ($codes) use (&$sequence, $handle, $labelOrder): void {
                    foreach ($codes as $code) {
                        fputcsv($handle, [
                            $sequence++,
                            $code->code,
                            $code->formattedCode(),
                            $code->status,
                            $code->verification_count,
                            $labelOrder->company?->name,
                            $labelOrder->batch?->product?->name,
                            $labelOrder->batch?->batch_number,
                            $labelOrder->label_type,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'print', 'ship', 'deliver', 'cancel', 'delete'])],
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:label_orders,id'],
        ]);

        $orders = LabelOrder::query()
            ->whereIn('id', $data['order_ids'])
            ->get();

        if ($orders->isEmpty()) {
            return redirect()
                ->route('active8.page', 'label-orders')
                ->withErrors('Select at least one label order.');
        }

        if ($data['action'] === 'delete') {
            LabelOrder::query()
                ->whereIn('id', $orders->pluck('id'))
                ->delete();

            return redirect()
                ->route('active8.page', 'label-orders')
                ->with('status', $orders->count().' label orders deleted successfully.');
        }

        $status = [
            'approve' => 'approved',
            'print' => 'printed',
            'ship' => 'shipped',
            'deliver' => 'delivered',
            'cancel' => 'cancelled',
        ][$data['action']];

        LabelOrder::query()
            ->whereIn('id', $orders->pluck('id'))
            ->update(['status' => $status]);

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', $orders->count().' label orders updated successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request): array
    {
        $data = $request->validate([
            'company_id' => ['required', 'exists:companies,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'label_type' => ['required', Rule::in(['hologram', 'tamper-proof', 'security seal', 'combo'])],
            'label_size' => ['nullable', 'string', 'max:255'],
            'print_quantity' => ['required', 'integer', 'min:1'],
            'printer_vendor' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['pending', 'approved', 'printed', 'shipped', 'delivered', 'cancelled'])],
            'delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['batch_id']) {
            $batch = Batch::query()->with('product')->find($data['batch_id']);

            if ($batch?->product?->company_id) {
                $data['company_id'] = $batch->product->company_id;
            }
        }

        return $data;
    }

    private function applySearch($query, string $search): void
    {
        $query->where(function ($builder) use ($search): void {
            if (ctype_digit($search)) {
                $builder->orWhereKey($search);
            }

            $builder
                ->orWhere('printer_vendor', 'like', '%'.$search.'%')
                ->orWhereHas('company', fn ($companyQuery) => $companyQuery->where('name', 'like', '%'.$search.'%'))
                ->orWhereHas('batch', fn ($batchQuery) => $batchQuery->where('batch_number', 'like', '%'.$search.'%'))
                ->orWhereHas('batch.product', fn ($productQuery) => $productQuery->where('name', 'like', '%'.$search.'%'));
        });
    }
}
