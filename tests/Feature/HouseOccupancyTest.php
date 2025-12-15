<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseOccupancyTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. HOUSE OCCUPANCY MODEL TESTS
    // ==========================================

    public function test_occupancy_can_be_created(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();

        $occupancy = HouseOccupancy::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'role' => 'owner',
        ]);

        $this->assertDatabaseHas('house_occupancies', [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'role' => 'owner',
        ]);
    }

    // ==========================================
    // B. OCCUPANCY RELATIONSHIPS TESTS
    // ==========================================

    public function test_occupancy_belongs_to_house(): void
    {
        $house = House::factory()->create();
        $occupancy = HouseOccupancy::factory()->create(['house_id' => $house->id]);

        $this->assertEquals($house->id, $occupancy->house->id);
    }

    public function test_occupancy_belongs_to_resident(): void
    {
        $resident = Resident::factory()->create();
        $occupancy = HouseOccupancy::factory()->create(['resident_id' => $resident->id]);

        $this->assertEquals($resident->id, $occupancy->resident->id);
    }

    // ==========================================
    // C. OCCUPANCY SCOPES TESTS
    // ==========================================

    public function test_active_scope_returns_only_active_occupancies(): void
    {
        $house = House::factory()->create();
        HouseOccupancy::factory()->active()->count(2)->create(['house_id' => $house->id]);
        HouseOccupancy::factory()->ended()->create(['house_id' => $house->id]);

        $this->assertEquals(2, HouseOccupancy::active()->count());
    }

    public function test_owners_scope(): void
    {
        HouseOccupancy::factory()->owner()->count(3)->create();
        HouseOccupancy::factory()->tenant()->count(2)->create();

        $this->assertEquals(3, HouseOccupancy::owners()->count());
    }

    public function test_tenants_scope(): void
    {
        HouseOccupancy::factory()->owner()->count(2)->create();
        HouseOccupancy::factory()->tenant()->count(3)->create();

        $this->assertEquals(3, HouseOccupancy::tenants()->count());
    }

    public function test_payers_scope(): void
    {
        HouseOccupancy::factory()->create(['is_payer' => true]);
        HouseOccupancy::factory()->create(['is_payer' => true]);
        HouseOccupancy::factory()->create(['is_payer' => false]);

        $this->assertEquals(2, HouseOccupancy::payers()->count());
    }

    // ==========================================
    // D. OCCUPANCY STATUS TESTS
    // ==========================================

    public function test_is_active_accessor(): void
    {
        $activeOccupancy = HouseOccupancy::factory()->active()->create();
        $endedOccupancy = HouseOccupancy::factory()->ended()->create();

        $this->assertTrue($activeOccupancy->is_active);
        $this->assertFalse($endedOccupancy->is_active);
    }

    // ==========================================
    // E. PAYER ASSIGNMENT TESTS
    // ==========================================

    public function test_set_payer_for_house(): void
    {
        $house = House::factory()->create();
        $owner = Resident::factory()->create();
        $tenant = Resident::factory()->create();

        // Create owner as payer
        HouseOccupancy::factory()->owner()->create([
            'house_id' => $house->id,
            'resident_id' => $owner->id,
            'is_payer' => true,
        ]);

        // Create tenant
        HouseOccupancy::factory()->tenant()->create([
            'house_id' => $house->id,
            'resident_id' => $tenant->id,
            'is_payer' => false,
        ]);

        // Set tenant as payer
        HouseOccupancy::setPayerForHouse($house, $tenant);

        // Refresh and verify
        $ownerOccupancy = HouseOccupancy::where('house_id', $house->id)
            ->where('resident_id', $owner->id)
            ->first();
        $tenantOccupancy = HouseOccupancy::where('house_id', $house->id)
            ->where('resident_id', $tenant->id)
            ->first();

        $this->assertFalse($ownerOccupancy->is_payer);
        $this->assertTrue($tenantOccupancy->is_payer);
    }

    public function test_only_one_payer_per_house(): void
    {
        $house = House::factory()->create();
        $resident1 = Resident::factory()->create();
        $resident2 = Resident::factory()->create();

        HouseOccupancy::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident1->id,
            'is_payer' => true,
        ]);

        HouseOccupancy::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident2->id,
            'is_payer' => false,
        ]);

        // Set second resident as payer
        HouseOccupancy::setPayerForHouse($house, $resident2);

        // Only one payer should exist
        $payersCount = HouseOccupancy::where('house_id', $house->id)
            ->where('is_payer', true)
            ->count();

        $this->assertEquals(1, $payersCount);
    }

    // ==========================================
    // F. OWNER / TENANT BUSINESS RULES TESTS
    // ==========================================

    public function test_house_can_have_one_active_owner(): void
    {
        $house = House::factory()->create();
        
        $owner1 = Resident::factory()->create();
        $owner2 = Resident::factory()->create();

        // First owner active
        HouseOccupancy::factory()->owner()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $owner1->id,
        ]);

        // Previous owner ended
        HouseOccupancy::factory()->owner()->ended()->create([
            'house_id' => $house->id,
            'resident_id' => $owner2->id,
        ]);

        $activeOwners = HouseOccupancy::where('house_id', $house->id)
            ->owners()
            ->active()
            ->count();

        $this->assertEquals(1, $activeOwners);
    }

    public function test_house_can_have_one_active_tenant(): void
    {
        $house = House::factory()->create();

        $tenant1 = Resident::factory()->create();
        $tenant2 = Resident::factory()->create();

        // Current tenant active
        HouseOccupancy::factory()->tenant()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $tenant1->id,
        ]);

        // Previous tenant ended
        HouseOccupancy::factory()->tenant()->ended()->create([
            'house_id' => $house->id,
            'resident_id' => $tenant2->id,
        ]);

        $activeTenants = HouseOccupancy::where('house_id', $house->id)
            ->tenants()
            ->active()
            ->count();

        $this->assertEquals(1, $activeTenants);
    }

    // ==========================================
    // G. DATE CASTING TESTS
    // ==========================================

    public function test_dates_are_casted_correctly(): void
    {
        $occupancy = HouseOccupancy::factory()->create([
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $occupancy->start_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $occupancy->end_date);
    }
}

