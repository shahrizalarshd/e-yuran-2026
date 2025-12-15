<?php

namespace App\Observers;

use App\Models\House;
use App\Services\BillingService;
use Illuminate\Support\Facades\Log;

class HouseObserver
{
    public function __construct(private BillingService $billingService)
    {
    }

    /**
     * Handle the House "updated" event.
     * When a house becomes billable (is_registered=true AND is_active=true),
     * automatically generate bills from current month to end of year.
     */
    public function updated(House $house): void
    {
        // Check if house just became billable
        $wasBillable = $house->getOriginal('is_registered') && $house->getOriginal('is_active');
        $isBillable = $house->is_registered && $house->is_active;

        // If house just became billable, generate bills
        if (!$wasBillable && $isBillable) {
            $this->generateBillsForNewlyBillableHouse($house);
        }
    }

    /**
     * Generate bills for a house that just became billable
     */
    private function generateBillsForNewlyBillableHouse(House $house): void
    {
        try {
            $result = $this->billingService->generateBillsForNewHouse($house);
            
            if ($result['success']) {
                Log::info("Auto-generated bills for newly billable house", [
                    'house_id' => $house->id,
                    'house_no' => $house->house_no,
                    'bills_generated' => $result['generated'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to auto-generate bills for house", [
                'house_id' => $house->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

