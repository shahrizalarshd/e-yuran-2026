<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToyyibPayService
{
    private string $secretKey;
    private string $categoryCode;
    private bool $isSandbox;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = SystemSetting::getToyyibPaySecretKey() ?? '';
        $this->categoryCode = SystemSetting::getToyyibPayCategoryCode() ?? '';
        $this->isSandbox = SystemSetting::isToyyibPaySandbox();
        $this->baseUrl = $this->isSandbox 
            ? 'https://dev.toyyibpay.com' 
            : 'https://toyyibpay.com';
    }

    public function isConfigured(): bool
    {
        return !empty($this->secretKey) && !empty($this->categoryCode);
    }

    public function createBill(Payment $payment, array $billDetails): ?string
    {
        if (!$this->isConfigured()) {
            Log::error('ToyyibPay not configured');
            return null;
        }

        $house = $payment->house;
        $resident = $payment->resident;

        $data = [
            'userSecretKey' => $this->secretKey,
            'categoryCode' => $this->categoryCode,
            'billName' => 'Yuran PPTT #' . $house->house_no, // Max 30 chars
            'billDescription' => $billDetails['description'] ?? 'Pembayaran yuran perumahan',
            'billPriceSetting' => 1, // Fixed price
            'billPayorInfo' => 1, // Required
            'billAmount' => $payment->amount * 100, // In cents
            'billReturnUrl' => route('payment.callback'),
            'billCallbackUrl' => route('payment.webhook'),
            'billExternalReferenceNo' => $payment->payment_no,
            'billTo' => $resident->name ?? 'Penduduk',
            'billEmail' => $resident->email ?? '',
            'billPhone' => $resident->phone ?? '',
            'billSplitPayment' => 0,
            'billSplitPaymentArgs' => '',
            'billPaymentChannel' => 0, // All channels
            'billContentEmail' => 'Terima kasih atas pembayaran yuran perumahan anda.',
            'billChargeToCustomer' => 1, // Charge fee to customer
            'billExpiryDate' => now()->addDays(7)->format('d-m-Y H:i:s'),
            'billExpiryDays' => 7,
        ];

        // Log request data (excluding sensitive info)
        Log::info('ToyyibPay createBill request', [
            'payment_id' => $payment->id,
            'amount' => $data['billAmount'],
            'category' => substr($this->categoryCode, 0, 4) . '...',
            'base_url' => $this->baseUrl,
        ]);

        try {
            $response = Http::connectTimeout(5)  // Connection timeout 5s
                ->timeout(15)                     // Read timeout 15s
                ->asForm()
                ->post($this->baseUrl . '/index.php/api/createBill', $data);

            $statusCode = $response->status();
            $body = $response->body();

            Log::info('ToyyibPay createBill response', [
                'payment_id' => $payment->id,
                'status' => $statusCode,
                'body' => substr($body, 0, 200),
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (is_array($result) && isset($result[0]['BillCode'])) {
                    $billCode = $result[0]['BillCode'];
                    
                    // Update payment with billcode
                    $payment->update(['toyyibpay_billcode' => $billCode]);
                    
                    Log::info('ToyyibPay bill created successfully', [
                        'payment_id' => $payment->id,
                        'bill_code' => $billCode,
                    ]);
                    
                    return $billCode;
                }
            }

            Log::error('ToyyibPay createBill failed', [
                'payment_id' => $payment->id,
                'status' => $statusCode,
                'response' => $body,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('ToyyibPay createBill exception', [
                'error' => $e->getMessage(),
                'payment_id' => $payment->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    public function getPaymentUrl(string $billCode): string
    {
        return $this->baseUrl . '/' . $billCode;
    }

    public function getBillTransactions(string $billCode): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::asForm()->post($this->baseUrl . '/index.php/api/getBillTransactions', [
                'billCode' => $billCode,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('ToyyibPay getBillTransactions exception', [
                'error' => $e->getMessage(),
                'billCode' => $billCode,
            ]);

            return null;
        }
    }

    public function verifyCallback(array $data): bool
    {
        // Verify the callback data from ToyyibPay
        $requiredFields = ['refno', 'status', 'reason', 'billcode', 'order_id'];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        return true;
    }

    public function processCallback(array $data): ?Payment
    {
        if (!$this->verifyCallback($data)) {
            Log::warning('Invalid ToyyibPay callback data', $data);
            return null;
        }

        $payment = Payment::where('payment_no', $data['order_id'])
            ->orWhere('toyyibpay_billcode', $data['billcode'])
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for ToyyibPay callback', $data);
            return null;
        }

        // Status: 1 = Success, 2 = Pending, 3 = Failed
        if ($data['status'] === '1') {
            $payment->markAsSuccess($data['refno'], json_encode($data));
        } elseif ($data['status'] === '3') {
            $payment->markAsFailed(json_encode($data));
        }

        $payment->update([
            'toyyibpay_ref' => $data['refno'],
            'toyyibpay_response' => json_encode($data),
        ]);

        return $payment;
    }
}

