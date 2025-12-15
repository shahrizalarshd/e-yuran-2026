<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    public function index(Request $request)
    {
        $query = House::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('house_no', 'like', "%{$search}%")
                    ->orWhere('street_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by registration
        if ($request->filled('is_registered')) {
            $query->where('is_registered', $request->is_registered === 'true');
        }

        $houses = $query->withCount(['bills', 'members'])
            ->orderBy('street_name')
            ->orderBy('house_no')
            ->paginate(20);

        return view('admin.houses.index', compact('houses'));
    }

    public function create()
    {
        return view('admin.houses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'house_no' => 'required|string|max:20',
            'street_name' => 'required|string|max:100',
            'is_registered' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'required|in:occupied,vacant',
        ]);

        $validated['is_registered'] = $request->boolean('is_registered');
        $validated['is_active'] = $request->boolean('is_active', true);

        $house = House::create($validated);

        AuditLog::logCreate($house, "House {$house->house_no} created");

        return redirect()->route('admin.houses.index')
            ->with('success', __('messages.saved_successfully'));
    }

    public function show(House $house)
    {
        $house->load([
            'occupancies' => function ($q) {
                $q->with('resident')->orderBy('start_date', 'desc');
            },
            'members' => function ($q) {
                $q->with('resident')->orderBy('status');
            },
            'bills' => function ($q) {
                $q->orderBy('bill_year', 'desc')->orderBy('bill_month', 'desc');
            },
            'payments' => function ($q) {
                $q->with('resident')->orderBy('created_at', 'desc');
            },
        ]);

        $currentOwner = $house->currentOwner();
        $currentTenant = $house->currentTenant();
        $currentPayer = $house->currentPayer();

        // Build timeline
        $timeline = $this->buildHouseTimeline($house);

        return view('admin.houses.show', compact(
            'house',
            'currentOwner',
            'currentTenant',
            'currentPayer',
            'timeline'
        ));
    }

    /**
     * Build comprehensive timeline for house history
     */
    private function buildHouseTimeline(House $house): array
    {
        $events = [];

        // Add house creation
        $events[] = [
            'type' => 'house_created',
            'date' => $house->created_at,
            'title' => 'Rumah Dicipta',
            'description' => "Rumah {$house->full_address} didaftarkan dalam sistem",
            'icon' => 'home',
            'color' => 'blue',
        ];

        // Add occupancy events (owners and tenants)
        foreach ($house->occupancies as $occupancy) {
            // Start event
            $events[] = [
                'type' => 'occupancy_start',
                'date' => $occupancy->start_date,
                'title' => ucfirst($occupancy->role) . ' Masuk',
                'description' => "{$occupancy->resident->name} mula mendiami rumah sebagai " . __('messages.' . $occupancy->role),
                'meta' => $occupancy->is_payer ? 'Pembayar' : null,
                'icon' => $occupancy->role === 'owner' ? 'user-check' : 'users',
                'color' => $occupancy->role === 'owner' ? 'green' : 'purple',
            ];

            // End event (if exists)
            if ($occupancy->end_date) {
                $events[] = [
                    'type' => 'occupancy_end',
                    'date' => $occupancy->end_date,
                    'title' => ucfirst($occupancy->role) . ' Keluar',
                    'description' => "{$occupancy->resident->name} tidak lagi mendiami rumah",
                    'icon' => 'user-minus',
                    'color' => 'gray',
                ];
            }
        }

        // Add member events
        foreach ($house->members as $member) {
            // Member registered
            $events[] = [
                'type' => 'member_registered',
                'date' => $member->created_at,
                'title' => 'Ahli Rumah Mendaftar',
                'description' => "{$member->resident->name} mendaftar sebagai " . __('messages.' . $member->relationship),
                'icon' => 'user-plus',
                'color' => 'indigo',
            ];

            // Member approved
            if ($member->approved_at && $member->status === 'active') {
                $approver = $member->approver ? " oleh {$member->approver->name}" : '';
                $events[] = [
                    'type' => 'member_approved',
                    'date' => $member->approved_at,
                    'title' => 'Ahli Rumah Diluluskan',
                    'description' => "{$member->resident->name} telah diluluskan{$approver}",
                    'icon' => 'check-circle',
                    'color' => 'green',
                ];
            }

            // Member rejected
            if ($member->status === 'rejected' && $member->approved_at) {
                $events[] = [
                    'type' => 'member_rejected',
                    'date' => $member->approved_at,
                    'title' => 'Ahli Rumah Ditolak',
                    'description' => "{$member->resident->name} ditolak" . ($member->rejection_reason ? ": {$member->rejection_reason}" : ''),
                    'icon' => 'x-circle',
                    'color' => 'red',
                ];
            }
        }

        // Add audit log events for important changes
        $auditLogs = AuditLog::where('model_type', House::class)
            ->where('model_id', $house->id)
            ->whereIn('action', ['update'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($auditLogs as $log) {
            $newValues = json_decode($log->new_values, true);
            $oldValues = json_decode($log->old_values, true);

            // Status changes
            if (isset($newValues['status']) && isset($oldValues['status']) && $newValues['status'] !== $oldValues['status']) {
                $events[] = [
                    'type' => 'status_changed',
                    'date' => $log->created_at,
                    'title' => 'Status Rumah Berubah',
                    'description' => "Status berubah dari " . __('messages.' . $oldValues['status']) . " ke " . __('messages.' . $newValues['status']),
                    'icon' => 'refresh',
                    'color' => 'yellow',
                ];
            }

            // Registration changes
            if (isset($newValues['is_registered']) && isset($oldValues['is_registered']) && $newValues['is_registered'] !== $oldValues['is_registered']) {
                $events[] = [
                    'type' => 'registration_changed',
                    'date' => $log->created_at,
                    'title' => $newValues['is_registered'] ? 'Rumah Didaftarkan' : 'Pendaftaran Dibatalkan',
                    'description' => $newValues['is_registered'] ? 'Rumah berdaftar untuk bayaran yuran' : 'Rumah tidak lagi berdaftar',
                    'icon' => $newValues['is_registered'] ? 'check-circle' : 'x-circle',
                    'color' => $newValues['is_registered'] ? 'green' : 'red',
                ];
            }

            // Active status changes
            if (isset($newValues['is_active']) && isset($oldValues['is_active']) && $newValues['is_active'] !== $oldValues['is_active']) {
                $events[] = [
                    'type' => 'active_status_changed',
                    'date' => $log->created_at,
                    'title' => $newValues['is_active'] ? 'Rumah Diaktifkan' : 'Rumah Dinyahaktifkan',
                    'description' => $newValues['is_active'] ? 'Rumah diaktifkan semula' : 'Rumah dinyahaktifkan',
                    'icon' => $newValues['is_active'] ? 'toggle-right' : 'toggle-left',
                    'color' => $newValues['is_active'] ? 'green' : 'gray',
                ];
            }
        }

        // Sort by date descending (newest first)
        usort($events, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return $events;
    }

    public function edit(House $house)
    {
        return view('admin.houses.edit', compact('house'));
    }

    public function update(Request $request, House $house)
    {
        $validated = $request->validate([
            'house_no' => 'required|string|max:20',
            'street_name' => 'required|string|max:100',
            'is_registered' => 'boolean',
            'is_active' => 'boolean',
            'status' => 'required|in:occupied,vacant',
        ]);

        $oldValues = $house->toArray();

        $validated['is_registered'] = $request->boolean('is_registered');
        $validated['is_active'] = $request->boolean('is_active');

        $house->update($validated);

        AuditLog::logUpdate($house, $oldValues, "House {$house->house_no} updated");

        return redirect()->route('admin.houses.show', $house)
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(House $house)
    {
        // Check if house has bills or payments
        if ($house->bills()->exists() || $house->payments()->exists()) {
            return back()->with('error', 'Cannot delete house with bills or payments');
        }

        AuditLog::logDelete($house, "House {$house->house_no} deleted");

        $house->delete();

        return redirect()->route('admin.houses.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    public function assignOwner(Request $request, House $house)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'start_date' => 'required|date',
        ]);

        // End current owner occupancy
        HouseOccupancy::where('house_id', $house->id)
            ->where('role', 'owner')
            ->whereNull('end_date')
            ->update(['end_date' => now()]);

        // Create new occupancy
        $occupancy = HouseOccupancy::create([
            'house_id' => $house->id,
            'resident_id' => $validated['resident_id'],
            'role' => 'owner',
            'start_date' => $validated['start_date'],
            'is_payer' => true, // Owner is default payer
        ]);

        AuditLog::logCreate($occupancy, "Owner assigned to house {$house->house_no}");

        return back()->with('success', 'Owner assigned successfully');
    }

    public function assignTenant(Request $request, House $house)
    {
        $validated = $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'start_date' => 'required|date',
            'is_payer' => 'boolean',
        ]);

        // End current tenant occupancy
        HouseOccupancy::where('house_id', $house->id)
            ->where('role', 'tenant')
            ->whereNull('end_date')
            ->update(['end_date' => now()]);

        // Create new occupancy
        $occupancy = HouseOccupancy::create([
            'house_id' => $house->id,
            'resident_id' => $validated['resident_id'],
            'role' => 'tenant',
            'start_date' => $validated['start_date'],
            'is_payer' => $request->boolean('is_payer'),
        ]);

        // If tenant is payer, update owner payer status
        if ($request->boolean('is_payer')) {
            HouseOccupancy::where('house_id', $house->id)
                ->where('role', 'owner')
                ->whereNull('end_date')
                ->update(['is_payer' => false]);
        }

        AuditLog::logCreate($occupancy, "Tenant assigned to house {$house->house_no}");

        return back()->with('success', 'Tenant assigned successfully');
    }
}

