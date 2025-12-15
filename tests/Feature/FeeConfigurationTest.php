<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeConfigurationTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. FEE CONFIGURATION MODEL TESTS
    // ==========================================

    public function test_fee_configuration_can_be_created(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $fee = FeeConfiguration::factory()->create([
            'name' => 'Yuran Bulanan 2024',
            'amount' => 20.00,
            'effective_from' => '2024-01-01',
            'created_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('fee_configurations', [
            'name' => 'Yuran Bulanan 2024',
            'amount' => 20.00,
        ]);
    }

    public function test_fee_configuration_amount_is_decimal(): void
    {
        $fee = FeeConfiguration::factory()->create(['amount' => 25.50]);

        $this->assertEquals(25.50, $fee->amount);
    }

    // ==========================================
    // B. FEE CONFIGURATION RELATIONSHIPS TESTS
    // ==========================================

    public function test_fee_configuration_belongs_to_creator(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->create(['created_by' => $admin->id]);

        $this->assertEquals($admin->id, $fee->creator->id);
    }

    public function test_fee_configuration_has_many_bills(): void
    {
        $fee = FeeConfiguration::factory()->create();
        Bill::factory()->count(5)->create(['fee_configuration_id' => $fee->id]);

        $this->assertCount(5, $fee->bills);
    }

    // ==========================================
    // C. FEE CONFIGURATION SCOPES TESTS
    // ==========================================

    public function test_active_scope(): void
    {
        FeeConfiguration::factory()->active()->count(2)->create();
        FeeConfiguration::factory()->inactive()->create();

        $this->assertEquals(2, FeeConfiguration::active()->count());
    }

    public function test_effective_on_scope(): void
    {
        // Fee effective from Jan 2024 to Dec 2024
        FeeConfiguration::factory()->create([
            'effective_from' => '2024-01-01',
            'effective_until' => '2024-12-31',
            'is_active' => true,
        ]);

        // Fee effective from Jan 2025 onwards
        FeeConfiguration::factory()->create([
            'effective_from' => '2025-01-01',
            'effective_until' => null,
            'is_active' => true,
        ]);

        // Check for date in 2024
        $date2024 = \Carbon\Carbon::parse('2024-06-15');
        $this->assertEquals(1, FeeConfiguration::effectiveOn($date2024)->count());

        // Check for date in 2025
        $date2025 = \Carbon\Carbon::parse('2025-06-15');
        $this->assertEquals(1, FeeConfiguration::effectiveOn($date2025)->count());
    }

    // ==========================================
    // D. GET CURRENT FEE TESTS
    // ==========================================

    public function test_get_current_fee(): void
    {
        FeeConfiguration::factory()->create([
            'name' => 'Old Fee',
            'amount' => 15.00,
            'effective_from' => now()->subYear(),
            'effective_until' => now()->subMonth(),
            'is_active' => true,
        ]);

        FeeConfiguration::factory()->create([
            'name' => 'Current Fee',
            'amount' => 20.00,
            'effective_from' => now()->subMonth(),
            'effective_until' => null,
            'is_active' => true,
        ]);

        $currentFee = FeeConfiguration::getCurrentFee();

        $this->assertNotNull($currentFee);
        $this->assertEquals('Current Fee', $currentFee->name);
        $this->assertEquals(20.00, $currentFee->amount);
    }

    public function test_get_fee_for_specific_date(): void
    {
        FeeConfiguration::factory()->create([
            'name' => 'Fee 2023',
            'amount' => 15.00,
            'effective_from' => '2023-01-01',
            'effective_until' => '2023-12-31',
            'is_active' => true,
        ]);

        FeeConfiguration::factory()->create([
            'name' => 'Fee 2024',
            'amount' => 20.00,
            'effective_from' => '2024-01-01',
            'effective_until' => null,
            'is_active' => true,
        ]);

        $fee2023 = FeeConfiguration::getFeeForDate(\Carbon\Carbon::parse('2023-06-15'));
        $fee2024 = FeeConfiguration::getFeeForDate(\Carbon\Carbon::parse('2024-06-15'));

        $this->assertEquals('Fee 2023', $fee2023->name);
        $this->assertEquals('Fee 2024', $fee2024->name);
    }

    public function test_returns_null_when_no_fee_configured(): void
    {
        $fee = FeeConfiguration::getCurrentFee();

        $this->assertNull($fee);
    }

    // ==========================================
    // E. ADMIN FEE MANAGEMENT TESTS
    // ==========================================

    public function test_super_admin_can_view_fees_list(): void
    {
        $admin = User::factory()->superAdmin()->create();
        FeeConfiguration::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/fees');

        $response->assertStatus(200);
    }

    public function test_super_admin_can_create_fee(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->post('/admin/fees', [
            'name' => 'Yuran 2025',
            'amount' => 25.00,
            'effective_from' => '2025-01-01',
            'description' => 'Yuran penyelenggaraan tahunan',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('fee_configurations', [
            'name' => 'Yuran 2025',
            'amount' => 25.00,
        ]);
    }

    public function test_super_admin_can_update_fee(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->create(['amount' => 20.00]);

        $response = $this->actingAs($admin)->put("/admin/fees/{$fee->id}", [
            'name' => $fee->name,
            'amount' => 30.00,
            'effective_from' => $fee->effective_from->format('Y-m-d'),
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $this->assertEquals(30.00, $fee->fresh()->amount);
    }

    public function test_super_admin_can_delete_fee(): void
    {
        $admin = User::factory()->superAdmin()->create();
        $fee = FeeConfiguration::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/fees/{$fee->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('fee_configurations', ['id' => $fee->id]);
    }

    public function test_treasurer_cannot_manage_fees(): void
    {
        $treasurer = User::factory()->treasurer()->create();

        $response = $this->actingAs($treasurer)->get('/admin/fees');

        $response->assertStatus(403);
    }

    public function test_auditor_cannot_manage_fees(): void
    {
        $auditor = User::factory()->auditor()->create();

        $response = $this->actingAs($auditor)->get('/admin/fees');

        $response->assertStatus(403);
    }

    // ==========================================
    // F. DATE CASTING TESTS
    // ==========================================

    public function test_dates_are_casted_correctly(): void
    {
        $fee = FeeConfiguration::factory()->create([
            'effective_from' => '2024-01-01',
            'effective_until' => '2024-12-31',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fee->effective_from);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $fee->effective_until);
    }

    public function test_effective_until_can_be_null(): void
    {
        $fee = FeeConfiguration::factory()->create(['effective_until' => null]);

        $this->assertNull($fee->effective_until);
    }

    // ==========================================
    // G. FEE HISTORY TESTS
    // ==========================================

    public function test_old_bills_retain_old_fee_amount(): void
    {
        $oldFee = FeeConfiguration::factory()->create([
            'amount' => 15.00,
            'effective_from' => '2023-01-01',
            'effective_until' => '2023-12-31',
        ]);

        $bill = Bill::factory()->create([
            'fee_configuration_id' => $oldFee->id,
            'amount' => 15.00,
        ]);

        // Create new fee
        FeeConfiguration::factory()->create([
            'amount' => 20.00,
            'effective_from' => '2024-01-01',
        ]);

        // Old bill should still have old amount
        $this->assertEquals(15.00, $bill->fresh()->amount);
    }
}

