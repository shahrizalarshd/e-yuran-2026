<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Bill;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['house', 'resident']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by month and year
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_no', 'like', "%{$search}%")
                    ->orWhere('toyyibpay_billcode', 'like', "%{$search}%")
                    ->orWhere('toyyibpay_ref', 'like', "%{$search}%")
                    ->orWhereHas('house', function ($hq) use ($search) {
                        $hq->where('house_no', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by street
        if ($request->filled('street')) {
            $query->whereHas('house', function ($q) use ($request) {
                $q->where('street_name', $request->street);
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $allowedSorts = ['payment_no', 'amount', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) { $sortBy = 'created_at'; }
        if (!in_array($sortDir, ['asc', 'desc'])) { $sortDir = 'desc'; }

        $payments = $query->orderBy($sortBy, $sortDir)->paginate(20);

        // Stats
        $totalSuccess = Payment::where('status', 'success')->sum('amount');
        $totalPending = Payment::where('status', 'pending')->sum('amount');
        $todayCollection = Payment::where('status', 'success')
            ->whereDate('paid_at', today())
            ->sum('amount');

        $streets = \App\Models\House::distinct()->pluck('street_name')->sort()->values();

        return view('admin.payments.index', compact(
            'payments',
            'totalSuccess',
            'totalPending',
            'todayCollection',
            'streets'
        ));
    }

    public function show(Payment $payment)
    {
        $payment->load(['house', 'resident', 'bills']);

        return view('admin.payments.show', compact('payment'));
    }

    public function reconciliation(Request $request)
    {
        // Get payments that need reconciliation
        $pendingPayments = Payment::with(['house', 'resident', 'bills'])
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subMinutes(30))
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.payments.reconciliation', compact('pendingPayments'));
    }

    public function report(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month');

        $query = Payment::where('status', 'success');

        if ($year) {
            $query->whereYear('paid_at', $year);
        }

        if ($month) {
            $query->whereMonth('paid_at', $month);
        }

        // Calculate total first before modifying query with pagination
        $totalAmount = (clone $query)->sum('amount');

        // Monthly breakdown - use strftime for SQLite compatibility
        // Also apply month filter if selected
        $monthlyQuery = Payment::where('status', 'success')
            ->whereYear('paid_at', $year);
        
        if ($month) {
            $monthlyQuery->whereMonth('paid_at', $month);
        }
        
        $monthlyData = $monthlyQuery
            ->selectRaw("MONTH(paid_at) as month, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $payments = $query->with(['house', 'resident'])
            ->orderBy('paid_at', 'desc')
            ->paginate(50);

        $years = Payment::selectRaw("DISTINCT YEAR(paid_at) as year")
            ->whereNotNull('paid_at')
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('admin.payments.report', compact(
            'payments',
            'monthlyData',
            'totalAmount',
            'year',
            'month',
            'years'
        ));
    }
}

