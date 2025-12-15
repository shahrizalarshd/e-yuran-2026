<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\House;
use App\Models\Payment;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Services\ToyyibPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(private ToyyibPayService $toyyibPayService)
    {
    }

    public function index()
    {
        $resident = auth()->user()->resident;
        $house = $this->getSelectedHouse($resident);

        if (!$house) {
            return redirect()->route('resident.dashboard');
        }

        $payments = Payment::where('house_id', $house->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('resident.payments.index', compact('payments', 'house'));
    }

    public function create(Request $request)
    {
        $resident = auth()->user()->resident;
        $house = $this->getSelectedHouse($resident);

        if (!$house) {
            return redirect()->route('resident.dashboard');
        }

        // Check permission
        $membership = $resident->houseMemberships()
            ->where('house_id', $house->id)
            ->where('status', 'active')
            ->first();

        if (!$membership || !$membership->can_pay) {
            abort(403, __('messages.unauthorized'));
        }

        // Get unpaid bills
        $unpaidBills = Bill::where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bill_year')
            ->orderBy('bill_month')
            ->get();

        if ($unpaidBills->isEmpty()) {
            return redirect()->route('resident.dashboard')
                ->with('info', __('Tiada bil tertunggak'));
        }

        // Pre-select bills based on payment type
        $paymentType = $request->get('type', 'selected');
        $selectedBillIds = [];

        if ($paymentType === 'current') {
            // Current month
            $currentBill = $unpaidBills->first(fn($b) => 
                $b->bill_year == now()->year && $b->bill_month == now()->month
            );
            if ($currentBill) {
                $selectedBillIds = [$currentBill->id];
            }
        } elseif ($paymentType === 'yearly') {
            // All unpaid bills for current year
            $selectedBillIds = $unpaidBills
                ->where('bill_year', now()->year)
                ->pluck('id')
                ->toArray();
        }

        // Prepare bills data for JavaScript
        $billsJson = $unpaidBills->map(function ($b) {
            return [
                'id' => $b->id,
                'amount' => $b->outstanding_amount,
                'year' => $b->bill_year,
                'month' => $b->bill_month,
            ];
        })->values()->toArray();

        return view('resident.payments.create', compact(
            'house',
            'unpaidBills',
            'paymentType',
            'selectedBillIds',
            'billsJson'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'exists:bills,id',
            'payment_type' => 'required|in:current_month,selected_months,yearly',
        ]);

        $resident = auth()->user()->resident;
        $house = $this->getSelectedHouse($resident);

        if (!$house) {
            return redirect()->route('resident.dashboard');
        }

        // Check permission
        $membership = $resident->houseMemberships()
            ->where('house_id', $house->id)
            ->where('status', 'active')
            ->first();

        if (!$membership || !$membership->can_pay) {
            abort(403);
        }

        // Get bills and verify they belong to this house
        $bills = Bill::whereIn('id', $validated['bill_ids'])
            ->where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        if ($bills->isEmpty()) {
            return back()->with('error', 'No valid bills selected');
        }

        // Calculate total amount
        $totalAmount = $bills->sum(fn($bill) => $bill->outstanding_amount);

        DB::beginTransaction();
        try {
            // Create payment record
            $payment = Payment::create([
                'house_id' => $house->id,
                'resident_id' => $resident->id,
                'payment_no' => Payment::generatePaymentNo(),
                'amount' => $totalAmount,
                'status' => 'pending',
                'payment_type' => $validated['payment_type'],
            ]);

            // Attach bills to payment
            foreach ($bills as $bill) {
                $payment->bills()->attach($bill->id, [
                    'amount' => $bill->outstanding_amount,
                ]);
                // Mark bill as processing
                $bill->markAsProcessing();
            }

            // Create ToyyibPay bill
            $billCode = $this->toyyibPayService->createBill($payment, [
                'description' => 'Yuran perumahan untuk ' . $bills->count() . ' bulan',
            ]);

            if (!$billCode) {
                throw new \Exception('Failed to create ToyyibPay bill');
            }

            DB::commit();

            AuditLog::logCreate($payment, "Payment initiated for house {$house->house_no}");

            // Redirect to ToyyibPay
            $paymentUrl = $this->toyyibPayService->getPaymentUrl($billCode);
            
            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->with('error', 'Payment creation failed: ' . $e->getMessage());
        }
    }

    public function confirm(Request $request)
    {
        // Confirmation page before payment
        $validated = $request->validate([
            'bill_ids' => 'required|array|min:1',
            'bill_ids.*' => 'exists:bills,id',
            'payment_type' => 'required|in:current_month,selected_months,yearly',
        ]);

        $resident = auth()->user()->resident;
        $house = $this->getSelectedHouse($resident);

        if (!$house) {
            return redirect()->route('resident.dashboard');
        }

        $bills = Bill::whereIn('id', $validated['bill_ids'])
            ->where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bill_year')
            ->orderBy('bill_month')
            ->get();

        if ($bills->isEmpty()) {
            return back()->with('error', 'No valid bills selected');
        }

        $totalAmount = $bills->sum(fn($bill) => $bill->outstanding_amount);

        return view('resident.payments.confirm', compact(
            'house',
            'bills',
            'totalAmount',
            'validated'
        ));
    }

    public function show(Payment $payment)
    {
        $resident = auth()->user()->resident;

        // Verify access
        $membership = $resident->houseMemberships()
            ->where('house_id', $payment->house_id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            abort(403);
        }

        $payment->load(['house', 'bills']);

        return view('resident.payments.show', compact('payment'));
    }

    private function getSelectedHouse($resident): ?House
    {
        $selectedHouseId = session('selected_house_id');

        if ($selectedHouseId) {
            $membership = $resident->houseMemberships()
                ->where('house_id', $selectedHouseId)
                ->where('status', 'active')
                ->first();

            if ($membership) {
                return $membership->house;
            }
        }

        $membership = $resident->houseMemberships()
            ->where('status', 'active')
            ->with('house')
            ->first();

        return $membership?->house;
    }
}

