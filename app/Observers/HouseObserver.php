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
     * 
     * NOTA: Auto-generation bil telah di-disable.
     * Bil kini dijana secara manual oleh admin untuk SEMUA rumah sekaligus.
     * Tidak perlu auto-generate bila rumah jadi billable.
     */
    public function updated(House $house): void
    {
        // Auto-generation disabled — bil dijana upfront untuk semua rumah
        // Logik lama disimpan di generateBillsForNewlyBillableHouse() untuk rujukan
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

