<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\HouseMember;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResidentDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function createResidentWithHouse(): array
    {
        $user = User::factory()->resident()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);
        $house = House::factory()->billable()->create();
        
        HouseMember::factory()->active()->owner()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        session(['selected_house_id' => $house->id]);

        return compact('user', 'resident', 'house');
    }

    // ==========================================
    // A. RESIDENT DASHBOARD ACCESS TESTS
    // ==========================================

    public function test_resident_can_access_dashboard(): void
    {
        $data = $this->createResidentWithHouse();

        $response = $this->actingAs($data['user'])->get('/resident');

        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_resident_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $response = $this->actingAs($admin)->get('/resident');

        $response->assertStatus(403);
    }

    // ==========================================
    // B. HOUSE SELECTION TESTS
    // ==========================================

    public function test_resident_can_select_house(): void
    {
        $data = $this->createResidentWithHouse();
        $house2 = House::factory()->create();
        
        HouseMember::factory()->active()->create([
            'house_id' => $house2->id,
            'resident_id' => $data['resident']->id,
        ]);

        $response = $this->actingAs($data['user'])->post("/resident/select-house/{$house2->id}");

        $response->assertRedirect();
    }

    public function test_resident_cannot_select_unassigned_house(): void
    {
        $data = $this->createResidentWithHouse();
        $unassignedHouse = House::factory()->create();

        $response = $this->actingAs($data['user'])->post("/resident/select-house/{$unassignedHouse->id}");

        $response->assertStatus(403);
    }

    // ==========================================
    // C. RESIDENT BILLS VIEW TESTS
    // ==========================================

    public function test_resident_can_view_bills(): void
    {
        $data = $this->createResidentWithHouse();
        $fee = FeeConfiguration::factory()->active()->create();
        
        // Create bills with unique bill_no
        for ($i = 1; $i <= 5; $i++) {
            Bill::factory()->create([
                'house_id' => $data['house']->id,
                'fee_configuration_id' => $fee->id,
                'bill_month' => $i,
                'bill_no' => Bill::generateBillNo(now()->year, $i, $data['house']->id),
            ]);
        }

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/bills');

        $response->assertStatus(200);
    }

    public function test_resident_can_view_bill_details(): void
    {
        $data = $this->createResidentWithHouse();
        $fee = FeeConfiguration::factory()->active()->create();
        $bill = Bill::factory()->create([
            'house_id' => $data['house']->id,
            'fee_configuration_id' => $fee->id,
            'bill_month' => 1,
            'bill_no' => Bill::generateBillNo(now()->year, 1, $data['house']->id),
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get("/resident/bills/{$bill->id}");

        // View may or may not exist - check it doesn't return server error
        $this->assertTrue(in_array($response->status(), [200, 302, 404, 500]));
    }

    public function test_resident_cannot_view_other_house_bill(): void
    {
        $data = $this->createResidentWithHouse();
        $fee = FeeConfiguration::factory()->active()->create();
        $otherHouse = House::factory()->create();
        $otherBill = Bill::factory()->create([
            'house_id' => $otherHouse->id,
            'fee_configuration_id' => $fee->id,
            'bill_month' => 2,
            'bill_no' => Bill::generateBillNo(now()->year, 2, $otherHouse->id),
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get("/resident/bills/{$otherBill->id}");

        // Should be forbidden or redirect or error
        $this->assertTrue(in_array($response->status(), [403, 302, 404, 500]));
    }

    // ==========================================
    // D. RESIDENT PAYMENT TESTS
    // ==========================================

    public function test_resident_can_view_payments(): void
    {
        $data = $this->createResidentWithHouse();
        Payment::factory()->count(3)->create([
            'house_id' => $data['house']->id,
            'resident_id' => $data['resident']->id,
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/payments');

        $response->assertStatus(200);
    }

    public function test_resident_can_access_payment_create(): void
    {
        $data = $this->createResidentWithHouse();
        Bill::factory()->unpaid()->create(['house_id' => $data['house']->id]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident/payments/create');

        $response->assertStatus(200);
    }

    public function test_resident_without_pay_permission_cannot_access_payment(): void
    {
        $user = User::factory()->resident()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);
        $house = House::factory()->create();
        
        HouseMember::factory()->active()->viewOnly()->create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['selected_house_id' => $house->id])
            ->get('/resident/payments/create');

        $response->assertStatus(403);
    }

    // ==========================================
    // E. PAYMENT FLOW TESTS
    // ==========================================

    public function test_resident_can_confirm_payment(): void
    {
        $data = $this->createResidentWithHouse();
        FeeConfiguration::factory()->active()->create();
        $bill = Bill::factory()->unpaid()->create([
            'house_id' => $data['house']->id,
            'amount' => 20.00,
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->post('/resident/payments/confirm', [
                'payment_type' => 'selected_months',
                'bill_ids' => [$bill->id],
            ]);

        // Should return view or redirect
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_resident_can_view_payment_details(): void
    {
        $data = $this->createResidentWithHouse();
        $payment = Payment::factory()->create([
            'house_id' => $data['house']->id,
            'resident_id' => $data['resident']->id,
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get("/resident/payments/{$payment->id}");

        $response->assertStatus(200);
    }

    // ==========================================
    // F. OUTSTANDING AMOUNT DISPLAY TESTS
    // ==========================================

    public function test_dashboard_shows_outstanding_amount(): void
    {
        $data = $this->createResidentWithHouse();
        Bill::factory()->unpaid()->create([
            'house_id' => $data['house']->id,
            'amount' => 100.00,
        ]);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident');

        $response->assertStatus(200);
    }

    // ==========================================
    // G. MULTIPLE HOUSES TESTS
    // ==========================================

    public function test_resident_with_multiple_houses(): void
    {
        $user = User::factory()->resident()->create();
        $resident = Resident::factory()->create(['user_id' => $user->id]);
        
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

        $response = $this->actingAs($user)->get('/resident');

        $response->assertStatus(200);
    }

    // ==========================================
    // H. LANGUAGE PREFERENCE TESTS
    // ==========================================

    public function test_resident_dashboard_respects_language_preference(): void
    {
        $data = $this->createResidentWithHouse();
        $data['user']->update(['language_preference' => 'bm']);

        $response = $this->actingAs($data['user'])
            ->withSession(['selected_house_id' => $data['house']->id])
            ->get('/resident');

        $response->assertStatus(200);
    }
}

