<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Services\ToyyibPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    public function __construct(private ToyyibPayService $toyyibPayService)
    {
    }

    /**
     * Handle return URL from ToyyibPay (user redirected back)
     */
    public function callback(Request $request)
    {
        Log::info('ToyyibPay callback received', $request->all());

        $data = $request->all();
        
        // Find the payment
        $payment = Payment::where('payment_no', $data['order_id'] ?? '')
            ->orWhere('toyyibpay_billcode', $data['billcode'] ?? '')
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for callback', $data);
            return redirect()->route('resident.dashboard')
                ->with('error', __('messages.payment_failed'));
        }

        // Process based on status
        // Status: 1 = Success, 2 = Pending, 3 = Failed
        $status = $data['status_id'] ?? $data['status'] ?? null;

        if ($status === '1') {
            // Payment successful
            if ($payment->status !== 'success') {
                $payment->markAsSuccess(
                    $data['refno'] ?? $data['transaction_id'] ?? '',
                    json_encode($data)
                );

                AuditLog::logAction('payment_success', "Payment {$payment->payment_no} completed successfully");

                // Notify user
                if ($payment->resident && $payment->resident->user) {
                    SystemNotification::notifySuccess(
                        $payment->resident->user,
                        __('messages.payment_success'),
                        __('Pembayaran sebanyak RM:amount telah berjaya.', ['amount' => number_format($payment->amount, 2)]),
                        route('resident.payments.show', $payment)
                    );
                }
            }

            return redirect()->route('resident.payments.show', $payment)
                ->with('success', __('messages.payment_success'));
        } elseif ($status === '3') {
            // Payment failed
            if ($payment->status === 'pending') {
                $payment->markAsFailed(json_encode($data));

                AuditLog::logAction('payment_failed', "Payment {$payment->payment_no} failed");
            }

            return redirect()->route('resident.dashboard')
                ->with('error', __('messages.payment_failed'));
        } else {
            // Payment pending or cancelled
            return redirect()->route('resident.dashboard')
                ->with('warning', __('Pembayaran masih dalam proses atau dibatalkan.'));
        }
    }

    /**
     * Handle webhook from ToyyibPay (server-to-server)
     */
    public function webhook(Request $request)
    {
        Log::info('ToyyibPay webhook received', $request->all());

        $data = $request->all();

        $payment = $this->toyyibPayService->processCallback($data);

        if (!$payment) {
            Log::error('Webhook processing failed', $data);
            return response('FAIL', 400);
        }

        if ($payment->status === 'success') {
            AuditLog::logAction('payment_webhook_success', "Payment {$payment->payment_no} confirmed via webhook");

            // Notify user
            if ($payment->resident && $payment->resident->user) {
                SystemNotification::notifySuccess(
                    $payment->resident->user,
                    __('messages.payment_success'),
                    __('Pembayaran sebanyak RM:amount telah berjaya.', ['amount' => number_format($payment->amount, 2)]),
                    route('resident.payments.show', $payment)
                );
            }
        }

        return response('OK', 200);
    }
}

