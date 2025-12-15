<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\Resident;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Http\Request;

class HouseSettingsController extends Controller
{
    /**
     * Show house settings page (payer management)
     */
    public function index()
    {
        $user = auth()->user();
        $resident = $user->resident;

        if (!$resident) {
            return redirect()->route('resident.dashboard');
        }

        // Get active house membership where user is owner
        $ownerOccupancy = HouseOccupancy::where('resident_id', $resident->id)
            ->where('role', 'owner')
            ->whereNull('end_date')
            ->with(['house', 'house.occupancies' => function ($query) {
                $query->whereNull('end_date')->with('resident.user');
            }])
            ->first();

        if (!$ownerOccupancy) {
            return redirect()->route('resident.dashboard')
                ->with('error', __('Anda bukan pemilik rumah. Hanya pemilik boleh mengurus tetapan pembayar.'));
        }

        $house = $ownerOccupancy->house;
        
        // Get all active occupants (owner + tenants)
        $occupants = $house->occupancies()
            ->whereNull('end_date')
            ->with('resident.user')
            ->get();

        // Get current payer
        $currentPayer = $occupants->firstWhere('is_payer', true);

        return view('resident.house-settings', compact(
            'house',
            'occupants',
            'currentPayer',
            'ownerOccupancy'
        ));
    }

    /**
     * Update the payer for the house
     */
    public function updatePayer(Request $request, House $house)
    {
        $user = auth()->user();
        $resident = $user->resident;

        if (!$resident) {
            return redirect()->route('resident.dashboard');
        }

        // Verify user is owner of this house
        $ownerOccupancy = HouseOccupancy::where('house_id', $house->id)
            ->where('resident_id', $resident->id)
            ->where('role', 'owner')
            ->whereNull('end_date')
            ->first();

        if (!$ownerOccupancy) {
            abort(403, __('Anda tidak mempunyai kebenaran untuk mengubah pembayar.'));
        }

        $validated = $request->validate([
            'payer_resident_id' => 'required|exists:residents,id',
        ]);

        // Verify the selected payer is an occupant of this house
        $payerOccupancy = HouseOccupancy::where('house_id', $house->id)
            ->where('resident_id', $validated['payer_resident_id'])
            ->whereNull('end_date')
            ->first();

        if (!$payerOccupancy) {
            return back()->with('error', __('Pembayar yang dipilih bukan penghuni rumah ini.'));
        }

        // Get current payer for comparison
        $oldPayer = HouseOccupancy::where('house_id', $house->id)
            ->where('is_payer', true)
            ->whereNull('end_date')
            ->with('resident')
            ->first();

        $newPayerResident = Resident::with('user')->find($validated['payer_resident_id']);

        // Check if payer is actually changing
        if ($oldPayer && $oldPayer->resident_id == $validated['payer_resident_id']) {
            return back()->with('info', __('Pembayar tidak berubah.'));
        }

        // Update payer using the model method
        HouseOccupancy::setPayerForHouse($house, $newPayerResident);

        // Create audit log
        AuditLog::log(
            'payer_changed',
            $house,
            ['payer' => $oldPayer ? $oldPayer->resident->name : null],
            ['payer' => $newPayerResident->name, 'changed_by' => $user->name],
            "Pembayar untuk rumah {$house->house_no} ditukar dari " . 
            ($oldPayer ? $oldPayer->resident->name : 'tiada') . 
            " kepada {$newPayerResident->name}"
        );

        // Notify admins
        $admins = User::whereIn('role', ['super_admin', 'treasurer'])->get();
        foreach ($admins as $admin) {
            SystemNotification::notify(
                $admin,
                'Perubahan Pembayar',
                "Pembayar untuk rumah {$house->house_no} telah ditukar kepada {$newPayerResident->name} oleh pemilik {$user->name}.",
                'info'
            );
        }

        // Notify new payer (if different from owner)
        if ($newPayerResident->user && $newPayerResident->user->id !== $user->id) {
            SystemNotification::notify(
                $newPayerResident->user,
                'Anda Ditetapkan Sebagai Pembayar',
                "Anda telah ditetapkan sebagai pembayar untuk rumah {$house->full_address}. Sila pastikan bil dibayar tepat pada masanya.",
                'info'
            );
        }

        // Notify old payer (if exists and different)
        if ($oldPayer && $oldPayer->resident->user && $oldPayer->resident->user->id !== $user->id) {
            SystemNotification::notify(
                $oldPayer->resident->user,
                'Status Pembayar Ditarik Balik',
                "Anda tidak lagi menjadi pembayar untuk rumah {$house->full_address}. {$newPayerResident->name} kini adalah pembayar.",
                'info'
            );
        }

        return back()->with('success', __('Pembayar berjaya dikemas kini kepada :name.', ['name' => $newPayerResident->name]));
    }
}

