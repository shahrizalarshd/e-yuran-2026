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

        // Filter by date range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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

        $payments = $query->orderBy('created_at', 'desc')->paginate(20);

        // Stats
        $totalSuccess = Payment::where('status', 'success')->sum('amount');
        $totalPending = Payment::where('status', 'pending')->sum('amount');
        $todayCollection = Payment::where('status', 'success')
            ->whereDate('paid_at', today())
            ->sum('amount');

        return view('admin.payments.index', compact(
            'payments',
            'totalSuccess',
            'totalPending',
            'todayCollection'
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
            ->selectRaw("CAST(strftime('%m', paid_at) AS INTEGER) as month, SUM(amount) as total, COUNT(*) as count")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $payments = $query->with(['house', 'resident'])
            ->orderBy('paid_at', 'desc')
            ->paginate(50);

        $years = Payment::selectRaw("DISTINCT CAST(strftime('%Y', paid_at) AS INTEGER) as year")
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

