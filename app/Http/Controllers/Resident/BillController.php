<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\House;
use Illuminate\Http\Request;

class BillController extends Controller
{
    public function index(Request $request)
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

        if (!$membership || !$membership->can_view_bills) {
            abort(403, __('messages.unauthorized'));
        }

        $query = Bill::where('house_id', $house->id);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('bill_year', $request->year);
        }

        $bills = $query->orderBy('bill_year', 'desc')
            ->orderBy('bill_month', 'desc')
            ->paginate(12);

        $years = Bill::where('house_id', $house->id)
            ->selectRaw('DISTINCT bill_year')
            ->orderBy('bill_year', 'desc')
            ->pluck('bill_year');

        return view('resident.bills.index', compact('bills', 'house', 'years'));
    }

    public function show(Bill $bill)
    {
        $resident = auth()->user()->resident;

        // Verify access
        $membership = $resident->houseMemberships()
            ->where('house_id', $bill->house_id)
            ->where('status', 'active')
            ->first();

        if (!$membership || !$membership->can_view_bills) {
            abort(403);
        }

        $bill->load(['house', 'payments']);

        return view('resident.bills.show', compact('bill'));
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

        // Return first active membership's house
        $membership = $resident->houseMemberships()
            ->where('status', 'active')
            ->with('house')
            ->first();

        return $membership?->house;
    }
}

