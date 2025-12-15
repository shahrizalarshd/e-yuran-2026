<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\HouseMember;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. RESIDENT MODEL TESTS
    // ==========================================

    public function test_resident_can_be_created(): void
    {
        $resident = Resident::factory()->create([
            'name' => 'Ahmad bin Ali',
            'email' => 'ahmad@example.com',
            'phone' => '0123456789',
        ]);

        $this->assertDatabaseHas('residents', [
            'name' => 'Ahmad bin Ali',
            'email' => 'ahmad@example.com',
        ]);
    }

    public function test_resident_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $resident->user->id);
    }

    public function test_resident_can_exist_without_user(): void
    {
        $resident = Resident::factory()->withoutUser()->create();

        $this->assertNull($resident->user_id);
    }

    // ==========================================
    // B. RESIDENT OCCUPANCY TESTS
    // ==========================================

    public function test_resident_has_many_occupancies(): void
    {
        $resident = Resident::factory()->create();
        HouseOccupancy::factory()->count(2)->create(['resident_id' => $resident->id]);

        $this->assertCount(2, $resident->occupancies);
    }

    public function test_resident_current_occupancy(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseOccupancy::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'end_date' => null,
        ]);

        $currentOccupancy = $resident->currentOccupancy();
        $this->assertNotNull($currentOccupancy);
        $this->assertEquals($house->id, $currentOccupancy->house_id);
    }

    public function test_resident_with_active_occupancy_scope(): void
    {
        $activeResident = Resident::factory()->create();
        $inactiveResident = Resident::factory()->create();
        
        HouseOccupancy::factory()->active()->create(['resident_id' => $activeResident->id]);
        HouseOccupancy::factory()->ended()->create(['resident_id' => $inactiveResident->id]);

        $this->assertEquals(1, Resident::withActiveOccupancy()->count());
    }

    // ==========================================
    // C. RESIDENT MEMBERSHIP TESTS
    // ==========================================

    public function test_resident_has_many_house_memberships(): void
    {
        $resident = Resident::factory()->create();
        HouseMember::factory()->count(2)->create(['resident_id' => $resident->id]);

        $this->assertCount(2, $resident->houseMemberships);
    }

    public function test_resident_active_memberships(): void
    {
        $resident = Resident::factory()->create();
        HouseMember::factory()->active()->count(2)->create(['resident_id' => $resident->id]);
        HouseMember::factory()->inactive()->create(['resident_id' => $resident->id]);

        $this->assertCount(2, $resident->activeMemberships()->get());
    }

    public function test_resident_houses_accessor(): void
    {
        $resident = Resident::factory()->create();
        $house1 = House::factory()->create();
        $house2 = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house1->id,
            'resident_id' => $resident->id,
        ]);
        HouseMember::factory()->active()->create([
            'house_id' => $house2->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertCount(2, $resident->houses);
    }

    // ==========================================
    // D. RESIDENT PERMISSION TESTS
    // ==========================================

    public function test_resident_can_view_bills_for_house(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'can_view_bills' => true,
        ]);

        $this->assertTrue($resident->canViewBillsFor($house));
    }

    public function test_resident_cannot_view_bills_without_permission(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'can_view_bills' => false,
        ]);

        $this->assertFalse($resident->canViewBillsFor($house));
    }

    public function test_resident_can_pay_for_house(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'can_pay' => true,
        ]);

        $this->assertTrue($resident->canPayFor($house));
    }

    public function test_resident_cannot_pay_without_permission(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseMember::factory()->viewOnly()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $this->assertFalse($resident->canPayFor($house));
    }

    public function test_pending_member_cannot_view_bills(): void
    {
        $resident = Resident::factory()->create();
        $house = House::factory()->create();
        
        HouseMember::factory()->pending()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'can_view_bills' => true,
        ]);

        $this->assertFalse($resident->canViewBillsFor($house));
    }

    // ==========================================
    // E. LANGUAGE PREFERENCE TESTS
    // ==========================================

    public function test_resident_bm_language_preference(): void
    {
        $resident = Resident::factory()->bm()->create();

        $this->assertEquals('bm', $resident->language_preference);
    }

    public function test_resident_en_language_preference(): void
    {
        $resident = Resident::factory()->en()->create();

        $this->assertEquals('en', $resident->language_preference);
    }

    // ==========================================
    // F. ADMIN RESIDENT MANAGEMENT TESTS
    // ==========================================

    public function test_admin_can_view_residents_list(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Resident::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/residents');

        $response->assertStatus(200);
    }

    public function test_admin_can_view_resident_details(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $resident = Resident::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/residents/{$resident->id}");

        $response->assertStatus(200);
    }

    public function test_admin_can_view_pending_verifications(): void
    {
        $admin = User::factory()->superAdmin()->create();
        HouseMember::factory()->pending()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/verifications/pending');

        $response->assertStatus(200);
    }
}

