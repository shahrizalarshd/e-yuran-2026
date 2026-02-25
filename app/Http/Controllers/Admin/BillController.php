<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\House;
use App\Models\User;
use App\Models\AuditLog;
use App\Services\BillingService;
use App\Mail\BillPaymentReminder;
use App\Mail\BillOverdueReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

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

        // Filter by street
        if ($request->filled('street')) {
            $query->whereHas('house', function ($q) use ($request) {
                $q->where('street_name', $request->street);
            });
        }

        $sortBy = $request->get('sort_by', 'bill_year');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['bill_no', 'bill_year', 'bill_month', 'amount', 'status'];
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'bill_year'; }
        if (!in_array($sortDir, ['asc', 'desc'])) { $sortDir = 'desc'; }

        $bills = $query->orderBy($sortBy, $sortDir)
            ->paginate(20);

        // Get available years for filter
        $years = Bill::selectRaw('DISTINCT bill_year')
            ->orderBy('bill_year', 'desc')
            ->pluck('bill_year');

        $streets = \App\Models\House::distinct()->pluck('street_name')->sort()->values();

        return view('admin.bills.index', compact('bills', 'years', 'streets'));
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
        $houses = House::whereHas('bills', function ($query) {
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

        // Count for reminder button
        $overdueCount = Bill::overdue()->count();
        $unpaidCount = Bill::whereIn('status', ['unpaid', 'partial'])->count();

        return view('admin.bills.outstanding', compact('houses', 'overdueCount', 'unpaidCount'));
    }

    /**
     * Show the reminders page
     */
    public function reminders()
    {
        $overdueHouses = House::billable()
            ->whereHas('bills', function ($query) {
                $query->overdue();
            })
            ->withCount(['bills' => function ($query) {
                $query->overdue();
            }])
            ->get();

        $unpaidHouses = House::billable()
            ->whereHas('bills', function ($query) {
                $query->whereIn('status', ['unpaid', 'partial'])
                    ->where('due_date', '>=', now());
            })
            ->withCount(['bills' => function ($query) {
                $query->whereIn('status', ['unpaid', 'partial'])
                    ->where('due_date', '>=', now());
            }])
            ->get();

        $overdueCount = Bill::overdue()->count();
        $unpaidCount = Bill::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '>=', now())
            ->count();

        $totalOverdueAmount = Bill::overdue()
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $totalUnpaidAmount = Bill::whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '>=', now())
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        return view('admin.bills.reminders', compact(
            'overdueHouses',
            'unpaidHouses',
            'overdueCount',
            'unpaidCount',
            'totalOverdueAmount',
            'totalUnpaidAmount'
        ));
    }

    /**
     * Send bill reminders
     */
    public function sendReminders(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:all,unpaid,overdue',
        ]);

        $type = $validated['type'];

        // Run the command
        Artisan::call('bills:send-reminders', [
            '--type' => $type,
        ]);

        $output = Artisan::output();

        // Parse the output to get counts
        preg_match('/Peringatan Bil Belum Bayar\s*:\s*(\d+)/', $output, $unpaidMatch);
        preg_match('/Peringatan Bil Tertunggak\s*:\s*(\d+)/', $output, $overdueMatch);

        $unpaidSent = $unpaidMatch[1] ?? 0;
        $overdueSent = $overdueMatch[1] ?? 0;
        $totalSent = $unpaidSent + $overdueSent;

        if ($totalSent > 0) {
            $message = "Berjaya menghantar {$totalSent} peringatan email";
            if ($unpaidSent > 0) {
                $message .= " ({$unpaidSent} bil belum bayar";
            }
            if ($overdueSent > 0) {
                $message .= $unpaidSent > 0 ? ", " : " (";
                $message .= "{$overdueSent} bil tertunggak";
            }
            $message .= ")";

            return back()->with('success', $message);
        }

        return back()->with('info', 'Tiada peringatan yang perlu dihantar. Semua rumah sama ada tiada bil tertunggak atau tiada alamat email.');
    }

    /**
     * Send reminder to a specific house
     */
    public function sendReminderToHouse(House $house)
    {
        // Get user for this house
        $occupancy = $house->activeMemberOccupancy();
        
        if (!$occupancy || !$occupancy->resident_id) {
            return back()->with('error', 'Tiada ahli aktif untuk rumah ini.');
        }

        $user = User::where('resident_id', $occupancy->resident_id)->first();
        
        if (!$user || !$user->email) {
            return back()->with('error', 'Tiada alamat email untuk penghuni rumah ini.');
        }

        $unpaidBills = $house->bills()
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bill_year')
            ->orderBy('bill_month')
            ->get();

        if ($unpaidBills->isEmpty()) {
            return back()->with('info', 'Tiada bil tertunggak untuk rumah ini.');
        }

        $overdueBills = $unpaidBills->filter(fn($bill) => $bill->is_overdue);
        $regularUnpaidBills = $unpaidBills->filter(fn($bill) => !$bill->is_overdue);

        try {
            if ($overdueBills->count() > 0) {
                $totalOverdue = $overdueBills->sum('outstanding_amount');
                $oldestOverdueDays = (int) now()->diffInDays($overdueBills->min('due_date'));

                Mail::to($user->email)->queue(new BillOverdueReminder(
                    $user,
                    $house,
                    $overdueBills,
                    $totalOverdue,
                    $oldestOverdueDays
                ));

                AuditLog::logAction(
                    'send_single_reminder',
                    "Sent overdue reminder to {$user->email} for house {$house->house_no}"
                );

                return back()->with('success', "Peringatan bil tertunggak telah dihantar ke {$user->email}");
            } else {
                $totalOutstanding = $regularUnpaidBills->sum('outstanding_amount');

                Mail::to($user->email)->queue(new BillPaymentReminder(
                    $user,
                    $house,
                    $regularUnpaidBills,
                    $totalOutstanding
                ));

                AuditLog::logAction(
                    'send_single_reminder',
                    "Sent payment reminder to {$user->email} for house {$house->house_no}"
                );

                return back()->with('success', "Peringatan yuran telah dihantar ke {$user->email}");
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghantar email: ' . $e->getMessage());
        }
    }
}

