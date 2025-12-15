<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeConfiguration;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class FeeConfigurationController extends Controller
{
    public function index()
    {
        $fees = FeeConfiguration::with('creator')
            ->orderBy('effective_from', 'desc')
            ->paginate(20);

        $currentFee = FeeConfiguration::getCurrentFee();

        return view('admin.fees.index', compact('fees', 'currentFee'));
    }

    public function create()
    {
        return view('admin.fees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['created_by'] = auth()->id();

        // If this fee is active, deactivate all other fees
        if ($validated['is_active']) {
            FeeConfiguration::where('is_active', true)->update(['is_active' => false]);
        }

        $fee = FeeConfiguration::create($validated);

        AuditLog::logCreate($fee, "Fee configuration '{$fee->name}' created");

        return redirect()->route('admin.fees.index')
            ->with('success', __('messages.saved_successfully'));
    }

    public function edit(FeeConfiguration $fee)
    {
        return view('admin.fees.edit', compact('fee'));
    }

    public function update(Request $request, FeeConfiguration $fee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_until' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $oldValues = $fee->toArray();

        $validated['is_active'] = $request->boolean('is_active');

        // If activating this fee, deactivate all other fees
        if ($validated['is_active'] && !$fee->is_active) {
            FeeConfiguration::where('is_active', true)
                ->where('id', '!=', $fee->id)
                ->update(['is_active' => false]);
        }

        $fee->update($validated);

        AuditLog::logUpdate($fee, $oldValues, "Fee configuration '{$fee->name}' updated");

        return redirect()->route('admin.fees.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(FeeConfiguration $fee)
    {
        // Check if fee is used in any bills
        if ($fee->bills()->exists()) {
            return back()->with('error', 'Cannot delete fee configuration that has been used in bills');
        }

        AuditLog::logDelete($fee, "Fee configuration '{$fee->name}' deleted");

        $fee->delete();

        return redirect()->route('admin.fees.index')
            ->with('success', __('messages.deleted_successfully'));
    }
}

