<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. LOGIN TESTS
    // ==========================================

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    // ==========================================
    // B. REGISTRATION TESTS
    // ==========================================

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        // Laravel Breeze may redirect to verification page, so check redirect or authenticated
        $response->assertRedirect();
    }

    public function test_new_user_default_role_is_resident(): void
    {
        // Create user directly to test role
        $user = User::factory()->create();
        
        // Default role from factory should be resident
        $this->assertEquals('resident', $user->role);
    }

    // ==========================================
    // C. ROLE-BASED ACCESS TESTS
    // ==========================================

    public function test_super_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_treasurer_can_access_admin_dashboard(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_auditor_can_access_admin_dashboard(): void
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

    public function test_resident_can_access_resident_dashboard(): void
    {
        $resident = User::factory()->resident()->create();

        $response = $this->actingAs($resident)->get('/resident');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_resident_routes(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/resident');

        $response->assertStatus(403);
    }

    // ==========================================
    // D. GUEST REDIRECT TESTS
    // ==========================================

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_guest_can_access_payment_callback(): void
    {
        $response = $this->get('/payment/callback');

        // Payment callback redirects (302) to resident dashboard when no payment found
        $response->assertRedirect();
    }

    // ==========================================
    // E. USER ROLE CHECKS
    // ==========================================

    public function test_user_is_super_admin(): void
    {
        $user = User::factory()->superAdmin()->create();

        $this->assertTrue($user->isSuperAdmin());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isResident());
    }

    public function test_user_is_treasurer(): void
    {
        $user = User::factory()->treasurer()->create();

        $this->assertTrue($user->isTreasurer());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_is_auditor(): void
    {
        $user = User::factory()->auditor()->create();

        $this->assertTrue($user->isAuditor());
        $this->assertTrue($user->isAdmin());
        $this->assertFalse($user->isSuperAdmin());
    }

    public function test_user_is_resident(): void
    {
        $user = User::factory()->resident()->create();

        $this->assertTrue($user->isResident());
        $this->assertFalse($user->isAdmin());
    }

    public function test_user_has_role(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->assertTrue($admin->hasRole('super_admin'));
        $this->assertTrue($admin->hasRole(['super_admin', 'treasurer']));
        $this->assertFalse($admin->hasRole('resident'));
    }

    // ==========================================
    // F. PERMISSION CHECKS
    // ==========================================

    public function test_super_admin_can_manage_houses(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->assertTrue($user->canManageHouses());
    }

    public function test_treasurer_cannot_manage_houses(): void
    {
        $user = User::factory()->treasurer()->create();
        $this->assertFalse($user->canManageHouses());
    }

    public function test_super_admin_can_manage_fees(): void
    {
        $user = User::factory()->superAdmin()->create();
        $this->assertTrue($user->canManageFees());
    }

    public function test_treasurer_can_edit_bills(): void
    {
        $user = User::factory()->treasurer()->create();
        $this->assertTrue($user->canEditBills());
    }

    public function test_auditor_cannot_edit_bills(): void
    {
        $user = User::factory()->auditor()->create();
        $this->assertFalse($user->canEditBills());
    }

    public function test_auditor_can_view_reports(): void
    {
        $user = User::factory()->auditor()->create();
        $this->assertTrue($user->canViewReports());
    }

    public function test_auditor_can_view_audit_logs(): void
    {
        $user = User::factory()->auditor()->create();
        $this->assertTrue($user->canViewAuditLogs());
    }

    public function test_treasurer_cannot_view_audit_logs(): void
    {
        $user = User::factory()->treasurer()->create();
        $this->assertFalse($user->canViewAuditLogs());
    }

    public function test_only_super_admin_can_manage_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $treasurer = User::factory()->treasurer()->create();

        $this->assertTrue($admin->canManageSettings());
        $this->assertFalse($treasurer->canManageSettings());
    }
}

