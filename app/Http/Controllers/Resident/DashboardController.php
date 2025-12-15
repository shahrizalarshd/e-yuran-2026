<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\House;
use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $resident = $user->resident;

        if (!$resident) {
            return view('resident.no-house');
        }

        // Get active house memberships
        $memberships = $resident->houseMemberships()
            ->where('status', 'active')
            ->with('house')
            ->get();

        if ($memberships->isEmpty()) {
            // Check if there are pending memberships
            $pendingMemberships = $resident->houseMemberships()
                ->where('status', 'pending')
                ->with('house')
                ->get();

            return view('resident.pending-verification', compact('pendingMemberships'));
        }

        // Get primary house (first active membership)
        $primaryMembership = $memberships->first();
        $house = $primaryMembership->house;

        // Get bills for this house
        $unpaidBills = Bill::where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bill_year')
            ->orderBy('bill_month')
            ->get();

        $paidBills = Bill::where('house_id', $house->id)
            ->where('status', 'paid')
            ->orderBy('bill_year', 'desc')
            ->orderBy('bill_month', 'desc')
            ->limit(12)
            ->get();

        $outstandingAmount = $unpaidBills->sum(fn($bill) => $bill->outstanding_amount);

        // Recent payments
        $recentPayments = $house->payments()
            ->where('status', 'success')
            ->orderBy('paid_at', 'desc')
            ->limit(5)
            ->get();

        // Chart Data: Bill Status for current year
        $currentYear = now()->year;
        $billStatusData = $this->getBillStatusData($house, $currentYear);
        
        // Chart Data: Payment History (last 12 months)
        $paymentHistoryData = $this->getPaymentHistoryData($house);

        return view('resident.dashboard', compact(
            'resident',
            'house',
            'memberships',
            'unpaidBills',
            'paidBills',
            'outstandingAmount',
            'recentPayments',
            'primaryMembership',
            'billStatusData',
            'paymentHistoryData',
            'currentYear'
        ));
    }

    /**
     * Get bill status data for donut chart
     */
    private function getBillStatusData(House $house, int $year): array
    {
        $bills = Bill::where('house_id', $house->id)
            ->where('bill_year', $year)
            ->get();

        $paid = $bills->where('status', 'paid')->count();
        $unpaid = $bills->where('status', 'unpaid')->count();
        $partial = $bills->where('status', 'partial')->count();
        $processing = $bills->where('status', 'processing')->count();

        $paidAmount = $bills->where('status', 'paid')->sum('amount');
        $unpaidAmount = $bills->whereIn('status', ['unpaid', 'partial'])->sum('outstanding_amount');

        return [
            'labels' => [__('messages.paid'), __('messages.unpaid'), __('Sebahagian'), __('Dalam Proses')],
            'data' => [$paid, $unpaid, $partial, $processing],
            'colors' => ['#22c55e', '#ef4444', '#f59e0b', '#3b82f6'],
            'paidAmount' => $paidAmount,
            'unpaidAmount' => $unpaidAmount,
            'totalBills' => $bills->count(),
        ];
    }

    /**
     * Get payment history data for bar chart (last 12 months)
     */
    private function getPaymentHistoryData(House $house): array
    {
        $labels = [];
        $data = [];
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mac', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Ogo',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dis'
        ];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            $labels[] = $monthNames[$month] . ' ' . substr($year, 2);
            
            // Get paid bills for this month
            $paidAmount = Bill::where('house_id', $house->id)
                ->where('bill_year', $year)
                ->where('bill_month', $month)
                ->where('status', 'paid')
                ->sum('paid_amount');
            
            $data[] = (float) $paidAmount;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    public function selectHouse(House $house)
    {
        $resident = auth()->user()->resident;

        // Verify membership
        $membership = $resident->houseMemberships()
            ->where('house_id', $house->id)
            ->where('status', 'active')
            ->first();

        if (!$membership) {
            abort(403);
        }

        // Store selected house in session
        session(['selected_house_id' => $house->id]);

        return redirect()->route('resident.dashboard');
    }
}

