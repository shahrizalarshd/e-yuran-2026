<?php

namespace Tests\Feature;

use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. NOTIFICATION MODEL TESTS
    // ==========================================

    public function test_notification_can_be_created(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::factory()->create([
            'user_id' => $user->id,
            'title' => 'Test Notification',
            'message' => 'Test message content',
        ]);

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $user->id,
            'title' => 'Test Notification',
        ]);
    }

    // ==========================================
    // B. NOTIFICATION RELATIONSHIPS
    // ==========================================

    public function test_notification_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $notification = SystemNotification::factory()->create(['user_id' => $user->id]);

        $this->assertEquals($user->id, $notification->user->id);
    }

    // ==========================================
    // C. NOTIFICATION SCOPES
    // ==========================================

    public function test_unread_scope(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->unread()->count(3)->create(['user_id' => $user->id]);
        SystemNotification::factory()->read()->count(2)->create(['user_id' => $user->id]);

        $this->assertEquals(3, SystemNotification::unread()->count());
    }

    public function test_read_scope(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->read()->count(4)->create(['user_id' => $user->id]);
        SystemNotification::factory()->unread()->count(2)->create(['user_id' => $user->id]);

        $this->assertEquals(4, SystemNotification::read()->count());
    }

    public function test_recent_scope(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->count(15)->create(['user_id' => $user->id]);

        $recent = SystemNotification::recent(5)->get();

        $this->assertCount(5, $recent);
    }

    // ==========================================
    // D. MARK AS READ TESTS
    // ==========================================

    public function test_mark_as_read(): void
    {
        $notification = SystemNotification::factory()->unread()->create();

        $notification->markAsRead();

        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_as_read_only_updates_unread(): void
    {
        $notification = SystemNotification::factory()->read()->create([
            'read_at' => now()->subDay(),
        ]);
        $originalReadAt = $notification->read_at;

        $notification->markAsRead();

        $notification->refresh();
        // Should not update read_at if already read
        $this->assertEquals($originalReadAt->format('Y-m-d H:i:s'), $notification->read_at->format('Y-m-d H:i:s'));
    }

    // ==========================================
    // E. STATIC NOTIFY METHODS
    // ==========================================

    public function test_notify_static_method(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::notify($user, 'Title', 'Message', 'info', '/action-url');

        $this->assertEquals('Title', $notification->title);
        $this->assertEquals('Message', $notification->message);
        $this->assertEquals('info', $notification->type);
        $this->assertEquals('/action-url', $notification->action_url);
    }

    public function test_notify_success(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::notifySuccess($user, 'Success', 'Operation successful');

        $this->assertEquals('success', $notification->type);
    }

    public function test_notify_warning(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::notifyWarning($user, 'Warning', 'Please take action');

        $this->assertEquals('warning', $notification->type);
    }

    public function test_notify_error(): void
    {
        $user = User::factory()->create();

        $notification = SystemNotification::notifyError($user, 'Error', 'Something went wrong');

        $this->assertEquals('error', $notification->type);
    }

    // ==========================================
    // F. NOTIFICATION ACCESSORS
    // ==========================================

    public function test_type_icon_accessor(): void
    {
        $info = SystemNotification::factory()->info()->create();
        $success = SystemNotification::factory()->success()->create();
        $warning = SystemNotification::factory()->warning()->create();
        $error = SystemNotification::factory()->error()->create();

        $this->assertEquals('information-circle', $info->type_icon);
        $this->assertEquals('check-circle', $success->type_icon);
        $this->assertEquals('exclamation-triangle', $warning->type_icon);
        $this->assertEquals('x-circle', $error->type_icon);
    }

    public function test_type_bg_class_accessor(): void
    {
        $info = SystemNotification::factory()->info()->create();
        $success = SystemNotification::factory()->success()->create();
        $warning = SystemNotification::factory()->warning()->create();
        $error = SystemNotification::factory()->error()->create();

        $this->assertEquals('bg-blue-50', $info->type_bg_class);
        $this->assertEquals('bg-green-50', $success->type_bg_class);
        $this->assertEquals('bg-yellow-50', $warning->type_bg_class);
        $this->assertEquals('bg-red-50', $error->type_bg_class);
    }

    // ==========================================
    // G. USER NOTIFICATION COUNT
    // ==========================================

    public function test_user_unread_notification_count(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->unread()->count(5)->create(['user_id' => $user->id]);
        SystemNotification::factory()->read()->count(3)->create(['user_id' => $user->id]);

        $this->assertEquals(5, $user->unread_notification_count);
    }

    // ==========================================
    // H. NOTIFICATION ROUTES TESTS
    // ==========================================

    public function test_user_can_view_notifications(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
    }

    public function test_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = SystemNotification::factory()->unread()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertTrue($notification->fresh()->is_read);
    }

    public function test_user_can_mark_all_as_read(): void
    {
        $user = User::factory()->create();
        SystemNotification::factory()->unread()->count(5)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->post('/notifications/mark-all-read');

        $response->assertRedirect();
        $this->assertEquals(0, SystemNotification::where('user_id', $user->id)->unread()->count());
    }

    public function test_user_can_delete_notification(): void
    {
        $user = User::factory()->create();
        $notification = SystemNotification::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->delete("/notifications/{$notification->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('system_notifications', ['id' => $notification->id]);
    }

    public function test_user_cannot_access_other_user_notification(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $notification = SystemNotification::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->post("/notifications/{$notification->id}/read");

        $response->assertStatus(403);
    }
}

