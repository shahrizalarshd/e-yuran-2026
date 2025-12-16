<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Bill;
use App\Models\Payment;
use App\Models\HouseMember;
use App\Services\BillingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    public function index(Request $request)
    {
        $stats = $this->billingService->getStatistics();
        
        $recentPayments = Payment::with(['house', 'resident'])
            ->where('status', 'success')
            ->orderBy('paid_at', 'desc')
            ->limit(10)
            ->get();

        $pendingVerifications = HouseMember::with(['house', 'resident'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $overdueHouses = House::billable()
            ->whereHas('bills', function ($query) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', now());
            })
            ->with(['bills' => function ($query) {
                $query->where('status', 'unpaid')
                    ->where('due_date', '<', now());
            }])
            ->limit(10)
            ->get();

        // Get available years from both Bill and Payment tables
        $billYears = Bill::select('bill_year')
            ->distinct()
            ->pluck('bill_year');
        
        $paymentYears = Payment::selectRaw("DISTINCT YEAR(paid_at) as year")
            ->whereNotNull('paid_at')
            ->where('paid_at', '>=', now()->subYears(10)->startOfYear())
            ->pluck('year');
        
        // Combine years from bills and payments
        $availableYears = $billYears->merge($paymentYears)->unique();
        
        // Add current year if not in list
        if (!$availableYears->contains(now()->year)) {
            $availableYears->push(now()->year);
        }
        
        // Sort years descending
        $availableYears = $availableYears->sortDesc()->values();

        // Get selected year from filter (default to current year)
        // Validate year to prevent invalid queries
        $selectedYear = $request->get('year', now()->year);
        $currentYear = (int) $selectedYear;
        
        // Ensure year is within valid range (not future, not too far in past)
        $minYear = now()->year - 20; // Allow up to 20 years of historical data
        $maxYear = now()->year;
        $currentYear = max($minYear, min($maxYear, $currentYear));
        
        // Get comparison year (default to previous year)
        $compareYear = $request->get('compare', $currentYear - 1);
        $compareYear = (int) $compareYear;
        $compareYear = max($minYear, min($maxYear, $compareYear));

        // Chart Data: Monthly Collection for Selected Year
        $monthlyCollection = Payment::where('status', 'success')
            ->whereYear('paid_at', $currentYear)
            ->selectRaw("MONTH(paid_at) as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill in missing months with 0
        $chartMonthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartMonthlyData[] = $monthlyCollection[$m] ?? 0;
        }

        // Chart Data: Year-over-Year Comparison (selected vs comparison year)
        $lastYear = $compareYear;
        $lastYearCollection = Payment::where('status', 'success')
            ->whereYear('paid_at', $compareYear)
            ->selectRaw("MONTH(paid_at) as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartLastYearData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartLastYearData[] = $lastYearCollection[$m] ?? 0;
        }

        // Chart Data: Bill Status Distribution for selected year
        $billStatusData = Bill::where('bill_year', $currentYear)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Chart Data: Weekly Collection (last 7 days) - Optimized single query
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();
        
        $weeklyData = Payment::where('status', 'success')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw("DATE(paid_at) as date, SUM(amount) as total")
            ->groupBy('date')
            ->pluck('total', 'date')
            ->toArray();
        
        $weeklyCollection = [];
        $weeklyLabels = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $weeklyLabels[] = $date->translatedFormat('D');
            $weeklyCollection[] = $weeklyData[$date->toDateString()] ?? 0;
        }

        // Collection rate for selected year (percentage of paid bills)
        $totalBills = Bill::where('bill_year', $currentYear)->count();
        $paidBills = Bill::where('bill_year', $currentYear)->where('status', 'paid')->count();
        $collectionRate = $totalBills > 0 ? round(($paidBills / $totalBills) * 100, 1) : 0;

        // Total collection for selected year
        $yearlyTotal = Payment::where('status', 'success')
            ->whereYear('paid_at', $currentYear)
            ->sum('amount');

        return view('admin.dashboard', compact(
            'stats',
            'recentPayments',
            'pendingVerifications',
            'overdueHouses',
            'chartMonthlyData',
            'chartLastYearData',
            'billStatusData',
            'weeklyCollection',
            'weeklyLabels',
            'collectionRate',
            'currentYear',
            'lastYear',
            'availableYears',
            'selectedYear',
            'compareYear',
            'yearlyTotal'
        ));
    }
}

