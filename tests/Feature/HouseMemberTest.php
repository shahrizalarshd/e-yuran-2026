<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\HouseMember;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseMemberTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. HOUSE MEMBER MODEL TESTS
    // ==========================================

    public function test_house_member_can_be_created(): void
    {
        $house = House::factory()->create();
        $resident = Resident::factory()->create();

        $member = HouseMember::factory()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'relationship' => 'owner',
        ]);

        $this->assertDatabaseHas('house_members', [
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'relationship' => 'owner',
        ]);
    }

    // ==========================================
    // B. HOUSE MEMBER RELATIONSHIPS TESTS
    // ==========================================

    public function test_member_belongs_to_house(): void
    {
        $house = House::factory()->create();
        $member = HouseMember::factory()->create(['house_id' => $house->id]);

        $this->assertEquals($house->id, $member->house->id);
    }

    public function test_member_belongs_to_resident(): void
    {
        $resident = Resident::factory()->create();
        $member = HouseMember::factory()->create(['resident_id' => $resident->id]);

        $this->assertEquals($resident->id, $member->resident->id);
    }

    public function test_member_belongs_to_approver(): void
    {
        $approver = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->create([
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'status' => 'active',
        ]);

        $this->assertEquals($approver->id, $member->approver->id);
    }

    // ==========================================
    // C. HOUSE MEMBER SCOPES TESTS
    // ==========================================

    public function test_pending_scope(): void
    {
        HouseMember::factory()->pending()->count(3)->create();
        HouseMember::factory()->active()->count(2)->create();

        $this->assertEquals(3, HouseMember::pending()->count());
    }

    public function test_active_scope(): void
    {
        HouseMember::factory()->active()->count(4)->create();
        HouseMember::factory()->pending()->create();
        HouseMember::factory()->inactive()->create();

        $this->assertEquals(4, HouseMember::active()->count());
    }

    // ==========================================
    // D. MEMBER APPROVAL TESTS
    // ==========================================

    public function test_member_can_be_approved(): void
    {
        $approver = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->pending()->create();

        $member->approve($approver);

        $this->assertEquals('active', $member->fresh()->status);
        $this->assertEquals($approver->id, $member->fresh()->approved_by);
        $this->assertNotNull($member->fresh()->approved_at);
    }

    public function test_member_can_be_rejected(): void
    {
        $approver = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->pending()->create();

        $member->reject($approver, 'Verification failed');

        $this->assertEquals('rejected', $member->fresh()->status);
        $this->assertEquals('Verification failed', $member->fresh()->rejection_reason);
    }

    public function test_member_can_be_deactivated(): void
    {
        $member = HouseMember::factory()->active()->create();

        $member->deactivate();

        $this->assertEquals('inactive', $member->fresh()->status);
    }

    // ==========================================
    // E. MEMBER STATUS BADGE TESTS
    // ==========================================

    public function test_pending_status_badge_class(): void
    {
        $member = HouseMember::factory()->pending()->create();

        $this->assertEquals('bg-yellow-100 text-yellow-800', $member->status_badge_class);
    }

    public function test_active_status_badge_class(): void
    {
        $member = HouseMember::factory()->active()->create();

        $this->assertEquals('bg-green-100 text-green-800', $member->status_badge_class);
    }

    public function test_rejected_status_badge_class(): void
    {
        $member = HouseMember::factory()->rejected()->create();

        $this->assertEquals('bg-red-100 text-red-800', $member->status_badge_class);
    }

    public function test_inactive_status_badge_class(): void
    {
        $member = HouseMember::factory()->inactive()->create();

        $this->assertEquals('bg-gray-100 text-gray-800', $member->status_badge_class);
    }

    // ==========================================
    // F. MEMBER PERMISSIONS TESTS
    // ==========================================

    public function test_owner_has_full_permissions(): void
    {
        $member = HouseMember::factory()->owner()->create();

        $this->assertTrue($member->can_view_bills);
        $this->assertTrue($member->can_pay);
    }

    public function test_child_has_view_only_permission(): void
    {
        $member = HouseMember::factory()->child()->create();

        $this->assertTrue($member->can_view_bills);
        $this->assertFalse($member->can_pay);
    }

    // ==========================================
    // G. ADMIN VERIFICATION TESTS
    // ==========================================

    public function test_super_admin_can_approve_member(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->pending()->create();

        $response = $this->actingAs($admin)->post("/admin/verifications/{$member->id}/approve");

        $response->assertRedirect();
        $this->assertEquals('active', $member->fresh()->status);
    }

    public function test_super_admin_can_reject_member(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->pending()->create();

        $response = $this->actingAs($admin)->post("/admin/verifications/{$member->id}/reject", [
            'rejection_reason' => 'Invalid documents',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $member->fresh()->status);
    }

    public function test_treasurer_can_approve_member(): void
    {
        $treasurer = User::factory()->treasurer()->create();
        $member = HouseMember::factory()->pending()->create();

        $response = $this->actingAs($treasurer)->post("/admin/verifications/{$member->id}/approve");

        $response->assertRedirect();
        $this->assertEquals('active', $member->fresh()->status);
    }

    public function test_treasurer_can_reject_member(): void
    {
        $treasurer = User::factory()->treasurer()->create();
        $member = HouseMember::factory()->pending()->create();

        $response = $this->actingAs($treasurer)->post("/admin/verifications/{$member->id}/reject", [
            'rejection_reason' => 'Invalid documents',
        ]);

        $response->assertRedirect();
        $this->assertEquals('rejected', $member->fresh()->status);
    }

    public function test_auditor_cannot_approve_member(): void
    {
        $auditor = User::factory()->auditor()->create();
        $member = HouseMember::factory()->pending()->create();

        $response = $this->actingAs($auditor)->post("/admin/verifications/{$member->id}/approve");

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_member_permissions(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $member = HouseMember::factory()->active()->create([
            'can_view_bills' => true,
            'can_pay' => false,
        ]);

        $response = $this->actingAs($admin)->patch("/admin/members/{$member->id}/permissions", [
            'can_view_bills' => true,
            'can_pay' => true,
        ]);

        $response->assertRedirect();
        $this->assertTrue($member->fresh()->can_pay);
    }

    // ==========================================
    // H. RELATIONSHIP TYPE TESTS
    // ==========================================

    public function test_all_relationship_types(): void
    {
        $relationships = ['owner', 'spouse', 'child', 'family', 'tenant'];

        foreach ($relationships as $relationship) {
            $member = HouseMember::factory()->create(['relationship' => $relationship]);
            $this->assertEquals($relationship, $member->relationship);
        }
    }
}

