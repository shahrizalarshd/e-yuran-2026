<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\HouseMember;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Mail\UserVerificationApproved;
use App\Mail\UserVerificationRejected;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with(['user', 'houseMemberships.house']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('ic_number', 'like', "%{$search}%");
            });
        }

        $residents = $query->orderBy('name')->paginate(20);

        return view('admin.residents.index', compact('residents'));
    }

    public function show(Resident $resident)
    {
        $resident->load([
            'user',
            'occupancies.house',
            'houseMemberships.house',
            'payments' => function ($q) {
                $q->orderBy('created_at', 'desc')->limit(10);
            },
        ]);

        return view('admin.residents.show', compact('resident'));
    }

    public function pendingVerifications()
    {
        $pendingMembers = HouseMember::with(['house', 'resident.user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->paginate(20);

        return view('admin.residents.pending', compact('pendingMembers'));
    }

    public function approve(HouseMember $houseMember)
    {
        $oldValues = $houseMember->toArray();
        
        $houseMember->approve(auth()->user());

        AuditLog::logUpdate($houseMember, $oldValues, "Member approved for house {$houseMember->house->house_no}");

        // Notify the resident
        if ($houseMember->resident->user) {
            // System notification
            SystemNotification::notifySuccess(
                $houseMember->resident->user,
                __('messages.approved_successfully'),
                __('Pendaftaran anda untuk rumah :house telah diluluskan.', ['house' => $houseMember->house->full_address]),
                route('resident.dashboard')
            );

            // Email notification
            try {
                Mail::to($houseMember->resident->email)->send(new UserVerificationApproved($houseMember));
            } catch (\Exception $e) {
                \Log::error('Failed to send verification approval email', [
                    'email' => $houseMember->resident->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return back()->with('success', __('messages.approved_successfully'));
    }

    public function reject(Request $request, HouseMember $houseMember)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $oldValues = $houseMember->toArray();

        $houseMember->reject(auth()->user(), $validated['rejection_reason'] ?? null);

        AuditLog::logUpdate($houseMember, $oldValues, "Member rejected for house {$houseMember->house->house_no}");

        // Notify the resident
        if ($houseMember->resident->user) {
            // System notification
            SystemNotification::notifyError(
                $houseMember->resident->user,
                __('messages.rejected_successfully'),
                __('Pendaftaran anda untuk rumah :house telah ditolak. :reason', [
                    'house' => $houseMember->house->full_address,
                    'reason' => $validated['rejection_reason'] ?? '',
                ])
            );

            // Email notification
            try {
                Mail::to($houseMember->resident->email)->send(new UserVerificationRejected($houseMember));
            } catch (\Exception $e) {
                \Log::error('Failed to send verification rejection email', [
                    'email' => $houseMember->resident->email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return back()->with('success', __('messages.rejected_successfully'));
    }

    public function updatePermissions(Request $request, HouseMember $houseMember)
    {
        $validated = $request->validate([
            'can_view_bills' => 'boolean',
            'can_pay' => 'boolean',
        ]);

        $oldValues = $houseMember->toArray();

        $houseMember->update([
            'can_view_bills' => $request->boolean('can_view_bills'),
            'can_pay' => $request->boolean('can_pay'),
        ]);

        AuditLog::logUpdate($houseMember, $oldValues, "Member permissions updated");

        return back()->with('success', __('messages.updated_successfully'));
    }
}

