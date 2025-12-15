<?php

namespace Tests\Unit;

use App\Models\Bill;
use PHPUnit\Framework\TestCase;

class BillModelTest extends TestCase
{
    // ==========================================
    // A. BILL NO GENERATION TESTS
    // ==========================================

    public function test_generate_bill_no_format(): void
    {
        $billNo = Bill::generateBillNo(2024, 6, 123);
        
        $this->assertEquals('BIL-202406-00123', $billNo);
    }

    public function test_generate_bill_no_with_single_digit_month(): void
    {
        $billNo = Bill::generateBillNo(2024, 1, 1);
        
        $this->assertEquals('BIL-202401-00001', $billNo);
    }

    public function test_generate_bill_no_with_large_house_id(): void
    {
        $billNo = Bill::generateBillNo(2024, 12, 99999);
        
        $this->assertEquals('BIL-202412-99999', $billNo);
    }

    // ==========================================
    // B. OUTSTANDING AMOUNT TESTS
    // ==========================================

    public function test_outstanding_amount_calculation(): void
    {
        $bill = new Bill([
            'amount' => 100.00,
            'paid_amount' => 30.00,
        ]);

        $this->assertEquals(70.00, $bill->outstanding_amount);
    }

    public function test_outstanding_amount_never_negative(): void
    {
        $bill = new Bill([
            'amount' => 100.00,
            'paid_amount' => 150.00, // Overpaid
        ]);

        $this->assertEquals(0, $bill->outstanding_amount);
    }

    public function test_outstanding_amount_full_when_unpaid(): void
    {
        $bill = new Bill([
            'amount' => 50.00,
            'paid_amount' => 0,
        ]);

        $this->assertEquals(50.00, $bill->outstanding_amount);
    }

    // ==========================================
    // C. BILL PERIOD TESTS
    // ==========================================

    public function test_bill_period_january(): void
    {
        $bill = new Bill(['bill_month' => 1, 'bill_year' => 2024]);
        $this->assertEquals('Januari 2024', $bill->bill_period);
    }

    public function test_bill_period_december(): void
    {
        $bill = new Bill(['bill_month' => 12, 'bill_year' => 2024]);
        $this->assertEquals('Disember 2024', $bill->bill_period);
    }

    public function test_bill_period_en_january(): void
    {
        $bill = new Bill(['bill_month' => 1, 'bill_year' => 2024]);
        $this->assertEquals('January 2024', $bill->bill_period_en);
    }

    public function test_bill_period_en_december(): void
    {
        $bill = new Bill(['bill_month' => 12, 'bill_year' => 2024]);
        $this->assertEquals('December 2024', $bill->bill_period_en);
    }

    // ==========================================
    // D. STATUS BADGE CLASS TESTS
    // ==========================================

    public function test_unpaid_status_badge_class(): void
    {
        $bill = new Bill(['status' => 'unpaid']);
        $this->assertEquals('bg-red-100 text-red-800', $bill->status_badge_class);
    }

    public function test_paid_status_badge_class(): void
    {
        $bill = new Bill(['status' => 'paid']);
        $this->assertEquals('bg-green-100 text-green-800', $bill->status_badge_class);
    }

    public function test_processing_status_badge_class(): void
    {
        $bill = new Bill(['status' => 'processing']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $bill->status_badge_class);
    }

    public function test_partial_status_badge_class(): void
    {
        $bill = new Bill(['status' => 'partial']);
        $this->assertEquals('bg-orange-100 text-orange-800', $bill->status_badge_class);
    }

    public function test_unknown_status_badge_class(): void
    {
        $bill = new Bill(['status' => 'unknown']);
        $this->assertEquals('bg-gray-100 text-gray-800', $bill->status_badge_class);
    }
}

