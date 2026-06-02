<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\LabelOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LabelOrderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        LabelOrder::query()->create($this->validatedData($request));

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order created successfully.');
    }

    public function update(Request $request, LabelOrder $labelOrder): RedirectResponse
    {
        $labelOrder->update($this->validatedData($request));

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order updated successfully.');
    }

    public function destroy(LabelOrder $labelOrder): RedirectResponse
    {
        $labelOrder->delete();

        return redirect()
            ->route('active8.page', 'label-orders')
            ->with('status', 'Label order deleted successfully.');
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
}
