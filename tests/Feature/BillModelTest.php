<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillModelTest extends TestCase
{
    use RefreshDatabase;

    // ==========================================
    // A. BILL MODEL TESTS
    // ==========================================

    public function test_bill_can_be_created(): void
    {
        $house = House::factory()->create();
        $fee = FeeConfiguration::factory()->create();

        $bill = Bill::factory()->create([
            'house_id' => $house->id,
            'fee_configuration_id' => $fee->id,
            'bill_year' => 2024,
            'bill_month' => 6,
            'amount' => 20.00,
        ]);

        $this->assertDatabaseHas('bills', [
            'house_id' => $house->id,
            'bill_year' => 2024,
            'bill_month' => 6,
        ]);
    }

    public function test_bill_no_generation(): void
    {
        $billNo = Bill::generateBillNo(2024, 6, 123);

        $this->assertEquals('BIL-202406-00123', $billNo);
    }

    // ==========================================
    // B. BILL RELATIONSHIPS TESTS
    // ==========================================

    public function test_bill_belongs_to_house(): void
    {
        $house = House::factory()->create();
        $bill = Bill::factory()->create(['house_id' => $house->id]);

        $this->assertEquals($house->id, $bill->house->id);
    }

    public function test_bill_belongs_to_fee_configuration(): void
    {
        $fee = FeeConfiguration::factory()->create();
        $bill = Bill::factory()->create(['fee_configuration_id' => $fee->id]);

        $this->assertEquals($fee->id, $bill->feeConfiguration->id);
    }

    public function test_bill_has_many_payments(): void
    {
        $bill = Bill::factory()->create();
        $payment = Payment::factory()->create();
        
        $bill->payments()->attach($payment->id, ['amount' => $bill->amount]);

        $this->assertCount(1, $bill->payments);
    }

    // ==========================================
    // C. BILL SCOPES TESTS
    // ==========================================

    public function test_unpaid_scope(): void
    {
        Bill::factory()->unpaid()->count(3)->create();
        Bill::factory()->paid()->count(2)->create();

        $this->assertEquals(3, Bill::unpaid()->count());
    }

    public function test_paid_scope(): void
    {
        Bill::factory()->paid()->count(4)->create();
        Bill::factory()->unpaid()->count(2)->create();

        $this->assertEquals(4, Bill::paid()->count());
    }

    public function test_processing_scope(): void
    {
        Bill::factory()->processing()->count(2)->create();
        Bill::factory()->unpaid()->create();

        $this->assertEquals(2, Bill::processing()->count());
    }

    public function test_for_year_scope(): void
    {
        Bill::factory()->forPeriod(2024, 1)->count(3)->create();
        Bill::factory()->forPeriod(2023, 1)->count(2)->create();

        $this->assertEquals(3, Bill::forYear(2024)->count());
    }

    public function test_for_month_scope(): void
    {
        Bill::factory()->forPeriod(2024, 6)->count(2)->create();
        Bill::factory()->forPeriod(2024, 7)->count(3)->create();

        $this->assertEquals(2, Bill::forMonth(6)->count());
    }

    public function test_overdue_scope(): void
    {
        Bill::factory()->overdue()->count(2)->create();
        Bill::factory()->unpaid()->create(['due_date' => now()->addMonth()]);
        Bill::factory()->paid()->create();

        $this->assertEquals(2, Bill::overdue()->count());
    }

    // ==========================================
    // D. BILL ACCESSORS TESTS
    // ==========================================

    public function test_outstanding_amount_accessor(): void
    {
        $bill = Bill::factory()->create([
            'amount' => 50.00,
            'paid_amount' => 20.00,
        ]);

        $this->assertEquals(30.00, $bill->outstanding_amount);
    }

    public function test_outstanding_amount_never_negative(): void
    {
        $bill = Bill::factory()->create([
            'amount' => 50.00,
            'paid_amount' => 60.00, // Overpaid scenario
        ]);

        $this->assertEquals(0, $bill->outstanding_amount);
    }

    public function test_is_overdue_accessor(): void
    {
        $overdueBill = Bill::factory()->overdue()->create();
        $currentBill = Bill::factory()->unpaid()->create(['due_date' => now()->addWeek()]);
        $paidBill = Bill::factory()->paid()->create(['due_date' => now()->subMonth()]);

        $this->assertTrue($overdueBill->is_overdue);
        $this->assertFalse($currentBill->is_overdue);
        $this->assertFalse($paidBill->is_overdue);
    }

    public function test_bill_period_accessor_bm(): void
    {
        $bill = Bill::factory()->create([
            'bill_month' => 6,
            'bill_year' => 2024,
        ]);

        $this->assertEquals('Jun 2024', $bill->bill_period);
    }

    public function test_bill_period_en_accessor(): void
    {
        $bill = Bill::factory()->create([
            'bill_month' => 6,
            'bill_year' => 2024,
        ]);

        $this->assertEquals('June 2024', $bill->bill_period_en);
    }

    public function test_status_badge_class_accessor(): void
    {
        $unpaidBill = Bill::factory()->unpaid()->create();
        $paidBill = Bill::factory()->paid()->create();
        $processingBill = Bill::factory()->processing()->create();
        $partialBill = Bill::factory()->partial()->create();

        $this->assertEquals('bg-red-100 text-red-800', $unpaidBill->status_badge_class);
        $this->assertEquals('bg-green-100 text-green-800', $paidBill->status_badge_class);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $processingBill->status_badge_class);
        $this->assertEquals('bg-orange-100 text-orange-800', $partialBill->status_badge_class);
    }

    // ==========================================
    // E. BILL STATUS CHANGES TESTS
    // ==========================================

    public function test_mark_as_paid(): void
    {
        $bill = Bill::factory()->unpaid()->create(['amount' => 20.00]);

        $bill->markAsPaid();

        $this->assertEquals('paid', $bill->fresh()->status);
        $this->assertEquals(20.00, $bill->fresh()->paid_amount);
        $this->assertNotNull($bill->fresh()->paid_at);
    }

    public function test_mark_as_processing(): void
    {
        $bill = Bill::factory()->unpaid()->create();

        $bill->markAsProcessing();

        $this->assertEquals('processing', $bill->fresh()->status);
    }

    public function test_reset_to_unpaid(): void
    {
        $bill = Bill::factory()->paid()->create();

        $bill->resetToUnpaid();

        $this->assertEquals('unpaid', $bill->fresh()->status);
        $this->assertEquals(0, $bill->fresh()->paid_amount);
        $this->assertNull($bill->fresh()->paid_at);
    }

    // ==========================================
    // F. BILL DATE CASTING TESTS
    // ==========================================

    public function test_dates_are_casted_correctly(): void
    {
        $bill = Bill::factory()->paid()->create([
            'due_date' => '2024-06-30',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $bill->due_date);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $bill->paid_at);
    }

    // ==========================================
    // G. ALL MONTHS BILL PERIOD TESTS
    // ==========================================

    public function test_all_months_bill_period(): void
    {
        $expectedBm = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
            5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
        ];

        foreach ($expectedBm as $month => $monthName) {
            $bill = Bill::factory()->create([
                'bill_month' => $month,
                'bill_year' => 2024,
            ]);

            $this->assertEquals("{$monthName} 2024", $bill->bill_period);
        }
    }
}

