<?php

namespace Tests\Feature;

use App\Models\Batch;
use App\Models\Company;
use App\Models\LabelCode;
use App\Models\LabelOrder;
use App\Models\Product;
use App\Models\User;
use App\Services\LabelCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LabelOrderCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_label_order_create_generates_one_ussd_code_per_printed_label(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $this->actingAs($user)
            ->post(route('label-orders.store'), [
                'company_id' => $company->id,
                'batch_id' => $batch->id,
                'label_type' => 'hologram',
                'label_size' => '30mm x 30mm',
                'print_quantity' => 4,
                'printer_vendor' => 'SecurePrint Ghana',
                'status' => 'approved',
            ])
            ->assertRedirect(route('active8.page', 'label-orders'));

        $order = LabelOrder::query()->firstOrFail();

        $this->assertSame(4, $order->labelCodes()->count());
        $this->assertSame(4, LabelCode::query()->distinct('code')->count('code'));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{11}$/', $order->labelCodes()->firstOrFail()->code);
    }

    public function test_label_order_update_syncs_ussd_codes_to_print_quantity(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 2,
            'status' => 'pending',
        ]);

        app(LabelCodeGenerator::class)->reconcile($order->load('company'));

        $this->actingAs($user)
            ->put(route('label-orders.update', $order), [
                'company_id' => $company->id,
                'batch_id' => $batch->id,
                'label_type' => 'tamper-proof',
                'print_quantity' => 5,
                'status' => 'printed',
            ])
            ->assertRedirect(route('active8.page', 'label-orders'));

        $this->assertSame(5, $order->refresh()->labelCodes()->count());

        $this->actingAs($user)
            ->put(route('label-orders.update', $order), [
                'company_id' => $company->id,
                'batch_id' => $batch->id,
                'label_type' => 'tamper-proof',
                'print_quantity' => 3,
                'status' => 'printed',
            ])
            ->assertRedirect(route('active8.page', 'label-orders'));

        $this->assertSame(3, $order->refresh()->labelCodes()->count());
    }

    public function test_label_order_codes_can_be_exported_for_printing_in_sequence(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 2,
            'status' => 'printed',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $order->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'FIRSTCODE01',
            'status' => 'unused',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $order->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'SECONDCODE2',
            'status' => 'unused',
        ]);

        $response = $this->actingAs($user)
            ->get(route('label-orders.codes.export', $order))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('sequence,scratch_code,formatted_code,status,verification_count,company,product,batch,label_type', $csv);
        $this->assertStringContainsString('1,FIRSTCODE01', $csv);
        $this->assertStringContainsString('2,SECONDCODE2', $csv);
    }

    public function test_admin_can_view_all_ussd_codes_page(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 1,
            'status' => 'printed',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $order->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'VISIBLECODE1',
            'status' => 'unused',
        ]);

        $this->actingAs($user)
            ->get(route('active8.page', ['page' => 'label-codes', 'code_status' => 'unused']))
            ->assertOk()
            ->assertSee('USSD Codes')
            ->assertSee('VISIBLECODE1')
            ->assertSee('Download order codes CSV');
    }

    public function test_ussd_codes_page_uses_admin_pagination_controls(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 30,
            'status' => 'printed',
        ]);

        for ($index = 1; $index <= 30; $index++) {
            LabelCode::query()->create([
                'label_order_id' => $order->id,
                'company_id' => $company->id,
                'batch_id' => $batch->id,
                'code' => 'PAGECODE'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                'status' => 'unused',
            ]);
        }

        $this->actingAs($user)
            ->get(route('active8.page', ['page' => 'label-codes', 'per_page' => 25]))
            ->assertOk()
            ->assertSee('Showing 1-25')
            ->assertSee('of 30 codes')
            ->assertSee('25 / page');

        $this->actingAs($user)
            ->get(route('active8.page', ['page' => 'label-codes', 'per_page' => 25, 'code_page' => 2]))
            ->assertOk()
            ->assertSee('Showing 26-30')
            ->assertSee('PAGECODE030');
    }

    public function test_ussd_codes_page_order_selector_is_searchable(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $matchingOrder = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 1,
            'printer_vendor' => 'NeedlePrint',
            'status' => 'printed',
        ]);

        $otherOrder = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'tamper-proof',
            'print_quantity' => 1,
            'printer_vendor' => 'Other Vendor',
            'status' => 'printed',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $matchingOrder->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'NEEDLECODE1',
            'status' => 'unused',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $otherOrder->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'OTHERCODE01',
            'status' => 'unused',
        ]);

        $this->actingAs($user)
            ->get(route('active8.page', ['page' => 'label-codes', 'code_order_search' => 'NeedlePrint']))
            ->assertOk()
            ->assertSee('NEEDLECODE1')
            ->assertSee('NeedlePrint')
            ->assertDontSee('OTHERCODE01');
    }

    public function test_label_order_search_returns_live_picker_results(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $matchingOrder = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 2,
            'printer_vendor' => 'Live Needle Printer',
            'status' => 'printed',
        ]);

        LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'tamper-proof',
            'print_quantity' => 1,
            'printer_vendor' => 'Quiet Press',
            'status' => 'printed',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $matchingOrder->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'LIVEPICK001',
            'status' => 'unused',
        ]);

        LabelCode::query()->create([
            'label_order_id' => $matchingOrder->id,
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'code' => 'LIVEPICK002',
            'status' => 'unused',
        ]);

        $this->actingAs($user)
            ->getJson(route('label-orders.search', ['q' => 'Needle']))
            ->assertOk()
            ->assertJsonCount(1, 'results')
            ->assertJsonFragment([
                'id' => $matchingOrder->id,
                'codes' => '2 codes',
            ])
            ->assertJsonMissing([
                'subtitle' => $batch->product->name.' · '.$batch->batch_number.' · Quiet Press',
            ]);
    }

    public function test_ussd_codes_printing_flow_is_order_aware(): void
    {
        $user = User::factory()->create();
        [$company, $batch] = $this->companyAndBatch();

        $order = LabelOrder::query()->create([
            'company_id' => $company->id,
            'batch_id' => $batch->id,
            'label_type' => 'hologram',
            'print_quantity' => 2,
            'status' => 'approved',
        ]);

        foreach (['FLOWCODE001', 'FLOWCODE002'] as $code) {
            LabelCode::query()->create([
                'label_order_id' => $order->id,
                'company_id' => $company->id,
                'batch_id' => $batch->id,
                'code' => $code,
                'status' => 'unused',
            ]);
        }

        $this->actingAs($user)
            ->get(route('active8.page', ['page' => 'label-codes', 'code_order' => $order->id]))
            ->assertOk()
            ->assertSee('2 of 4 stages complete')
            ->assertSee('2 of 2 codes ready')
            ->assertSee('Next action')
            ->assertSee('data-print-flow-open', false)
            ->assertSee('role="dialog"', false)
            ->assertSee('Download Print CSV')
            ->assertSee('Mark Printed');

        $this->actingAs($user)
            ->post(route('label-orders.bulk'), [
                'action' => 'print',
                'order_ids' => [$order->id],
            ])
            ->assertRedirect(route('active8.page', 'label-orders'));

        $this->assertDatabaseHas('label_orders', [
            'id' => $order->id,
            'status' => 'printed',
        ]);
    }

    /**
     * @return array{0: Company, 1: Batch}
     */
    private function companyAndBatch(): array
    {
        $company = Company::query()->create([
            'name' => 'Akoma Naturals',
            'slug' => 'akoma-naturals',
            'status' => 'active',
        ]);

        $product = Product::query()->create([
            'company_id' => $company->id,
            'name' => 'Shea Butter',
            'slug' => 'shea-butter',
            'status' => 'active',
        ]);

        $batch = Batch::query()->create([
            'product_id' => $product->id,
            'batch_number' => 'AK-SG-001',
            'status' => 'printed',
        ]);

        return [$company, $batch];
    }
}
