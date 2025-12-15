<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SystemSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    // ==========================================
    // A. SYSTEM SETTING MODEL TESTS
    // ==========================================

    public function test_system_setting_can_be_created(): void
    {
        $setting = SystemSetting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
        ]);

        $this->assertDatabaseHas('system_settings', [
            'key' => 'test_key',
            'value' => 'test_value',
        ]);
    }

    // ==========================================
    // B. GET & SET METHODS TESTS
    // ==========================================

    public function test_get_setting_value(): void
    {
        SystemSetting::factory()->create([
            'key' => 'app_name',
            'value' => 'E-Yuran',
            'type' => 'string',
        ]);

        $value = SystemSetting::get('app_name');

        $this->assertEquals('E-Yuran', $value);
    }

    public function test_get_returns_default_when_not_found(): void
    {
        $value = SystemSetting::get('nonexistent_key', 'default_value');

        $this->assertEquals('default_value', $value);
    }

    public function test_set_creates_new_setting(): void
    {
        SystemSetting::set('new_key', 'new_value', 'string', 'general', 'Test setting');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'new_key',
            'value' => 'new_value',
        ]);
    }

    public function test_set_updates_existing_setting(): void
    {
        SystemSetting::factory()->create([
            'key' => 'existing_key',
            'value' => 'old_value',
        ]);

        SystemSetting::set('existing_key', 'new_value');

        $this->assertDatabaseHas('system_settings', [
            'key' => 'existing_key',
            'value' => 'new_value',
        ]);
    }

    // ==========================================
    // C. VALUE TYPE CASTING TESTS
    // ==========================================

    public function test_boolean_type_casting(): void
    {
        SystemSetting::set('is_enabled', '1', 'boolean');
        
        $value = SystemSetting::get('is_enabled');
        
        $this->assertTrue($value);
    }

    public function test_boolean_false_casting(): void
    {
        SystemSetting::set('is_disabled', '0', 'boolean');
        
        $value = SystemSetting::get('is_disabled');
        
        $this->assertFalse($value);
    }

    public function test_integer_type_casting(): void
    {
        SystemSetting::set('max_items', '100', 'integer');
        
        $value = SystemSetting::get('max_items');
        
        $this->assertIsInt($value);
        $this->assertEquals(100, $value);
    }

    public function test_float_type_casting(): void
    {
        SystemSetting::set('tax_rate', '0.06', 'float');
        
        $value = SystemSetting::get('tax_rate');
        
        $this->assertIsFloat($value);
        $this->assertEquals(0.06, $value);
    }

    public function test_json_type_casting(): void
    {
        $arrayValue = ['key1' => 'value1', 'key2' => 'value2'];
        SystemSetting::set('config', $arrayValue, 'json');
        
        $value = SystemSetting::get('config');
        
        $this->assertIsArray($value);
        $this->assertEquals('value1', $value['key1']);
    }

    // ==========================================
    // D. GET BY GROUP TESTS
    // ==========================================

    public function test_get_by_group(): void
    {
        SystemSetting::set('setting1', 'value1', 'string', 'group_a');
        SystemSetting::set('setting2', 'value2', 'string', 'group_a');
        SystemSetting::set('setting3', 'value3', 'string', 'group_b');

        $groupA = SystemSetting::getByGroup('group_a');

        $this->assertCount(2, $groupA);
        $this->assertArrayHasKey('setting1', $groupA);
        $this->assertArrayHasKey('setting2', $groupA);
    }

    // ==========================================
    // E. CACHING TESTS
    // ==========================================

    public function test_setting_is_cached(): void
    {
        SystemSetting::factory()->create([
            'key' => 'cached_key',
            'value' => 'cached_value',
            'type' => 'string',
        ]);

        // First access
        SystemSetting::get('cached_key');

        // Verify cache was set
        $this->assertTrue(Cache::has('system_setting_cached_key'));
    }

    public function test_cache_is_cleared_on_set(): void
    {
        SystemSetting::set('cache_test', 'initial_value');
        
        // Access to cache it
        SystemSetting::get('cache_test');
        
        // Update value
        SystemSetting::set('cache_test', 'updated_value');
        
        // Should get new value
        $value = SystemSetting::get('cache_test');
        
        $this->assertEquals('updated_value', $value);
    }

    // ==========================================
    // F. TOYYIBPAY SETTINGS TESTS
    // ==========================================

    public function test_get_toyyibpay_secret_key(): void
    {
        SystemSetting::set('toyyibpay_secret_key', 'secret123', 'string', 'toyyibpay');

        $key = SystemSetting::getToyyibPaySecretKey();

        $this->assertEquals('secret123', $key);
    }

    public function test_get_toyyibpay_category_code(): void
    {
        SystemSetting::set('toyyibpay_category_code', 'cat123', 'string', 'toyyibpay');

        $code = SystemSetting::getToyyibPayCategoryCode();

        $this->assertEquals('cat123', $code);
    }

    public function test_is_toyyibpay_sandbox(): void
    {
        SystemSetting::set('toyyibpay_sandbox', '1', 'boolean', 'toyyibpay');

        $this->assertTrue(SystemSetting::isToyyibPaySandbox());
    }

    public function test_toyyibpay_sandbox_default_is_true(): void
    {
        $this->assertTrue(SystemSetting::isToyyibPaySandbox());
    }

    // ==========================================
    // G. TELEGRAM SETTINGS TESTS
    // ==========================================

    public function test_get_telegram_bot_token(): void
    {
        SystemSetting::set('telegram_bot_token', 'bot123:token', 'string', 'telegram');

        $token = SystemSetting::getTelegramBotToken();

        $this->assertEquals('bot123:token', $token);
    }

    public function test_get_telegram_chat_id(): void
    {
        SystemSetting::set('telegram_chat_id', '-123456789', 'string', 'telegram');

        $chatId = SystemSetting::getTelegramChatId();

        $this->assertEquals('-123456789', $chatId);
    }

    public function test_is_telegram_enabled(): void
    {
        SystemSetting::set('telegram_enabled', '1', 'boolean', 'telegram');

        $this->assertTrue(SystemSetting::isTelegramEnabled());
    }

    public function test_telegram_disabled_by_default(): void
    {
        $this->assertFalse(SystemSetting::isTelegramEnabled());
    }

    // ==========================================
    // H. ADMIN SETTINGS TESTS
    // ==========================================

    public function test_super_admin_can_view_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_update_toyyibpay_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/toyyibpay', [
            'secret_key' => 'new-secret-key',
            'category_code' => 'new-category',
            'sandbox' => true,
        ]);

        $response->assertRedirect();
        $this->assertEquals('new-secret-key', SystemSetting::getToyyibPaySecretKey());
    }

    public function test_super_admin_can_update_telegram_settings(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/settings/telegram', [
            'bot_token' => 'new-bot-token',
            'chat_id' => '-987654321',
            'enabled' => true,
        ]);

        $response->assertRedirect();
        $this->assertEquals('new-bot-token', SystemSetting::getTelegramBotToken());
    }

    public function test_treasurer_cannot_access_settings(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/settings');

        $response->assertStatus(403);
    }

    public function test_auditor_cannot_access_settings(): void
    {
        $auditor = User::factory()->auditor()->create();

        $response = $this->actingAs($auditor)->get('/admin/settings');

        $response->assertStatus(403);
    }
}

