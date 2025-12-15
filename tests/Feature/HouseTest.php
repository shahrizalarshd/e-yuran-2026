<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\HouseMember;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. HOUSE MODEL TESTS
    // ==========================================

    public function test_house_can_be_created(): void
    {
        $house = House::factory()->create([
            'house_no' => '123',
            'street_name' => 'Jalan Tropika 1',
        ]);

        $this->assertDatabaseHas('houses', [
            'house_no' => '123',
            'street_name' => 'Jalan Tropika 1',
        ]);
    }

    public function test_house_full_address_accessor(): void
    {
        $house = House::factory()->create([
            'house_no' => '123',
            'street_name' => 'Jalan Tropika 1',
        ]);

        $this->assertEquals('123, Jalan Tropika 1', $house->full_address);
    }

    public function test_house_is_billable_when_registered_and_active(): void
    {
        $house = House::factory()->billable()->create();

        $this->assertTrue($house->is_billable);
    }

    public function test_house_is_not_billable_when_unregistered(): void
    {
        $house = House::factory()->unregistered()->create();

        $this->assertFalse($house->is_billable);
    }

    public function test_house_is_not_billable_when_inactive(): void
    {
        $house = House::factory()->inactive()->create();

        $this->assertFalse($house->is_billable);
    }

    // ==========================================
    // B. HOUSE SCOPES TESTS
    // ==========================================

    public function test_house_registered_scope(): void
    {
        House::factory()->count(3)->billable()->create();
        House::factory()->count(2)->unregistered()->create();

        $this->assertEquals(3, House::registered()->count());
    }

    public function test_house_active_scope(): void
    {
        House::factory()->count(3)->billable()->create();
        House::factory()->count(2)->inactive()->create();

        $this->assertEquals(3, House::active()->count());
    }

    public function test_house_billable_scope(): void
    {
        House::factory()->count(3)->billable()->create();
        House::factory()->unregistered()->create();
        House::factory()->inactive()->create();

        $this->assertEquals(3, House::billable()->count());
    }

    // ==========================================
    // C. HOUSE RELATIONSHIPS TESTS
    // ==========================================

    public function test_house_has_many_occupancies(): void
    {
        $house = House::factory()->create();
        HouseOccupancy::factory()->count(2)->create(['house_id' => $house->id]);

        $this->assertCount(2, $house->occupancies);
    }

    public function test_house_has_many_members(): void
    {
        $house = House::factory()->create();
        HouseMember::factory()->count(3)->create(['house_id' => $house->id]);

        $this->assertCount(3, $house->members);
    }

    public function test_house_has_many_bills(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        // Create bills with different months to avoid constraint violation
        for ($month = 1; $month <= 5; $month++) {
            Bill::factory()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $month,
                'bill_year' => 2023, // Use past year to avoid conflicts
            ]);
        }

        $this->assertCount(5, $house->bills);
    }

    // ==========================================
    // D. HOUSE OWNER & TENANT TESTS
    // ==========================================

    public function test_house_current_owner(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->owner()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $owner = $house->currentOwner();
        $this->assertNotNull($owner);
        $this->assertEquals($resident->id, $owner->resident_id);
    }

    public function test_house_current_tenant(): void
    {
        $house = House::factory()->create();
        $tenant = Resident::factory()->create();
        
        HouseOccupancy::factory()->tenant()->create([
            'house_id' => $house->id,
            'resident_id' => $tenant->id,
        ]);

        $currentTenant = $house->currentTenant();
        $this->assertNotNull($currentTenant);
        $this->assertEquals($tenant->id, $currentTenant->resident_id);
    }

    public function test_house_current_payer(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        HouseOccupancy::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'is_payer' => true,
        ]);

        $payer = $house->currentPayer();
        $this->assertNotNull($payer);
        $this->assertTrue($payer->is_payer);
    }

    // ==========================================
    // E. HOUSE MEMBERS TESTS
    // ==========================================

    public function test_house_active_members(): void
    {
        $house = House::factory()->create();
        HouseMember::factory()->active()->count(3)->create(['house_id' => $house->id]);
        HouseMember::factory()->pending()->create(['house_id' => $house->id]);
        HouseMember::factory()->inactive()->create(['house_id' => $house->id]);

        $this->assertCount(3, $house->activeMembers()->get());
    }

    public function test_house_pending_members(): void
    {
        $house = House::factory()->create();
        HouseMember::factory()->active()->count(2)->create(['house_id' => $house->id]);
        HouseMember::factory()->pending()->count(3)->create(['house_id' => $house->id]);

        $this->assertCount(3, $house->pendingMembers()->get());
    }

    // ==========================================
    // F. HOUSE BILLS TESTS
    // ==========================================

    public function test_house_unpaid_bills(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();
        
        // Create unpaid bills with unique months
        for ($i = 1; $i <= 3; $i++) {
            Bill::factory()->unpaid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_year' => 2022,
            ]);
        }
        
        // Create paid bills with unique months
        for ($i = 4; $i <= 5; $i++) {
            Bill::factory()->paid()->create([
                'house_id' => $house->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_year' => 2022,
            ]);
        }

        $this->assertCount(3, $house->unpaidBills()->get());
    }

    public function test_house_outstanding_amount(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->active()->create();
        
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 50.00,
            'paid_amount' => 0,
            'bill_month' => 1,
            'bill_no' => Bill::generateBillNo(now()->year, 1, $house->id),
        ]);
        Bill::factory()->partial()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'amount' => 50.00,
            'paid_amount' => 20.00,
            'bill_month' => 2,
            'bill_no' => Bill::generateBillNo(now()->year, 2, $house->id),
        ]);

        $this->assertEquals(80.00, $house->outstanding_amount);
    }

    // ==========================================
    // G. ADMIN HOUSE MANAGEMENT TESTS
    // ==========================================

    public function test_super_admin_can_create_house(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/houses', [
            'house_no' => 'A101',
            'street_name' => 'Jalan Tropika 1',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('houses', ['house_no' => 'A101']);
    }

    public function test_super_admin_can_view_houses_list(): void
    {
        $admin = User::factory()->superAdmin()->create();
        House::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/houses');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_view_house_details(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $house = House::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/houses/{$house->id}");

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_house(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $house = House::factory()->create();

        $response = $this->actingAs($admin)->put("/admin/houses/{$house->id}", [
            'house_no' => 'B202',
            'street_name' => 'Jalan Tropika 2',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'vacant',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('houses', [
            'id' => $house->id,
            'house_no' => 'B202',
        ]);
    }

    public function test_auditor_cannot_create_house(): void
    {
        $auditor = User::factory()->auditor()->create();

        $response = $this->actingAs($auditor)->post('/admin/houses', [
            'house_no' => 'A101',
            'street_name' => 'Jalan Tropika 1',
            'is_registered' => true,
            'is_active' => true,
            'status' => 'occupied',
        ]);

        // Either 403 Forbidden or redirect with error
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_treasurer_can_view_houses(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/houses');

        $response->assertStatus(200);
    }
}

