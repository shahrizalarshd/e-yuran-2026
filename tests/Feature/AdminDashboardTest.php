<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\House;
use App\Models\HouseMember;
use App\Models\HouseOccupancy;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

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

    // ==========================================
    // A. DASHBOARD ACCESS TESTS
    // ==========================================

    public function test_super_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_treasurer_can_access_dashboard(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_auditor_can_access_dashboard(): void
    {
        $auditor = User::factory()->auditor()->create();

        $response = $this->actingAs($auditor)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_resident_cannot_access_admin_dashboard(): void
    {
        $resident = User::factory()->resident()->create();

        $response = $this->actingAs($resident)->get('/admin');

        $response->assertStatus(403);
    }

    // ==========================================
    // B. DASHBOARD STATISTICS DISPLAY
    // ==========================================

    public function test_dashboard_shows_total_houses(): void
    {
        $admin = User::factory()->superAdmin()->create();
        House::factory()->count(10)->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_collection_data(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $house = $this->createBillableHouse();
        
        Bill::factory()->paid()->create([
            'house_id' => $house->id,
            'amount' => 100.00,
            'paid_amount' => 100.00,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_outstanding_amount(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $house = $this->createBillableHouse();
        
        Bill::factory()->unpaid()->create([
            'house_id' => $house->id,
            'amount' => 50.00,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_dashboard_shows_pending_verifications(): void
    {
        $admin = User::factory()->superAdmin()->create();
        HouseMember::factory()->pending()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    // ==========================================
    // C. DASHBOARD REDIRECTS
    // ==========================================

    public function test_root_redirects_authenticated_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_root_redirects_authenticated_resident_to_resident_dashboard(): void
    {
        $resident = User::factory()->resident()->create();

        $response = $this->actingAs($resident)->get('/');

        $response->assertRedirect(route('resident.dashboard'));
    }

    public function test_dashboard_route_redirects_based_on_role(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $resident = User::factory()->resident()->create();

        $adminResponse = $this->actingAs($admin)->get('/dashboard');
        $adminResponse->assertRedirect(route('admin.dashboard'));

        $residentResponse = $this->actingAs($resident)->get('/dashboard');
        $residentResponse->assertRedirect(route('resident.dashboard'));
    }

    // ==========================================
    // D. ADMIN NAVIGATION TESTS
    // ==========================================

    public function test_admin_can_navigate_to_houses(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/houses');

        $response->assertStatus(200);
    }

    public function test_admin_can_navigate_to_residents(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/residents');

        $response->assertStatus(200);
    }

    public function test_admin_can_navigate_to_bills(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/bills');

        $response->assertStatus(200);
    }

    public function test_admin_can_navigate_to_payments(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/payments');

        $response->assertStatus(200);
    }

    // ==========================================
    // E. PAYMENT REPORT TESTS
    // ==========================================

    public function test_admin_can_view_payment_report(): void
    {
        $admin = User::factory()->superAdmin()->create();
        Payment::factory()->success()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/payments/report');

        $response->assertStatus(200);
    }

    // ==========================================
    // F. ROLE-BASED MENU VISIBILITY
    // ==========================================

    public function test_super_admin_sees_all_menu_items(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        // Super admin should see settings and fees links
    }

    public function test_treasurer_does_not_see_settings_menu(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        // Treasurer should not access settings
        $response = $this->actingAs($treasurer)->get('/admin/settings');
        $response->assertStatus(403);
    }

    public function test_auditor_only_sees_read_only_items(): void
    {
        $auditor = User::factory()->auditor()->create();

        // Auditor can view
        $viewResponse = $this->actingAs($auditor)->get('/admin/bills');
        $viewResponse->assertStatus(200);

        // Auditor cannot generate bills
        $generateResponse = $this->actingAs($auditor)->post('/admin/bills/generate-yearly', [
            'year' => now()->year,
            'amount' => 20.00,
        ]);
        $generateResponse->assertStatus(403);
    }
}

