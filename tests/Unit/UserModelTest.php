<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    // ==========================================
    // A. ROLE CHECK TESTS
    // ==========================================

    public function test_is_super_admin(): void
    {
        $user = new User(['role' => 'super_admin']);
        
        $this->assertTrue($user->isSuperAdmin());
        $this->assertFalse($user->isTreasurer());
        $this->assertFalse($user->isAuditor());
        $this->assertFalse($user->isResident());
    }

    public function test_is_treasurer(): void
    {
        $user = new User(['role' => 'treasurer']);
        
        $this->assertTrue($user->isTreasurer());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isAuditor());
        $this->assertFalse($user->isResident());
    }

    public function test_is_auditor(): void
    {
        $user = new User(['role' => 'auditor']);
        
        $this->assertTrue($user->isAuditor());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isTreasurer());
        $this->assertFalse($user->isResident());
    }

    public function test_is_resident(): void
    {
        $user = new User(['role' => 'resident']);
        
        $this->assertTrue($user->isResident());
        $this->assertFalse($user->isSuperAdmin());
        $this->assertFalse($user->isTreasurer());
        $this->assertFalse($user->isAuditor());
    }

    // ==========================================
    // B. IS ADMIN TESTS
    // ==========================================

    public function test_super_admin_is_admin(): void
    {
        $user = new User(['role' => 'super_admin']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_treasurer_is_admin(): void
    {
        $user = new User(['role' => 'treasurer']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_auditor_is_admin(): void
    {
        $user = new User(['role' => 'auditor']);
        $this->assertTrue($user->isAdmin());
    }

    public function test_resident_is_not_admin(): void
    {
        $user = new User(['role' => 'resident']);
        $this->assertFalse($user->isAdmin());
    }

    // ==========================================
    // C. HAS ROLE TESTS
    // ==========================================

    public function test_has_role_with_string(): void
    {
        $user = new User(['role' => 'super_admin']);
        
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertFalse($user->hasRole('resident'));
    }

    public function test_has_role_with_array(): void
    {
        $user = new User(['role' => 'treasurer']);
        
        $this->assertTrue($user->hasRole(['super_admin', 'treasurer']));
        $this->assertFalse($user->hasRole(['super_admin', 'auditor']));
    }

    // ==========================================
    // D. PERMISSION TESTS
    // ==========================================

    public function test_super_admin_can_manage_houses(): void
    {
        $user = new User(['role' => 'super_admin']);
        $this->assertTrue($user->canManageHouses());
    }

    public function test_treasurer_cannot_manage_houses(): void
    {
        $user = new User(['role' => 'treasurer']);
        $this->assertFalse($user->canManageHouses());
    }

    public function test_super_admin_can_manage_users(): void
    {
        $user = new User(['role' => 'super_admin']);
        $this->assertTrue($user->canManageUsers());
    }

    public function test_super_admin_can_manage_fees(): void
    {
        $user = new User(['role' => 'super_admin']);
        $this->assertTrue($user->canManageFees());
    }

    public function test_treasurer_can_edit_bills(): void
    {
        $user = new User(['role' => 'treasurer']);
        $this->assertTrue($user->canEditBills());
    }

    public function test_auditor_cannot_edit_bills(): void
    {
        $user = new User(['role' => 'auditor']);
        $this->assertFalse($user->canEditBills());
    }

    public function test_all_admins_can_view_reports(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $treasurer = new User(['role' => 'treasurer']);
        $auditor = new User(['role' => 'auditor']);
        $resident = new User(['role' => 'resident']);
        
        $this->assertTrue($superAdmin->canViewReports());
        $this->assertTrue($treasurer->canViewReports());
        $this->assertTrue($auditor->canViewReports());
        $this->assertFalse($resident->canViewReports());
    }

    public function test_only_super_admin_and_auditor_can_view_audit_logs(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $treasurer = new User(['role' => 'treasurer']);
        $auditor = new User(['role' => 'auditor']);
        
        $this->assertTrue($superAdmin->canViewAuditLogs());
        $this->assertTrue($auditor->canViewAuditLogs());
        $this->assertFalse($treasurer->canViewAuditLogs());
    }

    public function test_only_super_admin_can_manage_settings(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $treasurer = new User(['role' => 'treasurer']);
        $auditor = new User(['role' => 'auditor']);
        
        $this->assertTrue($superAdmin->canManageSettings());
        $this->assertFalse($treasurer->canManageSettings());
        $this->assertFalse($auditor->canManageSettings());
    }

    // ==========================================
    // E. ROLE BADGE CLASS TESTS
    // ==========================================

    public function test_super_admin_role_badge_class(): void
    {
        $user = new User(['role' => 'super_admin']);
        $this->assertEquals('bg-purple-100 text-purple-800', $user->role_badge_class);
    }

    public function test_treasurer_role_badge_class(): void
    {
        $user = new User(['role' => 'treasurer']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $user->role_badge_class);
    }

    public function test_auditor_role_badge_class(): void
    {
        $user = new User(['role' => 'auditor']);
        $this->assertEquals('bg-blue-100 text-blue-800', $user->role_badge_class);
    }

    public function test_resident_role_badge_class(): void
    {
        $user = new User(['role' => 'resident']);
        $this->assertEquals('bg-green-100 text-green-800', $user->role_badge_class);
    }

}

