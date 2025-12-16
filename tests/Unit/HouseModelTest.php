<?php

namespace Tests\Unit;

use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. UNIT TESTS FOR HOUSE MODEL (MODEL HIBRID)
    // ==========================================

    /**
     * MODEL HIBRID: is_member/is_billable = true bila ada occupancy aktif dengan is_member = true
     */
    public function test_is_member_returns_true_when_has_active_member_occupancy(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        // Create active member occupancy
        HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertTrue($house->fresh()->is_member);
        $this->assertTrue($house->fresh()->is_billable);
    }

    /**
     * MODEL HIBRID: is_member = false bila tiada occupancy aktif dengan is_member = true
     */
    public function test_is_member_returns_false_when_no_member_occupancy(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        // Create active but NOT member occupancy
        HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertFalse($house->fresh()->is_member);
        $this->assertFalse($house->fresh()->is_billable);
    }

    /**
     * MODEL HIBRID: is_member = false bila occupancy sudah ended
     */
    public function test_is_member_returns_false_when_occupancy_ended(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        // Create ended member occupancy
        HouseOccupancy::factory()->member()->ended()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertFalse($house->fresh()->is_member);
    }

    public function test_full_address_accessor(): void
    {
        $house = new House([
            'house_no' => '123',
            'street_name' => 'Jalan Tropika',
        ]);

        $this->assertEquals('123, Jalan Tropika', $house->full_address);
    }

    /**
     * MODEL HIBRID: Test activeMemberOccupancy helper
     */
    public function test_active_member_occupancy_returns_correct_occupancy(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();
        
        $occupancy = HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $result = $house->fresh()->activeMemberOccupancy();

        $this->assertNotNull($result);
        $this->assertEquals($occupancy->id, $result->id);
    }

    /**
     * MODEL HIBRID: Test billable scope
     */
    public function test_billable_scope_returns_houses_with_active_members(): void
    {
        // House with active member
        $houseWithMember = House::factory()->create();
        HouseOccupancy::factory()->activeMember()->create([
            'house_id' => $houseWithMember->id,
        ]);

        // House without member
        $houseWithoutMember = House::factory()->create();
        HouseOccupancy::factory()->active()->notMember()->create([
            'house_id' => $houseWithoutMember->id,
        ]);

        // House with no occupancy
        $houseEmpty = House::factory()->create();

        $billableHouses = House::billable()->get();

        $this->assertCount(1, $billableHouses);
        $this->assertEquals($houseWithMember->id, $billableHouses->first()->id);
    }
}
