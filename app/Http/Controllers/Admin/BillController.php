<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\House;
use App\Models\AuditLog;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    public function index(Request $request)
    {
        $query = Bill::with('house');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('bill_year', $request->year);
        }

        // Filter by month
        if ($request->filled('month')) {
            $query->where('bill_month', $request->month);
        }

        // Search by house
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('house', function ($q) use ($search) {
                $q->where('house_no', 'like', "%{$search}%")
                    ->orWhere('street_name', 'like', "%{$search}%");
            });
        }

        $bills = $query->orderBy('bill_year', 'desc')
            ->orderBy('bill_month', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Get available years for filter
        $years = Bill::selectRaw('DISTINCT bill_year')
            ->orderBy('bill_year', 'desc')
            ->pluck('bill_year');

        return view('admin.bills.index', compact('bills', 'years'));
    }

    public function show(Bill $bill)
    {
        $bill->load(['house', 'feeConfiguration', 'payments.resident']);

        return view('admin.bills.show', compact('bill'));
    }

    public function generateForm()
    {
        $currentFee = \App\Models\FeeConfiguration::getCurrentFee();
        $currentFeeAmount = $currentFee ? $currentFee->amount : 20;
        $housesCount = House::billable()->count();

        return view('admin.bills.generate', compact('currentFeeAmount', 'housesCount'));
    }

    public function generateYearly(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'amount' => 'required|numeric|min:1|max:9999',
        ]);

        $result = $this->billingService->generateYearlyBills(
            $validated['year'],
            $validated['amount']
        );

        if ($result['success']) {
            return redirect()->route('admin.bills.index')
                ->with('success', "Berjaya menjana {$result['generated']} bil untuk {$result['houses']} rumah bagi tahun {$validated['year']} dengan kadar RM " . number_format($validated['amount'], 2) . "/bulan. Notifikasi telah dihantar.");
        }

        return back()->with('error', $result['message']);
    }

    public function edit(Bill $bill)
    {
        // Cannot edit paid bills
        if ($bill->status === 'paid') {
            return back()->with('error', 'Cannot edit paid bills');
        }

        return view('admin.bills.edit', compact('bill'));
    }

    public function update(Request $request, Bill $bill)
    {
        // Cannot edit paid bills (for non-super-admin)
        if ($bill->status === 'paid' && !auth()->user()->isSuperAdmin()) {
            return back()->with('error', 'Cannot edit paid bills');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
        ]);

        $oldValues = $bill->toArray();

        $bill->update($validated);

        AuditLog::logUpdate($bill, $oldValues, "Bill {$bill->bill_no} updated");

        return redirect()->route('admin.bills.show', $bill)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Bill $bill)
    {
        // Cannot delete paid bills
        if ($bill->status === 'paid') {
            return back()->with('error', 'Cannot delete paid bills');
        }

        AuditLog::logDelete($bill, "Bill {$bill->bill_no} deleted");

        $bill->delete();

        return redirect()->route('admin.bills.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function outstanding(Request $request)
    {
        $houses = House::billable()
            ->whereHas('bills', function ($query) {
                $query->whereIn('status', ['unpaid', 'partial']);
            })
            ->with(['bills' => function ($query) {
                $query->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('bill_year')
                    ->orderBy('bill_month');
            }])
            ->get()
            ->map(function ($house) {
                $house->total_outstanding = $house->bills->sum(fn($b) => $b->outstanding_amount);
                return $house;
            })
            ->sortByDesc('total_outstanding');

        return view('admin.bills.outstanding', compact('houses'));
    }
}

