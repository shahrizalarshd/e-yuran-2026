<?php

namespace Tests\Unit;

use App\Models\Payment;
use PHPUnit\Framework\TestCase;

class PaymentModelTest extends TestCase
{
    // ==========================================
    // A. PAYMENT NO GENERATION TESTS
    // ==========================================

    public function test_generate_payment_no_format(): void
    {
        $paymentNo = Payment::generatePaymentNo();
        
        $this->assertStringStartsWith('PAY-', $paymentNo);
        $this->assertMatchesRegularExpression('/^PAY-\d{8}-[A-Z0-9]{6}$/', $paymentNo);
    }

    public function test_generate_payment_no_is_unique(): void
    {
        $paymentNo1 = Payment::generatePaymentNo();
        $paymentNo2 = Payment::generatePaymentNo();
        
        // Due to random component, should be different
        // (extremely unlikely to be same in test scenario)
        $this->assertNotEquals($paymentNo1, $paymentNo2);
    }

    // ==========================================
    // B. STATUS BADGE CLASS TESTS
    // ==========================================

    public function test_pending_status_badge_class(): void
    {
        $payment = new Payment(['status' => 'pending']);
        $this->assertEquals('bg-yellow-100 text-yellow-800', $payment->status_badge_class);
    }

    public function test_success_status_badge_class(): void
    {
        $payment = new Payment(['status' => 'success']);
        $this->assertEquals('bg-green-100 text-green-800', $payment->status_badge_class);
    }

    public function test_failed_status_badge_class(): void
    {
        $payment = new Payment(['status' => 'failed']);
        $this->assertEquals('bg-red-100 text-red-800', $payment->status_badge_class);
    }

    public function test_cancelled_status_badge_class(): void
    {
        $payment = new Payment(['status' => 'cancelled']);
        $this->assertEquals('bg-gray-100 text-gray-800', $payment->status_badge_class);
    }

}

