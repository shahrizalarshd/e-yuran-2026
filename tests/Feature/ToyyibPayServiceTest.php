<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\SystemSetting;
use App\Services\ToyyibPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ToyyibPayServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ToyyibPayService $toyyibPayService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup ToyyibPay settings
        SystemSetting::set('toyyibpay_secret_key', 'test-secret-key', 'string', 'toyyibpay');
        SystemSetting::set('toyyibpay_category_code', 'test-category', 'string', 'toyyibpay');
        SystemSetting::set('toyyibpay_sandbox', '1', 'boolean', 'toyyibpay');

        $this->toyyibPayService = new ToyyibPayService();
    }

    // ==========================================
    // A. CONFIGURATION TESTS
    // ==========================================

    public function test_toyyibpay_is_configured(): void
    {
        $this->assertTrue($this->toyyibPayService->isConfigured());
    }

    public function test_toyyibpay_not_configured_without_credentials(): void
    {
        // Clear cache and settings
        \Illuminate\Support\Facades\Cache::flush();
        SystemSetting::where('key', 'toyyibpay_secret_key')->delete();
        SystemSetting::where('key', 'toyyibpay_category_code')->delete();
        
        $service = new ToyyibPayService();
        
        $this->assertFalse($service->isConfigured());
    }

    // ==========================================
    // B. PAYMENT URL TESTS
    // ==========================================

    public function test_get_payment_url(): void
    {
        $billCode = 'test123abc';
        $url = $this->toyyibPayService->getPaymentUrl($billCode);

        $this->assertStringContainsString($billCode, $url);
        $this->assertStringContainsString('dev.toyyibpay.com', $url);
    }

    // ==========================================
    // C. CALLBACK VERIFICATION TESTS
    // ==========================================

    public function test_verify_callback_with_valid_data(): void
    {
        $data = [
            'refno' => 'REF123456',
            'status' => '1',
            'reason' => 'Approved',
            'billcode' => 'test123abc',
            'order_id' => 'PAY-20241215-ABC123',
        ];

        $this->assertTrue($this->toyyibPayService->verifyCallback($data));
    }

    public function test_verify_callback_with_missing_fields(): void
    {
        $data = [
            'refno' => 'REF123456',
            'status' => '1',
            // Missing required fields
        ];

        $this->assertFalse($this->toyyibPayService->verifyCallback($data));
    }

    // ==========================================
    // D. PROCESS CALLBACK TESTS
    // ==========================================

    public function test_process_callback_success(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        $payment = Payment::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_no' => 'PAY-20241215-ABC123',
            'toyyibpay_billcode' => 'test123abc',
        ]);

        $data = [
            'refno' => 'REF123456',
            'status' => '1', // Success
            'reason' => 'Approved',
            'billcode' => 'test123abc',
            'order_id' => 'PAY-20241215-ABC123',
        ];

        $result = $this->toyyibPayService->processCallback($data);

        $this->assertNotNull($result);
        $this->assertEquals('success', $result->fresh()->status);
    }

    public function test_process_callback_failed(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        $payment = Payment::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_no' => 'PAY-20241215-ABC123',
        ]);

        $data = [
            'refno' => 'REF123456',
            'status' => '3', // Failed
            'reason' => 'Declined',
            'billcode' => 'test123abc',
            'order_id' => 'PAY-20241215-ABC123',
        ];

        $result = $this->toyyibPayService->processCallback($data);

        $this->assertNotNull($result);
        $this->assertEquals('failed', $result->fresh()->status);
    }

    public function test_process_callback_payment_not_found(): void
    {
        $data = [
            'refno' => 'REF123456',
            'status' => '1',
            'reason' => 'Approved',
            'billcode' => 'nonexistent',
            'order_id' => 'nonexistent',
        ];

        $result = $this->toyyibPayService->processCallback($data);

        $this->assertNull($result);
    }

    // ==========================================
    // E. CREATE BILL MOCK TESTS
    // ==========================================

    public function test_create_bill_returns_billcode(): void
    {
        Http::fake([
            'dev.toyyibpay.com/*' => Http::response([
                ['BillCode' => 'mock-billcode-123']
            ], 200),
        ]);

        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        $payment = Payment::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $billCode = $this->toyyibPayService->createBill($payment, [
            'description' => 'Test payment',
        ]);

        $this->assertEquals('mock-billcode-123', $billCode);
        $this->assertEquals('mock-billcode-123', $payment->fresh()->toyyibpay_billcode);
    }

    public function test_create_bill_returns_null_on_failure(): void
    {
        Http::fake([
            'dev.toyyibpay.com/*' => Http::response(['error' => 'Invalid request'], 400),
        ]);

        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        $payment = Payment::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $billCode = $this->toyyibPayService->createBill($payment, []);

        $this->assertNull($billCode);
    }

    public function test_create_bill_returns_null_when_not_configured(): void
    {
        SystemSetting::where('key', 'toyyibpay_secret_key')->delete();
        $service = new ToyyibPayService();

        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        $payment = Payment::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $billCode = $service->createBill($payment, []);

        $this->assertNull($billCode);
    }

    // ==========================================
    // F. PAYMENT CALLBACK CONTROLLER TESTS
    // ==========================================

    public function test_payment_callback_route_accessible(): void
    {
        $response = $this->get('/payment/callback');

        // Redirects to resident dashboard when no payment found
        $response->assertRedirect();
    }

    public function test_payment_webhook_route_accessible(): void
    {
        $response = $this->post('/payment/webhook', [
            'refno' => 'REF123',
            'status' => '1',
            'reason' => 'Test',
            'billcode' => 'test',
            'order_id' => 'test',
        ]);

        // Returns 400 when payment not found (as per controller logic)
        $response->assertStatus(400);
    }

    // ==========================================
    // G. SANDBOX vs PRODUCTION TESTS
    // ==========================================

    public function test_sandbox_url_in_sandbox_mode(): void
    {
        SystemSetting::set('toyyibpay_sandbox', '1', 'boolean', 'toyyibpay');
        $service = new ToyyibPayService();
        
        $url = $service->getPaymentUrl('test');
        
        $this->assertStringContainsString('dev.toyyibpay.com', $url);
    }

    // ==========================================
    // H. GET BILL TRANSACTIONS TESTS
    // ==========================================

    public function test_get_bill_transactions(): void
    {
        Http::fake([
            'dev.toyyibpay.com/*' => Http::response([
                ['status' => '1', 'refno' => 'REF123']
            ], 200),
        ]);

        $transactions = $this->toyyibPayService->getBillTransactions('test-billcode');

        $this->assertIsArray($transactions);
    }

    public function test_get_bill_transactions_returns_null_when_not_configured(): void
    {
        SystemSetting::where('key', 'toyyibpay_secret_key')->delete();
        $service = new ToyyibPayService();

        $transactions = $service->getBillTransactions('test-billcode');

        $this->assertNull($transactions);
    }
}

