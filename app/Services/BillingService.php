<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\House;
use App\Models\HouseOccupancy;
use App\Models\MembershipFee;
use App\Models\MembershipFeeConfiguration;
use App\Models\FeeConfiguration;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * BillingService - MODEL HIBRID
 * 
 * Yuran Keahlian: per OCCUPANCY (reset bila owner tukar)
 * Yuran Tahunan: per RUMAH (inherit bila owner tukar)
 */
class BillingService
{
    /**
     * Generate yearly bills for all houses with active members
     * MODEL HIBRID: Bil tahunan attach ke rumah, bukan occupancy
     * 
     * @param int $year The year to generate bills for
     * @param float|null $customAmount Optional custom amount per month
     */
    public function generateYearlyBills(int $year, ?float $customAmount = null): array
    {
        $feeConfig = FeeConfiguration::getCurrentFee();
        $amount = $customAmount ?? ($feeConfig ? $feeConfig->amount : null);
        
        if (!$amount) {
            Log::error('Yearly bill generation failed: No amount specified and no active fee configuration');
            return [
                'success' => false,
                'message' => 'Tiada amaun ditetapkan dan tiada konfigurasi yuran aktif',
                'generated' => 0,
                'houses' => 0,
            ];
        }

        // MODEL HIBRID: Get houses with active members (occupancy is_member = true)
        $houses = House::billable()->get();
        $totalGenerated = 0;
        $housesProcessed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($houses as $house) {
                $billsForHouse = 0;
                
                // Generate bills for all 12 months
                for ($month = 1; $month <= 12; $month++) {
                    $bill = $this->generateBillForHouseWithAmount($house, $year, $month, $amount, $feeConfig);
                    if ($bill) {
                        $totalGenerated++;
                        $billsForHouse++;
                    }
                }
                
                if ($billsForHouse > 0) {
                    $housesProcessed++;
                }
            }

            DB::commit();

            // Log the action
            AuditLog::logAction(
                'generate_yearly_bills',
                "Generated {$totalGenerated} bills for {$housesProcessed} houses for year {$year} at RM " . number_format($amount, 2) . "/month"
            );

            // Send notifications
            $this->notifyResidentsYearlyBills($year, $amount);
            $this->notifyAdminYearlyBillsGenerated($year, $totalGenerated, $housesProcessed);

            Log::info("Yearly bills generated", [
                'year' => $year,
                'total_bills' => $totalGenerated,
                'houses' => $housesProcessed,
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'message' => "Generated {$totalGenerated} bills for {$housesProcessed} houses",
                'generated' => $totalGenerated,
                'houses' => $housesProcessed,
                'amount' => $amount,
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Yearly bill generation failed', ['error' => $e->getMessage()]);
            
            return [
                'success' => false,
                'message' => 'Gagal menjana bil tahunan: ' . $e->getMessage(),
                'generated' => 0,
                'houses' => 0,
            ];
        }
    }

    /**
     * Generate a bill for a specific house with a custom amount
     * MODEL HIBRID: Bil attach ke house_id
     */
    private function generateBillForHouseWithAmount(House $house, int $year, int $month, float $amount, ?FeeConfiguration $feeConfig = null): ?Bill
    {
        // Check if house has active member
        if (!$house->is_member) {
            return null;
        }

        // Check if bill already exists
        $existingBill = Bill::where('house_id', $house->id)
            ->where('bill_year', $year)
            ->where('bill_month', $month)
            ->first();

        if ($existingBill) {
            return null;
        }

        // Calculate due date
        $dueDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $bill = Bill::create([
            'house_id' => $house->id,
            'fee_configuration_id' => $feeConfig?->id,
            'bill_no' => Bill::generateBillNo($year, $month, $house->id),
            'bill_year' => $year,
            'bill_month' => $month,
            'amount' => $amount,
            'status' => 'unpaid',
            'paid_amount' => 0,
            'due_date' => $dueDate,
        ]);

        AuditLog::logCreate($bill, "Annual bill generated for house {$house->house_no}");

        return $bill;
    }

    /**
     * Generate bills for a newly registered house
     * MODEL HIBRID: Only generate if house has active member
     */
    public function generateBillsForNewHouse(House $house, int $fromMonth = null, int $year = null): array
    {
        $year = $year ?? now()->year;
        $fromMonth = $fromMonth ?? now()->month;
        
        $feeConfig = FeeConfiguration::getCurrentFee();
        
        if (!$feeConfig) {
            return [
                'success' => false,
                'message' => 'No active fee configuration found',
                'generated' => 0,
            ];
        }

        // Check if house has active member
        if (!$house->is_member) {
            return [
                'success' => false,
                'message' => 'House does not have an active member',
                'generated' => 0,
            ];
        }

        $generated = 0;

        DB::beginTransaction();
        try {
            for ($month = $fromMonth; $month <= 12; $month++) {
                $bill = $this->generateBillForHouse($house, $year, $month, $feeConfig);
                if ($bill) {
                    $generated++;
                }
            }

            DB::commit();

            if ($generated > 0) {
                AuditLog::logAction(
                    'generate_new_house_bills',
                    "Generated {$generated} annual bills for house {$house->house_no} ({$fromMonth}/{$year} - 12/{$year})"
                );

                $this->notifyHouseOwnerNewBills($house, $year, $fromMonth, $generated, $feeConfig->amount);
            }

            return [
                'success' => true,
                'message' => "Generated {$generated} bills for house {$house->house_no}",
                'generated' => $generated,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('New house bill generation failed', [
                'house_id' => $house->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
                'generated' => 0,
            ];
        }
    }

    /**
     * Generate membership fee for an occupancy
     * MODEL HIBRID: Yuran keahlian per occupancy
     */
    public function generateMembershipFee(HouseOccupancy $occupancy): array
    {
        $config = MembershipFeeConfiguration::getActiveConfig();
        
        if (!$config) {
            return [
                'success' => false,
                'message' => 'No active membership fee configuration found',
            ];
        }

        // Check if already a member
        if ($occupancy->is_member) {
            return [
                'success' => false,
                'message' => 'Occupancy is already a member',
            ];
        }

        DB::beginTransaction();
        try {
            $membershipFee = MembershipFee::createForOccupancy($occupancy, $config->amount);

            DB::commit();

            AuditLog::logAction(
                'membership_fee_generated',
                "Membership fee generated for occupancy {$occupancy->id}, amount: RM " . number_format($config->amount, 2)
            );

            return [
                'success' => true,
                'message' => 'Membership fee generated',
                'membership_fee' => $membershipFee,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Membership fee generation failed', [
                'occupancy_id' => $occupancy->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Register occupancy as member and generate bills
     * MODEL HIBRID: Complete flow untuk daftar ahli baru
     */
    public function registerMember(HouseOccupancy $occupancy, float $membershipAmount): array
    {
        DB::beginTransaction();
        try {
            // 1. Register as member
            $occupancy->registerAsMember($membershipAmount);

            // 2. Generate annual bills for remaining year
            $house = $occupancy->house;
            $result = $this->generateBillsForNewHouse($house);

            DB::commit();

            AuditLog::logAction(
                'member_registered',
                "Occupancy {$occupancy->id} registered as member. Membership: RM " . number_format($membershipAmount, 2) . 
                ". Annual bills generated: {$result['generated']}"
            );

            return [
                'success' => true,
                'message' => 'Member registered successfully',
                'bills_generated' => $result['generated'],
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Member registration failed', [
                'occupancy_id' => $occupancy->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle ownership transfer
     * MODEL HIBRID: Keahlian reset, bil tahunan kekal dengan rumah
     */
    public function handleOwnershipTransfer(House $house, HouseOccupancy $oldOwner, HouseOccupancy $newOwner): array
    {
        DB::beginTransaction();
        try {
            // 1. End old occupancy
            $oldOwner->endOccupancy();

            // 2. Bills stay with house (no action needed - MODEL HIBRID)
            // Outstanding bills will be visible to new owner

            // 3. Log audit
            $outstandingAmount = $house->outstanding_amount;
            
            AuditLog::logAction(
                'ownership_transfer',
                "House {$house->house_no} transferred. Old owner occupancy: {$oldOwner->id}, New owner occupancy: {$newOwner->id}. " .
                "Outstanding bills: RM " . number_format($outstandingAmount, 2) . " inherited by new owner."
            );

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ownership transferred successfully',
                'outstanding_inherited' => $outstandingAmount,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ownership transfer failed', [
                'house_id' => $house->id,
                'error' => $e->getMessage(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Notify residents about yearly bills
     */
    private function notifyResidentsYearlyBills(int $year, float $monthlyAmount): void
    {
        $residents = User::where('role', 'resident')
            ->whereHas('resident', function ($q) {
                $q->whereHas('occupancies', function ($oq) {
                    $oq->active()->member();
                });
            })
            ->get();

        $totalYearly = $monthlyAmount * 12;

        foreach ($residents as $user) {
            SystemNotification::create([
                'user_id' => $user->id,
                'type' => 'yearly_bills_generated',
                'title' => "Bil Tahunan {$year} Telah Dijana",
                'message' => "Bil yuran bulanan untuk tahun {$year} telah dijana. " .
                    "Jumlah: RM " . number_format($monthlyAmount, 2) . " × 12 bulan = RM " . number_format($totalYearly, 2),
                'data' => json_encode(['year' => $year, 'monthly_amount' => $monthlyAmount]),
            ]);
        }
    }

    /**
     * Notify admin about yearly bills generation
     */
    private function notifyAdminYearlyBillsGenerated(int $year, int $totalBills, int $houses): void
    {
        $admins = User::whereIn('role', ['super_admin', 'treasurer'])->get();

        foreach ($admins as $admin) {
            SystemNotification::create([
                'user_id' => $admin->id,
                'type' => 'yearly_bills_generated',
                'title' => "Bil Tahunan {$year} Berjaya Dijana",
                'message' => "Sistem telah menjana {$totalBills} bil untuk {$houses} rumah bagi tahun {$year}.",
                'data' => json_encode([
                    'year' => $year,
                    'total_bills' => $totalBills,
                    'houses' => $houses,
                ]),
            ]);
        }
    }

    /**
     * Notify house owner about new bills
     */
    private function notifyHouseOwnerNewBills(House $house, int $year, int $fromMonth, int $billCount, float $amount): void
    {
        $memberOccupancy = $house->activeMemberOccupancy();
        if (!$memberOccupancy || !$memberOccupancy->resident) {
            return;
        }

        $user = User::where('resident_id', $memberOccupancy->resident_id)->first();
        if (!$user) {
            return;
        }

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
            5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
        ];

        SystemNotification::create([
            'user_id' => $user->id,
            'type' => 'new_house_bills',
            'title' => 'Bil Yuran Telah Dijana',
            'message' => "Bil yuran untuk rumah {$house->full_address} telah dijana dari {$months[$fromMonth]} hingga Disember {$year}. " .
                "Jumlah: {$billCount} bil × RM " . number_format($amount, 2),
            'data' => json_encode([
                'house_id' => $house->id,
                'year' => $year,
                'from_month' => $fromMonth,
                'bill_count' => $billCount,
            ]),
        ]);
    }

    /**
     * Generate bills for all billable houses for a given month/year
     */
    public function generateMonthlyBills(int $year, int $month): array
    {
        $feeConfig = FeeConfiguration::getFeeForDate(now()->setYear($year)->setMonth($month)->startOfMonth());
        
        if (!$feeConfig) {
            return [
                'success' => false,
                'message' => 'No active fee configuration found for this period',
                'generated' => 0,
                'skipped' => 0,
            ];
        }

        // MODEL HIBRID: Get houses with active members
        $houses = House::billable()->get();
        $generated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($houses as $house) {
            try {
                $result = $this->generateBillForHouse($house, $year, $month, $feeConfig);
                if ($result) {
                    $generated++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors[] = "House {$house->house_no}: {$e->getMessage()}";
                Log::error('Bill generation failed', [
                    'house_id' => $house->id,
                    'year' => $year,
                    'month' => $month,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        AuditLog::logAction('generate_bills', "Generated {$generated} annual bills for {$month}/{$year}");

        return [
            'success' => true,
            'message' => "Generated {$generated} bills, skipped {$skipped}",
            'generated' => $generated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Generate a bill for a specific house
     */
    public function generateBillForHouse(House $house, int $year, int $month, ?FeeConfiguration $feeConfig = null): ?Bill
    {
        // Check if house has active member
        if (!$house->is_member) {
            return null;
        }

        // Check if bill already exists
        $existingBill = Bill::where('house_id', $house->id)
            ->where('bill_year', $year)
            ->where('bill_month', $month)
            ->first();

        if ($existingBill) {
            return null;
        }

        if (!$feeConfig) {
            $feeConfig = FeeConfiguration::getFeeForDate(now()->setYear($year)->setMonth($month)->startOfMonth());
        }

        if (!$feeConfig) {
            throw new \Exception('No active fee configuration found');
        }

        $dueDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $bill = Bill::create([
            'house_id' => $house->id,
            'fee_configuration_id' => $feeConfig->id,
            'bill_no' => Bill::generateBillNo($year, $month, $house->id),
            'bill_year' => $year,
            'bill_month' => $month,
            'amount' => $feeConfig->amount,
            'status' => 'unpaid',
            'paid_amount' => 0,
            'due_date' => $dueDate,
        ]);

        AuditLog::logCreate($bill, "Annual bill generated for house {$house->house_no}");

        return $bill;
    }

    /**
     * Get outstanding bills for a house
     */
    public function getOutstandingBills(House $house)
    {
        return Bill::where('house_id', $house->id)
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bill_year')
            ->orderBy('bill_month')
            ->get();
    }

    /**
     * Get total outstanding amount for a house
     */
    public function getTotalOutstanding(House $house): float
    {
        return $this->getOutstandingBills($house)
            ->sum(fn($bill) => $bill->outstanding_amount);
    }

    /**
     * Get bills for current year
     */
    public function getCurrentYearBills(House $house)
    {
        return Bill::where('house_id', $house->id)
            ->where('bill_year', now()->year)
            ->orderBy('bill_month')
            ->get();
    }

    /**
     * Get system-wide statistics
     * MODEL HIBRID: Updated to use new model
     */
    public function getStatistics(): array
    {
        $totalHouses = House::count();
        $housesWithMembers = House::billable()->count();

        $totalCollection = Bill::where('status', 'paid')->sum('paid_amount');
        $totalOutstanding = Bill::whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as outstanding')
            ->value('outstanding') ?? 0;

        $currentMonthCollection = Bill::where('status', 'paid')
            ->where('bill_year', now()->year)
            ->where('bill_month', now()->month)
            ->sum('paid_amount');

        $overdueCount = Bill::overdue()->count();

        // Membership statistics
        $totalMembers = HouseOccupancy::active()->member()->count();
        $membershipCollection = MembershipFee::paid()->sum('amount');

        return [
            'total_houses' => $totalHouses,
            'houses_with_members' => $housesWithMembers,
            'total_members' => $totalMembers,
            'total_collection' => $totalCollection,
            'membership_collection' => $membershipCollection,
            'total_outstanding' => $totalOutstanding,
            'current_month_collection' => $currentMonthCollection,
            'overdue_count' => $overdueCount,
        ];
    }
}
