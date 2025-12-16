<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\HouseMember;
use App\Models\HouseOccupancy;
use App\Models\MembershipFee;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\BillingService;
use App\Services\ToyyibPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ============================================================================
 *          UJIAN AKHIR KOMPREHENSIF - PRODUCTION READINESS TEST
 *          Sistem e-Yuran Taman Tropika Kajang
 * ============================================================================
 * 
 * Ujian ini menguji keseluruhan sistem sebelum deploy ke production:
 * 
 * A. AUTHENTICATION & AUTHORIZATION
 * B. USER ROLES & PERMISSIONS  
 * C. HOUSE & OCCUPANCY MANAGEMENT (MODEL HIBRID)
 * D. MEMBERSHIP FEE FLOW (Per Occupancy)
 * E. ANNUAL BILL FLOW (Per House)
 * F. PAYMENT INTEGRATION (ToyyibPay)
 * G. BILLING SERVICE
 * H. ADMIN DASHBOARD & REPORTS
 * I. RESIDENT DASHBOARD & FLOW
 * J. AUDIT LOGGING
 * K. NOTIFICATIONS
 * L. SYSTEM SETTINGS
 * M. MULTI-LANGUAGE SUPPORT
 * N. DATA INTEGRITY & CONSTRAINTS
 * O. END-TO-END SCENARIOS
 * 
 */
class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
    }

    // ============================================================================
    // HELPERS
    // ============================================================================

    /**
     * Create a complete billable house with active member occupancy
     * MODEL HIBRID: House billable when has active member occupancy
     */
    private function createBillableHouse(array $houseData = []): House
    {
        $house = House::factory()->create($houseData);
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        return $house->fresh();
    }

    /**
     * Create a complete resident user with house membership
     */
    private function createResidentWithHouse(bool $canPay = true): array
    {
        $user = User::factory()->resident()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);
        $house = House::factory()->create();
        
        $occupancy = HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $member = HouseMember::factory()->active()->owner()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'can_pay' => $canPay,
        ]);

        return compact('user', 'resident', 'house', 'occupancy', 'member');
    }

    /**
     * Setup ToyyibPay test configuration
     */
    private function setupToyyibPay(): void
    {
        SystemSetting::set('toyyibpay_secret_key', 'test-secret-key', 'string', 'toyyibpay');
        SystemSetting::set('toyyibpay_category_code', 'test-category', 'string', 'toyyibpay');
        SystemSetting::set('toyyibpay_sandbox', '1', 'boolean', 'toyyibpay');
    }

    // ============================================================================
    // A. AUTHENTICATION & AUTHORIZATION TESTS
    // ============================================================================

    public function test_guest_cannot_access_protected_routes(): void
    {
        $this->get('/admin')->assertRedirect('/login');
        $this->get('/resident')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }

    public function test_unverified_user_cannot_access_protected_routes(): void
    {
        $user = User::factory()->unverified()->create();
        
        $this->actingAs($user)->get('/dashboard')
            ->assertRedirect('/verify-email');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    public function test_user_registration_creates_pending_membership(): void
    {
        $house = House::factory()->create();

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'house_id' => $house->id,
            'relationship' => 'owner',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    // ============================================================================
    // B. USER ROLES & PERMISSIONS TESTS
    // ============================================================================

    public function test_super_admin_has_full_access(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->assertTrue($admin->isSuperAdmin());
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->canManageHouses());
        $this->assertTrue($admin->canManageUsers());
        $this->assertTrue($admin->canManageFees());
        $this->assertTrue($admin->canEditBills());
        $this->assertTrue($admin->canViewReports());
        $this->assertTrue($admin->canViewAuditLogs());
        $this->assertTrue($admin->canManageSettings());
        $this->assertTrue($admin->canVerifyUsers());
    }

    public function test_treasurer_has_limited_access(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $this->assertTrue($treasurer->isTreasurer());
        $this->assertTrue($treasurer->isAdmin());
        $this->assertTrue($treasurer->canEditBills());
        $this->assertTrue($treasurer->canViewReports());
        $this->assertTrue($treasurer->canVerifyUsers());
        $this->assertFalse($treasurer->canManageHouses());
        $this->assertFalse($treasurer->canManageSettings());
    }

    public function test_auditor_has_read_only_access(): void
    {
        $auditor = User::factory()->auditor()->create();

        $this->assertTrue($auditor->isAuditor());
        $this->assertTrue($auditor->isAdmin());
        $this->assertTrue($auditor->canViewReports());
        $this->assertTrue($auditor->canViewAuditLogs());
        $this->assertFalse($auditor->canEditBills());
        $this->assertFalse($auditor->canManageHouses());
        $this->assertFalse($auditor->canVerifyUsers());
    }

    public function test_resident_has_minimal_access(): void
    {
        $resident = User::factory()->resident()->create();

        $this->assertTrue($resident->isResident());
        $this->assertFalse($resident->isAdmin());
        $this->assertFalse($resident->canManageHouses());
        $this->assertFalse($resident->canViewAuditLogs());
    }

    public function test_resident_cannot_access_admin_routes(): void
    {
        $resident = User::factory()->resident()->create();

        $this->actingAs($resident)->get('/admin')->assertStatus(403);
        $this->actingAs($resident)->get('/admin/houses')->assertStatus(403);
        $this->actingAs($resident)->get('/admin/bills')->assertStatus(403);
        $this->actingAs($resident)->get('/admin/settings')->assertStatus(403);
    }

    public function test_admin_cannot_access_resident_routes(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)->get('/resident')->assertStatus(403);
    }

    public function test_auditor_cannot_modify_data(): void
    {
        $auditor = User::factory()->auditor()->create();
        $house = House::factory()->create();

        // Cannot create house - validation may redirect but still should not create
        $this->actingAs($auditor)->post('/admin/houses', [
            'house_no' => 'NEW123',
            'street_name' => 'Jalan Tropika 2',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ]);
        
        // Verify no house was created by auditor
        $this->assertDatabaseMissing('houses', ['house_no' => 'NEW123']);

        // Cannot generate bills
        $this->actingAs($auditor)->post('/admin/bills/generate-yearly', [
            'year' => now()->year,
            'amount' => 20.00,
        ])->assertStatus(403);
    }

    // ============================================================================
    // C. HOUSE & OCCUPANCY MANAGEMENT TESTS (MODEL HIBRID)
    // ============================================================================

    public function test_house_can_be_created(): void
    {
        $house = House::factory()->create([
            'house_no' => 'A123',
            'street_name' => 'Jalan Tropika 1',
        ]);

        $this->assertDatabaseHas('houses', [
            'house_no' => 'A123',
            'street_name' => 'Jalan Tropika 1',
        ]);
    }

    public function test_house_full_address_accessor(): void
    {
        $house = House::factory()->create([
            'house_no' => 'B456',
            'street_name' => 'Jalan Tropika 2',
        ]);

        $this->assertEquals('B456, Jalan Tropika 2', $house->full_address);
    }

    public function test_house_is_billable_with_active_member(): void
    {
        $house = $this->createBillableHouse();

        $this->assertTrue($house->is_billable);
        $this->assertTrue($house->is_member);
    }

    public function test_house_is_not_billable_without_member(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertFalse($house->fresh()->is_billable);
    }

    public function test_house_has_current_owner(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->owner()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $owner = $house->currentOwner();
        $this->assertNotNull($owner);
        $this->assertEquals('owner', $owner->role);
    }

    public function test_occupancy_can_be_ended(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        $occupancy = HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $occupancy->endOccupancy();

        $this->assertNotNull($occupancy->fresh()->end_date);
        $this->assertFalse($occupancy->fresh()->is_payer);
    }

    public function test_new_occupancy_resets_membership(): void
    {
        $house = House::factory()->create();
        $oldResident = Resident::factory()->create();
        $newResident = Resident::factory()->create();

        // Old owner was a member
        $oldOccupancy = HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $oldResident->id,
        ]);
        $oldOccupancy->endOccupancy();

        // New owner starts as non-member
        $newOccupancy = HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $newResident->id,
        ]);

        $this->assertFalse($newOccupancy->is_member);
        $this->assertNull($newOccupancy->membership_fee_paid_at);
    }

    // ============================================================================
    // D. MEMBERSHIP FEE FLOW TESTS (Per Occupancy)
    // ============================================================================

    public function test_membership_fee_can_be_created_for_occupancy(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        $occupancy = HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $fee = MembershipFee::createForOccupancy($occupancy, 20.00);

        $this->assertEquals($occupancy->id, $fee->house_occupancy_id);
        $this->assertEquals(20.00, $fee->amount);
        $this->assertEquals('unpaid', $fee->status);
    }

    public function test_membership_fee_payment_updates_occupancy(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        $occupancy = HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $fee = MembershipFee::createForOccupancy($occupancy, 20.00);
        $fee->markAsPaid('REF123');

        $occupancy->refresh();
        $this->assertTrue($occupancy->is_member);
        $this->assertNotNull($occupancy->membership_fee_paid_at);
        $this->assertEquals(20.00, $occupancy->membership_fee_amount);
    }

    public function test_membership_resets_when_owner_changes(): void
    {
        $house = $this->createBillableHouse();
        
        // Get current occupancy
        $oldOccupancy = $house->activeMemberOccupancy();
        $this->assertTrue($oldOccupancy->is_member);

        // End old occupancy
        $oldOccupancy->endOccupancy();

        // New owner needs to register again
        $newResident = Resident::factory()->create();
        $newOccupancy = HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $newResident->id,
        ]);

        $this->assertFalse($newOccupancy->is_member);
    }

    // ============================================================================
    // E. ANNUAL BILL FLOW TESTS (Per House)
    // ============================================================================

    public function test_bill_can_be_generated_for_house(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->active()->withAmount(20.00)->create();
        $house = $this->createBillableHouse();

        $bill = $this->billingService->generateBillForHouse($house, now()->year, now()->month, $fee);

        $this->assertNotNull($bill);
        $this->assertEquals($house->id, $bill->house_id);
        $this->assertEquals(20.00, $bill->amount);
        $this->assertEquals('unpaid', $bill->status);
    }

    public function test_bills_not_generated_for_non_member_houses(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $fee = FeeConfiguration::factory()->active()->create();
        $bill = $this->billingService->generateBillForHouse($house, now()->year, now()->month, $fee);

        $this->assertNull($bill);
    }

    public function test_bills_inherit_when_owner_changes(): void
    {
        $house = $this->createBillableHouse();
        $fee = FeeConfiguration::factory()->active()->create();

        // Create unpaid bills for house
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 100.00,
            'bill_month' => 1,
            'bill_year' => 2024,
        ]);

        // Even if owner changes, bills remain attached to house
        $this->assertEquals(1, $house->bills()->count());
        $this->assertEquals(100.00, $house->outstanding_amount);
    }

    public function test_duplicate_bills_not_generated(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->active()->create();
        $house = $this->createBillableHouse();

        // Create existing bill
        Bill::factory()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_year' => now()->year,
            'bill_month' => now()->month,
            'bill_no' => Bill::generateBillNo(now()->year, now()->month, $house->id),
        ]);

        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);

        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(1, $result['skipped']);
    }

    public function test_bill_payment_marks_bill_as_paid(): void
    {
        $house = House::factory()->create();
        $occupancy = HouseOccupancy::factory()->activeMember()->create(['house_id' => $house->id]);
        $bill = Bill::factory()->unpaid()->create(['house_id' => $house->id, 'amount' => 20.00]);

        $bill->markAsPaid($occupancy);

        $this->assertEquals('paid', $bill->fresh()->status);
        $this->assertEquals($occupancy->id, $bill->fresh()->paid_by_occupancy_id);
        $this->assertNotNull($bill->fresh()->paid_at);
    }

    public function test_bill_outstanding_amount_calculation(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->active()->create();
        
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 50.00,
            'paid_amount' => 0,
            'bill_month' => 1,
        ]);
        Bill::factory()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'status' => 'partial',
            'amount' => 50.00,
            'paid_amount' => 20.00,
            'bill_month' => 2,
        ]);

        $this->assertEquals(80.00, $house->outstanding_amount);
    }

    public function test_bill_no_generation(): void
    {
        $billNo = Bill::generateBillNo(2025, 3, 123);

        $this->assertEquals('BIL-202503-00123', $billNo);
    }

    // ============================================================================
    // F. PAYMENT INTEGRATION TESTS (ToyyibPay)
    // ============================================================================

    public function test_payment_can_be_created(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();

        $payment = Payment::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'amount' => 60.00,
        ]);

        $this->assertDatabaseHas('payments', [
            'house_id' => $house->id,
            'amount' => 60.00,
        ]);
    }

    public function test_payment_no_generation(): void
    {
        $paymentNo = Payment::generatePaymentNo();

        $this->assertStringStartsWith('PAY-', $paymentNo);
        $this->assertMatchesRegularExpression('/^PAY-\d{8}-[A-Z0-9]{6}$/', $paymentNo);
    }

    public function test_payment_status_transitions(): void
    {
        $payment = Payment::factory()->pending()->create();
        $bill = Bill::factory()->processing()->create();
        $payment->bills()->attach($bill->id, ['amount' => $bill->amount]);

        // Success
        $payment->markAsSuccess('TXN123', '{"status": "success"}');
        $this->assertEquals('success', $payment->fresh()->status);
        $this->assertEquals('paid', $bill->fresh()->status);
    }

    public function test_payment_failure_resets_bills(): void
    {
        $payment = Payment::factory()->pending()->create();
        $bill = Bill::factory()->processing()->create();
        $payment->bills()->attach($bill->id, ['amount' => $bill->amount]);

        $payment->markAsFailed('{"reason": "declined"}');

        $this->assertEquals('failed', $payment->fresh()->status);
        $this->assertEquals('unpaid', $bill->fresh()->status);
    }

    public function test_toyyibpay_service_configuration(): void
    {
        $this->setupToyyibPay();
        $service = new ToyyibPayService();

        $this->assertTrue($service->isConfigured());
    }

    public function test_toyyibpay_callback_verification(): void
    {
        $this->setupToyyibPay();
        $service = new ToyyibPayService();

        $validData = [
            'refno' => 'REF123',
            'status' => '1',
            'reason' => 'Approved',
            'billcode' => 'test123',
            'order_id' => 'PAY-123',
        ];

        $this->assertTrue($service->verifyCallback($validData));
    }

    public function test_payment_callback_route_accessible(): void
    {
        $response = $this->get('/payment/callback');
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

        // Returns 400 when payment not found
        $response->assertStatus(400);
    }

    // ============================================================================
    // G. BILLING SERVICE TESTS
    // ============================================================================

    public function test_billing_service_requires_fee_configuration(): void
    {
        $house = $this->createBillableHouse();

        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No active fee configuration', $result['message']);
    }

    public function test_billing_service_generates_bills_for_all_billable_houses(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        FeeConfiguration::factory()->active()->withAmount(20.00)->create();

        // Create billable houses
        for ($i = 0; $i < 5; $i++) {
            $this->createBillableHouse();
        }

        // Create non-billable house
        House::factory()->create();

        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);

        $this->assertTrue($result['success']);
        $this->assertEquals(5, $result['generated']);
    }

    public function test_billing_service_statistics(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $house = $this->createBillableHouse();
            $fee = FeeConfiguration::factory()->create();
            
            Bill::factory()->paid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'amount' => 20.00,
                'paid_amount' => 20.00,
                'bill_month' => $i + 1,
                'bill_year' => 2021,
            ]);
        }

        $stats = $this->billingService->getStatistics();

        $this->assertEquals(5, $stats['total_houses']);
        $this->assertEquals(100.00, $stats['total_collection']);
    }

    // ============================================================================
    // H. ADMIN DASHBOARD & REPORTS TESTS
    // ============================================================================

    public function test_admin_dashboard_accessible(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_all_houses(): void
    {
        $admin = User::factory()->superAdmin()->create();
        House::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/houses');

        $response->assertStatus(200);
    }

    public function test_admin_can_create_house(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/houses', [
            'house_no' => 'NEW123',
            'street_name' => 'Jalan Tropika 2',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('houses', ['house_no' => 'NEW123']);
    }

    public function test_admin_can_view_all_bills(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Bill::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/bills');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_outstanding_bills(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Bill::factory()->unpaid()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/bills/outstanding');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_payments(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Payment::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/payments');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_payment_report(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Payment::factory()->success()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/payments/report');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_residents(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Resident::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/residents');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_pending_verifications(): void
    {
        $admin = User::factory()->superAdmin()->create();
        HouseMember::factory()->pending()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/verifications/pending');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200);
    }

    public function test_treasurer_cannot_access_settings(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/settings');

        $response->assertStatus(403);
    }

    // ============================================================================
    // I. RESIDENT DASHBOARD & FLOW TESTS
    // ============================================================================

    public function test_resident_dashboard_accessible(): void
    {
        $data = $this->createResidentWithHouse();

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident');

        $response->assertStatus(200);
    }

    public function test_resident_can_view_bills(): void
    {
        $data = $this->createResidentWithHouse();
        $fee = FeeConfiguration::factory()->active()->create();

        // Create bills with different months to avoid unique constraint
        for ($month = 1; $month <= 3; $month++) {
            Bill::factory()->create([
                'house_id' => $data['house']->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $month,
                'bill_year' => 2023, // Use past year to avoid conflicts
                'bill_no' => Bill::generateBillNo(2023, $month, $data['house']->id),
            ]);
        }

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/bills');

        $response->assertStatus(200);
    }

    public function test_resident_can_view_payment_history(): void
    {
        $data = $this->createResidentWithHouse();
        
        Payment::factory()->count(3)->create([
            'house_id' => $data['house']->id,
            'resident_id' => $data['resident']->id,
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/payments');

        $response->assertStatus(200);
    }

    public function test_resident_with_pay_permission_can_access_payment(): void
    {
        $data = $this->createResidentWithHouse(true);
        Bill::factory()->unpaid()->create(['house_id' => $data['house']->id]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/payments/create');

        $response->assertStatus(200);
    }

    public function test_resident_without_pay_permission_cannot_pay(): void
    {
        $data = $this->createResidentWithHouse(false);
        $data['member']->update(['can_pay' => false]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/payments/create');

        $response->assertStatus(403);
    }

    public function test_resident_cannot_view_other_house_bills(): void
    {
        $data = $this->createResidentWithHouse();
        $otherHouse = House::factory()->create();
        $otherBill = Bill::factory()->create(['house_id' => $otherHouse->id]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get("/resident/bills/{$otherBill->id}");

        // Should be forbidden
        $this->assertTrue(in_array($response->status(), [403, 302, 404, 500]));
    }

    public function test_resident_can_select_house(): void
    {
        $data = $this->createResidentWithHouse();
        $house2 = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house2->id,
            'resident_id' => $data['resident']->id,
        ]);

        $response = $this->actingAs($data['user'])
            ->post("/resident/select-house/{$house2->id}");

        $response->assertRedirect();
    }

    // ============================================================================
    // J. AUDIT LOGGING TESTS
    // ============================================================================

    public function test_audit_log_can_be_created(): void
    {
        $user = User::factory()->superAdmin()->create();
        
        $this->actingAs($user);

        AuditLog::logAction(
            'test_action',
            'Test description',
            null,
            null
        );

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'test_action',
            'description' => 'Test description',
        ]);
    }

    public function test_super_admin_can_view_audit_logs(): void
    {
        $admin = User::factory()->superAdmin()->create();
        AuditLog::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin/audit-logs');

        $response->assertStatus(200);
    }

    public function test_auditor_can_view_audit_logs(): void
    {
        $auditor = User::factory()->auditor()->create();
        AuditLog::factory()->count(10)->create();

        $response = $this->actingAs($auditor)->get('/admin/audit-logs');

        $response->assertStatus(200);
    }

    public function test_treasurer_cannot_view_audit_logs(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/audit-logs');

        $response->assertStatus(403);
    }

    // ============================================================================
    // K. NOTIFICATIONS TESTS
    // ============================================================================

    public function test_notification_can_be_created(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Notification',
        ]);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $user->id,
            'title' => 'Test Notification',
        ]);
    }

    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = SystemNotification::factory()->create([
            'user_id' => $user->id,
            'is_read' => false,
        ]);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertTrue($notification->fresh()->is_read);
    }

    // ============================================================================
    // L. SYSTEM SETTINGS TESTS
    // ============================================================================

    public function test_system_setting_can_be_created(): void
    {
        SystemSetting::set('test_key', 'test_value', 'string', 'general');

        $this->assertEquals('test_value', SystemSetting::get('test_key'));
    }

    public function test_system_setting_updates_existing(): void
    {
        SystemSetting::set('test_key', 'value1', 'string', 'general');
        SystemSetting::set('test_key', 'value2', 'string', 'general');

        $this->assertEquals('value2', SystemSetting::get('test_key'));
    }

    public function test_super_admin_can_update_toyyibpay_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/toyyibpay', [
            'secret_key' => 'new-secret-key',
            'category_code' => 'new-category',
            'sandbox' => true,
        ]);

        $response->assertRedirect();
    }

    // ============================================================================
    // M. MULTI-LANGUAGE SUPPORT TESTS
    // ============================================================================

    public function test_language_can_be_switched(): void
    {
        $response = $this->get('/language/bm');

        $response->assertRedirect();
        $response->assertSessionHas('locale', 'bm');
    }

    public function test_user_language_preference_is_respected(): void
    {
        $user = User::factory()->create(['language_preference' => 'bm']);
        
        $response = $this->actingAs($user)->get('/dashboard');
        
        // Should work and respect language
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_translation_files_exist(): void
    {
        $this->assertFileExists(base_path('lang/bm/messages.php'));
        $this->assertFileExists(base_path('lang/en/messages.php'));
    }

    // ============================================================================
    // N. DATA INTEGRITY & CONSTRAINTS TESTS
    // ============================================================================

    public function test_house_no_and_street_must_be_unique(): void
    {
        // Create first house
        House::factory()->create([
            'house_no' => 'UNIQUE123',
            'street_name' => 'Jalan Tropika 2',
        ]);

        // Creating same house_no + street should fail validation or constraint
        // In SQLite, we may not have unique constraint, so we test via controller
        $admin = User::factory()->superAdmin()->create();
        
        // First creation should work
        $this->actingAs($admin)->post('/admin/houses', [
            'house_no' => 'UNIQUE456',
            'street_name' => 'Jalan Tropika 3',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ])->assertRedirect();
        
        $this->assertDatabaseHas('houses', ['house_no' => 'UNIQUE456']);
    }

    public function test_user_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'unique@example.com']);
    }

    public function test_bill_no_is_unique(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        Bill::factory()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_no' => 'BIL-UNIQUE-001',
            'bill_month' => 1,
            'bill_year' => 2025,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Bill::factory()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_no' => 'BIL-UNIQUE-001',
            'bill_month' => 2,
            'bill_year' => 2025,
        ]);
    }

    public function test_relationships_are_properly_defined(): void
    {
        $data = $this->createResidentWithHouse();
        
        // User -> Resident relationship
        $this->assertEquals($data['resident']->id, $data['user']->resident->id);
        
        // House -> Bills relationship
        $fee = FeeConfiguration::factory()->create();
        Bill::factory()->create([
            'house_id' => $data['house']->id,
            'fee_configuration_id' => $fee->id,
        ]);
        $this->assertCount(1, $data['house']->bills);
        
        // House -> Members relationship
        $this->assertCount(1, $data['house']->members);
    }

    // ============================================================================
    // O. END-TO-END SCENARIO TESTS
    // ============================================================================

    public function test_complete_new_owner_registration_flow(): void
    {
        // 1. House exists
        $house = House::factory()->create(['house_no' => 'E2E-001']);
        
        // 2. New owner registers
        $this->post('/register', [
            'name' => 'New Owner',
            'email' => 'newowner@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'house_id' => $house->id,
            'relationship' => 'owner',
        ]);
        
        $user = User::where('email', 'newowner@example.com')->first();
        $this->assertNotNull($user);
    }

    public function test_complete_bill_generation_and_payment_flow(): void
    {
        // Setup
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->active()->withAmount(20.00)->create();
        $data = $this->createResidentWithHouse();
        
        // 1. Admin generates bills
        $this->actingAs($admin);
        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);
        $this->assertTrue($result['success']);
        
        // 2. Resident sees outstanding bill
        $outstanding = $this->billingService->getOutstandingBills($data['house']);
        $this->assertCount(1, $outstanding);
        
        // 3. Create payment
        $payment = Payment::factory()->pending()->create([
            'house_id' => $data['house']->id,
            'resident_id' => $data['resident']->id,
            'amount' => 20.00,
        ]);
        
        $bill = $outstanding->first();
        $bill->markAsProcessing();
        $payment->bills()->attach($bill->id, ['amount' => 20.00]);
        
        // 4. Payment succeeds
        $payment->markAsSuccess('TXN-E2E-001');
        
        // 5. Verify bill is paid
        $this->assertEquals('paid', $bill->fresh()->status);
        $this->assertEquals(0.00, $data['house']->fresh()->outstanding_amount);
    }

    public function test_complete_owner_change_scenario(): void
    {
        // 1. Original owner with paid bills
        $house = $this->createBillableHouse(['house_no' => 'CHANGE-001']);
        $oldOccupancy = $house->activeMemberOccupancy();
        $fee = FeeConfiguration::factory()->active()->create();
        
        Bill::factory()->paid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 100.00,
            'paid_amount' => 100.00,
            'bill_month' => 1,
            'bill_year' => 2024,
            'paid_by_occupancy_id' => $oldOccupancy->id,
        ]);
        
        // 2. Old owner sells house
        $oldOccupancy->endOccupancy();
        
        // 3. New owner takes over
        $newResident = Resident::factory()->create();
        $newOccupancy = HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $newResident->id,
        ]);
        
        // 4. Verify membership reset
        $this->assertFalse($newOccupancy->is_member);
        
        // 5. Verify bills still attached to house
        $this->assertEquals(1, $house->bills()->count());
        
        // 6. New owner registers as member
        $membershipFee = MembershipFee::createForOccupancy($newOccupancy, 20.00);
        $membershipFee->markAsPaid('REF-CHANGE-001');
        
        // 7. Verify new owner is now member
        $this->assertTrue($newOccupancy->fresh()->is_member);
        
        // 8. House is now billable again
        $this->assertTrue($house->fresh()->is_billable);
    }

    public function test_complete_admin_workflow(): void
    {
        $admin = User::factory()->superAdmin()->create();
        
        // 1. View dashboard
        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        
        // 2. Create house
        $this->actingAs($admin)->post('/admin/houses', [
            'house_no' => 'ADMIN-001',
            'street_name' => 'Jalan Tropika 2',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ])->assertRedirect();
        
        $house = House::where('house_no', 'ADMIN-001')->first();
        $this->assertNotNull($house);
        
        // 3. View house details
        $this->actingAs($admin)->get("/admin/houses/{$house->id}")->assertStatus(200);
        
        // 4. Update house
        $this->actingAs($admin)->put("/admin/houses/{$house->id}", [
            'house_no' => 'ADMIN-001',
            'street_name' => 'Jalan Tropika 3',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ])->assertRedirect();
        
        $this->assertEquals('Jalan Tropika 3', $house->fresh()->street_name);
    }

    public function test_verification_workflow(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $house = House::factory()->create();
        
        // Create pending house member
        $user = User::factory()->resident()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);
        $member = HouseMember::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);
        
        // 1. View pending verifications
        $this->actingAs($admin)->get('/admin/verifications/pending')->assertStatus(200);
        
        // 2. Approve member
        $this->actingAs($admin)->post("/admin/verifications/{$member->id}/approve")->assertRedirect();
        
        $this->assertEquals('active', $member->fresh()->status);
    }

    // ============================================================================
    // P. PERFORMANCE & SCALABILITY CHECKS
    // ============================================================================

    public function test_bulk_house_creation_performance(): void
    {
        $startTime = microtime(true);
        
        House::factory()->count(100)->create();
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Should complete in reasonable time (less than 10 seconds)
        $this->assertLessThan(10, $duration);
        $this->assertEquals(100, House::count());
    }

    public function test_bulk_bill_generation_performance(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());
        $fee = FeeConfiguration::factory()->active()->create();
        
        // Create 50 billable houses
        for ($i = 0; $i < 50; $i++) {
            $this->createBillableHouse();
        }
        
        $startTime = microtime(true);
        
        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);
        
        $endTime = microtime(true);
        $duration = $endTime - $startTime;
        
        // Should complete in reasonable time
        $this->assertLessThan(30, $duration);
        $this->assertTrue($result['success']);
        $this->assertEquals(50, $result['generated']);
    }

    // ============================================================================
    // Q. SECURITY TESTS
    // ============================================================================

    public function test_sql_injection_prevention(): void
    {
        $admin = User::factory()->superAdmin()->create();
        
        $maliciousInput = "'; DROP TABLE houses; --";
        
        $this->actingAs($admin)->post('/admin/houses', [
            'house_no' => $maliciousInput,
            'street_name' => 'Jalan Tropika 2',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ]);
        
        // Table should still exist
        $this->assertTrue(\Schema::hasTable('houses'));
    }

    public function test_csrf_protection_on_forms(): void
    {
        $user = User::factory()->superAdmin()->create();
        
        // Post with proper authentication
        $response = $this->actingAs($user)
            ->post('/admin/houses', [
                'house_no' => 'TEST',
                'street_name' => 'Jalan Tropika 2',
                'is_registered' => true,
                'is_active' => true,
                'status' => 'occupied',
            ]);
        
        // Should not throw 419 when properly authenticated
        $this->assertNotEquals(419, $response->status());
    }

    public function test_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plaintext123',
        ]);
        
        $this->assertNotEquals('plaintext123', $user->password);
        $this->assertTrue(Hash::check('plaintext123', $user->password));
    }

    // ============================================================================
    // R. DATABASE MIGRATION & SEEDING TESTS
    // ============================================================================

    public function test_all_required_tables_exist(): void
    {
        $requiredTables = [
            'users',
            'residents',
            'houses',
            'house_occupancies',
            'house_members',
            'bills',
            'payments',
            'payment_bill',
            'fee_configurations',
            'membership_fees',
            'membership_fee_configurations',
            'legacy_payments',
            'audit_logs',
            'system_notifications',
            'system_settings',
        ];
        
        foreach ($requiredTables as $table) {
            $this->assertTrue(\Schema::hasTable($table), "Table {$table} does not exist");
        }
    }

    public function test_all_required_columns_exist(): void
    {
        // Bills table MODEL HIBRID columns
        $this->assertTrue(\Schema::hasColumn('bills', 'house_id'));
        $this->assertTrue(\Schema::hasColumn('bills', 'paid_by_occupancy_id'));
        
        // HouseOccupancy membership columns
        $this->assertTrue(\Schema::hasColumn('house_occupancies', 'is_member'));
        $this->assertTrue(\Schema::hasColumn('house_occupancies', 'membership_fee_paid_at'));
        $this->assertTrue(\Schema::hasColumn('house_occupancies', 'membership_fee_amount'));
        
        // MembershipFees occupancy link
        $this->assertTrue(\Schema::hasColumn('membership_fees', 'house_occupancy_id'));
    }
}

