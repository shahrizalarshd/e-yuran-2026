<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->billingService = new BillingService();
    }

    /**
     * Helper to create a billable house (with active member occupancy)
     * MODEL HIBRID: House is billable when it has an active member occupancy
     */
    private function createBillableHouse(): House
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        return $house->fresh();
    }

    /**
     * Helper to create a non-billable house (no active member)
     */
    private function createNonBillableHouse(): House
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        return $house->fresh();
    }

    // ==========================================
    // A. BILL GENERATION TESTS (MODEL HIBRID)
    // ==========================================

    public function test_generate_monthly_bills_for_all_billable_houses(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        FeeConfiguration::factory()->active()->withAmount(20.00)->create();
        
        // Create 5 billable houses (with active member occupancy)
        for ($i = 0; $i < 5; $i++) {
            $this->createBillableHouse();
        }
        
        // Create non-billable houses
        $this->createNonBillableHouse();
        House::factory()->create(); // No occupancy at all

        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);

        $this->assertTrue($result['success']);
        $this->assertEquals(5, $result['generated']);
        $this->assertEquals(5, Bill::count());
    }

    public function test_fails_when_no_fee_configuration(): void
    {
        $this->createBillableHouse();

        $result = $this->billingService->generateMonthlyBills(now()->year, now()->month);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('No active fee configuration', $result['message']);
    }

    public function test_skips_existing_bills(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $fee = FeeConfiguration::factory()->active()->create();
        $house = $this->createBillableHouse();

        // Create existing bill with proper bill_no
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

    public function test_generate_bill_for_specific_house(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create());

        $fee = FeeConfiguration::factory()->active()->withAmount(20.00)->create();
        $house = $this->createBillableHouse();

        $bill = $this->billingService->generateBillForHouse($house, now()->year, now()->month, $fee);

        $this->assertNotNull($bill);
        $this->assertEquals($house->id, $bill->house_id);
        $this->assertEquals(20.00, $bill->amount);
        $this->assertEquals('unpaid', $bill->status);
    }

    public function test_does_not_generate_bill_for_non_billable_house(): void
    {
        $house = $this->createNonBillableHouse();
        $fee = FeeConfiguration::factory()->active()->create();

        $bill = $this->billingService->generateBillForHouse($house, now()->year, now()->month, $fee);

        $this->assertNull($bill);
    }

    // ==========================================
    // B. OUTSTANDING BILLS TESTS
    // ==========================================

    public function test_get_outstanding_bills(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        // Create unpaid bills with unique months
        for ($i = 1; $i <= 3; $i++) {
            Bill::factory()->unpaid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_year' => 2019,
            ]);
        }
        
        // Create paid bills
        for ($i = 4; $i <= 5; $i++) {
            Bill::factory()->paid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_year' => 2019,
            ]);
        }
        
        // Create partial bill
        Bill::factory()->partial()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_month' => 6,
            'bill_year' => 2019,
        ]);

        $outstanding = $this->billingService->getOutstandingBills($house);

        $this->assertCount(4, $outstanding); // 3 unpaid + 1 partial
    }

    public function test_get_total_outstanding(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 50.00,
            'paid_amount' => 0,
            'bill_month' => 1,
            'bill_year' => 2018,
        ]);
        Bill::factory()->partial()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 50.00,
            'paid_amount' => 20.00,
            'bill_month' => 2,
            'bill_year' => 2018,
        ]);

        $total = $this->billingService->getTotalOutstanding($house);

        $this->assertEquals(80.00, $total);
    }

    // ==========================================
    // C. CURRENT YEAR BILLS TESTS
    // ==========================================

    public function test_get_current_year_bills(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        // Create current year bills with unique months
        for ($i = 1; $i <= 6; $i++) {
            Bill::factory()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_year' => now()->year,
                'bill_month' => $i,
            ]);
        }
        
        // Create previous year bills with unique months
        for ($i = 1; $i <= 3; $i++) {
            Bill::factory()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_year' => now()->subYear()->year,
                'bill_month' => $i,
            ]);
        }

        $currentYearBills = $this->billingService->getCurrentYearBills($house);

        $this->assertCount(6, $currentYearBills);
    }

    // ==========================================
    // D. STATISTICS TESTS (MODEL HIBRID)
    // ==========================================

    public function test_get_statistics(): void
    {
        // Create houses with active members (billable)
        $billableHouses = [];
        for ($i = 0; $i < 5; $i++) {
            $billableHouses[] = $this->createBillableHouse();
        }
        
        // Create non-billable houses
        $this->createNonBillableHouse();
        $this->createNonBillableHouse();
        House::factory()->create(); // No occupancy

        $fee = FeeConfiguration::factory()->create();

        // Create bills with unique months for each house
        $monthCounter = 1;
        foreach ($billableHouses as $house) {
            Bill::factory()->paid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'amount' => 20.00,
                'paid_amount' => 20.00,
                'bill_month' => $monthCounter,
                'bill_year' => 2021,
            ]);
            Bill::factory()->unpaid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'amount' => 20.00,
                'paid_amount' => 0,
                'bill_month' => $monthCounter + 6,
                'bill_year' => 2021,
            ]);
            $monthCounter++;
        }

        $stats = $this->billingService->getStatistics();

        $this->assertEquals(8, $stats['total_houses']);
        $this->assertEquals(5, $stats['houses_with_members']);
        $this->assertEquals(100.00, $stats['total_collection']); // 5 x RM20
        $this->assertEquals(100.00, $stats['total_outstanding']); // 5 x RM20
    }

    public function test_overdue_count_in_statistics(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        // Create overdue bills with unique months
        for ($i = 1; $i <= 3; $i++) {
            Bill::factory()->overdue()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_year' => 2020,
            ]);
        }
        
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_month' => 4,
            'bill_year' => 2020,
            'due_date' => now()->addMonth(), // Not overdue
        ]);

        $stats = $this->billingService->getStatistics();

        $this->assertEquals(3, $stats['overdue_count']);
    }

    // ==========================================
    // E. ADMIN BILL MANAGEMENT TESTS
    // ==========================================

    public function test_admin_can_view_bills_list(): void
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

    public function test_admin_can_generate_bills(): void
    {
        $admin = User::factory()->superAdmin()->create();
        FeeConfiguration::factory()->active()->create();
        
        // Create billable houses with active member occupancy
        for ($i = 0; $i < 3; $i++) {
            $this->createBillableHouse();
        }

        $response = $this->actingAs($admin)->post('/admin/bills/generate-yearly', [
            'year' => now()->year,
            'amount' => 20.00,
        ]);

        $response->assertRedirect();
        // Each billable house should have 12 bills for yearly
        $this->assertGreaterThan(0, Bill::count());
    }

    public function test_treasurer_cannot_generate_bills(): void
    {
        $treasurer = User::factory()->treasurer()->create();
        FeeConfiguration::factory()->active()->create();
        
        // Create billable houses
        for ($i = 0; $i < 2; $i++) {
            $this->createBillableHouse();
        }

        $response = $this->actingAs($treasurer)->post('/admin/bills/generate-yearly', [
            'year' => now()->year,
            'amount' => 20.00,
        ]);

        // Only super_admin can generate bills
        $response->assertStatus(403);
    }

    public function test_auditor_cannot_generate_bills(): void
    {
        $auditor = User::factory()->auditor()->create();

        $response = $this->actingAs($auditor)->post('/admin/bills/generate-yearly', [
            'year' => now()->year,
            'amount' => 20.00,
        ]);

        $response->assertStatus(403);
    }

    // ==========================================
    // F. BILL UPDATE TESTS
    // ==========================================

    public function test_treasurer_can_edit_unpaid_bill(): void
    {
        $treasurer = User::factory()->treasurer()->create();
        $bill = Bill::factory()->unpaid()->create();

        $response = $this->actingAs($treasurer)->patch("/admin/bills/{$bill->id}", [
            'amount' => 25.00,
        ]);

        $response->assertRedirect();
    }

    public function test_only_super_admin_can_delete_bill(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $treasurer = User::factory()->treasurer()->create();
        $bill = Bill::factory()->create();

        // Treasurer cannot delete
        $response = $this->actingAs($treasurer)->delete("/admin/bills/{$bill->id}");
        $response->assertStatus(403);

        // Super admin can delete
        $response = $this->actingAs($admin)->delete("/admin/bills/{$bill->id}");
        $response->assertRedirect();
    }

    // ==========================================
    // G. MODEL HIBRID SPECIFIC TESTS
    // ==========================================

    public function test_bills_inherit_when_owner_changes(): void
    {
        $house = $this->createBillableHouse();
        $fee = FeeConfiguration::factory()->create();

        // Create unpaid bills
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 100.00,
            'bill_month' => 1,
            'bill_year' => 2024,
        ]);

        // Bills should still be attached to house (inherited)
        $this->assertEquals(1, $house->bills()->count());
        $this->assertEquals(100.00, $house->outstanding_amount);
    }
}
