<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\MembershipFee;
use App\Models\MembershipFeeConfiguration;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipFeeController extends Controller
{
    /**
     * Display listing of membership fees
     */
    public function index(Request $request)
    {
        $query = MembershipFee::with(['house', 'resident'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('fee_year', $request->year);
        }

        // Filter by street
        if ($request->filled('street')) {
            $query->whereHas('house', function ($q) use ($request) {
                $q->where('street_name', $request->street);
            });
        }

        // Search by house or owner name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('legacy_owner_name', 'like', "%{$search}%")
                    ->orWhereHas('house', function ($q2) use ($search) {
                        $q2->where('house_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('resident', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $membershipFees = $query->paginate(20)->withQueryString();

        // Get summary stats
        $stats = [
            'total' => MembershipFee::count(),
            'paid' => MembershipFee::where('status', 'paid')->count(),
            'unpaid' => MembershipFee::where('status', 'unpaid')->count(),
            'total_collected' => MembershipFee::where('status', 'paid')->sum('amount'),
            'total_outstanding' => MembershipFee::where('status', 'unpaid')->sum('amount'),
        ];

        // Get available years for filter
        $years = MembershipFee::distinct()->pluck('fee_year')->sort()->values();

        // Get streets for filter
        $streets = House::distinct()->pluck('street_name')->sort()->values();

        return view('admin.membership-fees.index', compact(
            'membershipFees',
            'stats',
            'years',
            'streets'
        ));
    }

    /**
     * Show membership fee details
     */
    public function show(MembershipFee $membershipFee)
    {
        $membershipFee->load(['house', 'resident']);

        return view('admin.membership-fees.show', compact('membershipFee'));
    }

    /**
     * Show form to edit membership fee
     */
    public function edit(MembershipFee $membershipFee)
    {
        $membershipFee->load(['house', 'resident']);

        return view('admin.membership-fees.edit', compact('membershipFee'));
    }

    /**
     * Update membership fee
     */
    public function update(Request $request, MembershipFee $membershipFee)
    {
        $validated = $request->validate([
            'status' => 'required|in:paid,unpaid',
            'amount' => 'required|numeric|min:0',
            'paid_at' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:255',
            'legacy_owner_name' => 'nullable|string|max:255',
        ]);

        $oldStatus = $membershipFee->status;

        // If marking as paid, set paid_at
        if ($validated['status'] === 'paid' && !$validated['paid_at']) {
            $validated['paid_at'] = now();
        }

        // If marking as unpaid, clear paid_at
        if ($validated['status'] === 'unpaid') {
            $validated['paid_at'] = null;
        }

        $membershipFee->update($validated);

        // Log the action
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'update_membership_fee',
            'model_type' => MembershipFee::class,
            'model_id' => $membershipFee->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $validated['status']],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('admin.membership-fees.index')
            ->with('success', __('messages.membership_fee_updated'));
    }

    /**
     * Mark membership fee as paid
     */
    public function markAsPaid(Request $request, MembershipFee $membershipFee)
    {
        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $membershipFee->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        // Log the action
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'mark_membership_fee_paid',
            'model_type' => MembershipFee::class,
            'model_id' => $membershipFee->id,
            'old_values' => ['status' => 'unpaid'],
            'new_values' => ['status' => 'paid'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->back()
            ->with('success', __('messages.membership_fee_marked_paid'));
    }

    /**
     * Configuration index - list all configs
     */
    public function configIndex()
    {
        $configurations = MembershipFeeConfiguration::orderBy('effective_from', 'desc')->get();
        $currentConfig = MembershipFeeConfiguration::getCurrentFee();

        return view('admin.membership-fees.config-index', compact('configurations', 'currentConfig'));
    }

    /**
     * Show form to create new configuration
     */
    public function configCreate()
    {
        return view('admin.membership-fees.config-create');
    }

    /**
     * Store new configuration
     */
    public function configStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['is_active'] = $request->has('is_active');

        MembershipFeeConfiguration::create($validated);

        return redirect()
            ->route('admin.membership-fees.config.index')
            ->with('success', __('messages.config_created'));
    }

    /**
     * Show form to edit configuration
     */
    public function configEdit(MembershipFeeConfiguration $configuration)
    {
        return view('admin.membership-fees.config-edit', compact('configuration'));
    }

    /**
     * Update configuration
     */
    public function configUpdate(Request $request, MembershipFeeConfiguration $configuration)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $configuration->update($validated);

        return redirect()
            ->route('admin.membership-fees.config.index')
            ->with('success', __('messages.config_updated'));
    }

    /**
     * Delete configuration
     */
    public function configDestroy(MembershipFeeConfiguration $configuration)
    {
        $configuration->delete();

        return redirect()
            ->route('admin.membership-fees.config.index')
            ->with('success', __('messages.config_deleted'));
    }
}

