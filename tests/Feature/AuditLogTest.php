<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\House;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. AUDIT LOG MODEL TESTS
    // ==========================================

    public function test_audit_log_can_be_created(): void
    {
        $user = User::factory()->create();

        $log = AuditLog::factory()->create([
            'user_id' => $user->id,
            'action' => 'test_action',
            'description' => 'Test description',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'test_action',
        ]);
    }

    // ==========================================
    // B. AUDIT LOG RELATIONSHIPS
    // ==========================================

    public function test_audit_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $log = AuditLog::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $log->user->id);
    }

    // ==========================================
    // C. AUDIT LOG HELPER METHODS
    // ==========================================

    public function test_log_static_method(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();
        
        $log = AuditLog::log(
            'custom_action',
            $house,
            ['old' => 'value'],
            ['new' => 'value'],
            'Custom description'
        );

        $this->assertEquals('custom_action', $log->action);
        $this->assertEquals(House::class, $log->model_type);
        $this->assertEquals($house->id, $log->model_id);
        $this->assertEquals('Custom description', $log->description);
    }

    public function test_log_create(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();
        
        $log = AuditLog::logCreate($house, 'House created');

        $this->assertEquals('create', $log->action);
        $this->assertNull($log->old_values);
        $this->assertNotNull($log->new_values);
    }

    public function test_log_update(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create(['house_no' => 'A1']);
        $oldValues = $house->toArray();
        
        $house->update(['house_no' => 'A2']);
        
        $log = AuditLog::logUpdate($house, $oldValues, 'House updated');

        $this->assertEquals('update', $log->action);
        $this->assertNotNull($log->old_values);
        $this->assertNotNull($log->new_values);
    }

    public function test_log_delete(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $house = House::factory()->create();
        
        $log = AuditLog::logDelete($house, 'House deleted');

        $this->assertEquals('delete', $log->action);
        $this->assertNotNull($log->old_values);
        $this->assertNull($log->new_values);
    }

    public function test_log_action(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $log = AuditLog::logAction('generate_bills', 'Generated 10 bills');

        $this->assertEquals('generate_bills', $log->action);
        $this->assertNull($log->model_type);
        $this->assertEquals('Generated 10 bills', $log->description);
    }

    // ==========================================
    // D. AUDIT LOG SCOPES
    // ==========================================

    public function test_for_model_scope(): void
    {
        $house = House::factory()->create();
        
        AuditLog::factory()->forModel(House::class, $house->id)->count(3)->create();
        AuditLog::factory()->count(2)->create();

        $logs = AuditLog::forModel(House::class, $house->id)->get();

        $this->assertCount(3, $logs);
    }

    public function test_by_user_scope(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        AuditLog::factory()->count(3)->create(['user_id' => $user1->id]);
        AuditLog::factory()->count(2)->create(['user_id' => $user2->id]);

        $this->assertEquals(3, AuditLog::byUser($user1->id)->count());
    }

    public function test_by_action_scope(): void
    {
        AuditLog::factory()->createAction()->count(3)->create();
        AuditLog::factory()->updateAction()->count(2)->create();
        AuditLog::factory()->deleteAction()->create();

        $this->assertEquals(3, AuditLog::byAction('create')->count());
        $this->assertEquals(2, AuditLog::byAction('update')->count());
        $this->assertEquals(1, AuditLog::byAction('delete')->count());
    }

    // ==========================================
    // E. AUDIT LOG ACCESSORS
    // ==========================================

    public function test_model_name_accessor(): void
    {
        $log = AuditLog::factory()->forModel(House::class, 1)->create();

        $this->assertEquals('House', $log->model_name);
    }

    public function test_model_name_returns_dash_when_no_model(): void
    {
        $log = AuditLog::factory()->create(['model_type' => null]);

        $this->assertEquals('-', $log->model_name);
    }

    // ==========================================
    // F. ADMIN AUDIT LOG ACCESS TESTS
    // ==========================================

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
        AuditLog::factory()->count(5)->create();

        $response = $this->actingAs($auditor)->get('/admin/audit-logs');

        $response->assertStatus(200);
    }

    public function test_treasurer_cannot_view_audit_logs(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/audit-logs');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_audit_log_details(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $log = AuditLog::factory()->create();

        $response = $this->actingAs($admin)->get("/admin/audit-logs/{$log->id}");

        $response->assertStatus(200);
    }

    // ==========================================
    // G. AUDIT LOG CAPTURES IP AND USER AGENT
    // ==========================================

    public function test_audit_log_captures_request_info(): void
    {
        $user = User::factory()->create();
        
        $this->actingAs($user)
            ->withHeaders([
                'User-Agent' => 'TestBrowser/1.0',
            ])
            ->get('/admin');

        // Manually create log to test
        $log = AuditLog::log('test', null, null, null, 'Test');

        $this->assertNotNull($log->ip_address);
    }

    // ==========================================
    // H. AUDIT LOG VALUES CASTING
    // ==========================================

    public function test_old_values_is_array(): void
    {
        $log = AuditLog::factory()->updateAction()->create();

        $this->assertIsArray($log->old_values);
    }

    public function test_new_values_is_array(): void
    {
        $log = AuditLog::factory()->updateAction()->create();

        $this->assertIsArray($log->new_values);
    }
}

