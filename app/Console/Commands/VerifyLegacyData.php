<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\House;
use App\Models\MembershipFee;
use App\Models\Payment;
use Illuminate\Console\Command;

class VerifyLegacyData extends Command
{
    protected $signature = 'verify:legacy-data 
                            {--detailed : Show detailed breakdown}';

    protected $description = 'Verify legacy data integrity and completeness';

    public function handle(): int
    {
        $this->info('╔════════════════════════════════════════════════════╗');
        $this->info('║   LEGACY DATA VERIFICATION                         ║');
        $this->info('║   e-Yuran Taman Tropika Kajang                     ║');
        $this->info('╚════════════════════════════════════════════════════╝');
        $this->newLine();

        // Check if any data exists
        $housesCount = House::count();
        $billsCount = Bill::count();
        $paymentsCount = Payment::count();
        $membershipCount = MembershipFee::count();

        if ($housesCount === 0) {
            $this->error('❌ NO DATA FOUND!');
            $this->newLine();
            $this->warn('Please run: php artisan import:legacy-data');
            $this->newLine();
            return Command::FAILURE;
        }

        // Overall Status
        $this->info('📊 OVERALL STATUS');
        $this->line('─────────────────────────────────────────────────────');
        $this->table(
            ['Item', 'Count', 'Status'],
            [
                ['Houses', number_format($housesCount), $this->getStatusIcon($housesCount > 0)],
                ['Bills (Legacy)', number_format(Bill::where('is_legacy', true)->count()), $this->getStatusIcon(Bill::where('is_legacy', true)->count() > 0)],
                ['Payments (Legacy)', number_format(Payment::where('is_legacy', true)->count()), $this->getStatusIcon(Payment::where('is_legacy', true)->count() > 0)],
                ['Membership Fees', number_format($membershipCount), $this->getStatusIcon($membershipCount > 0)],
            ]
        );
        $this->newLine();

        // Houses by Street
        $this->info('🏘️  HOUSES BY STREET');
        $this->line('─────────────────────────────────────────────────────');
        foreach ([2, 3, 4, 5] as $jalan) {
            $count = House::where('street_name', "Jalan Tropika {$jalan}")->count();
            $this->line("   Jalan Tropika {$jalan}: {$count} houses");
        }
        $this->newLine();

        // Bills Summary
        $this->info('📄 BILLS SUMMARY (2017-2025)');
        $this->line('─────────────────────────────────────────────────────');
        
        $totalLegacyBills = Bill::where('is_legacy', true)->count();
        $paidLegacyBills = Bill::where('is_legacy', true)->where('status', 'paid')->count();
        $unpaidLegacyBills = Bill::where('is_legacy', true)->where('status', 'unpaid')->count();
        
        $totalAmount = Bill::where('is_legacy', true)->sum('amount');
        $paidAmount = Bill::where('is_legacy', true)->where('status', 'paid')->sum('paid_amount');
        $unpaidAmount = Bill::where('is_legacy', true)->where('status', 'unpaid')->sum('amount');

        $this->table(
            ['Status', 'Count', 'Amount (RM)', 'Percentage'],
            [
                [
                    'Total Bills', 
                    number_format($totalLegacyBills), 
                    number_format($totalAmount, 2),
                    '100%'
                ],
                [
                    'Paid', 
                    number_format($paidLegacyBills), 
                    number_format($paidAmount, 2),
                    $totalLegacyBills > 0 ? round(($paidLegacyBills / $totalLegacyBills) * 100, 1) . '%' : '0%'
                ],
                [
                    'Unpaid', 
                    number_format($unpaidLegacyBills), 
                    number_format($unpaidAmount, 2),
                    $totalLegacyBills > 0 ? round(($unpaidLegacyBills / $totalLegacyBills) * 100, 1) . '%' : '0%'
                ],
            ]
        );
        $this->newLine();

        // Bills by Year
        if ($this->option('detailed')) {
            $this->info('📅 BILLS BY YEAR');
            $this->line('─────────────────────────────────────────────────────');
            
            $yearData = [];
            for ($year = 2017; $year <= 2025; $year++) {
                $total = Bill::where('is_legacy', true)->where('bill_year', $year)->count();
                $paid = Bill::where('is_legacy', true)->where('bill_year', $year)->where('status', 'paid')->count();
                $unpaid = Bill::where('is_legacy', true)->where('bill_year', $year)->where('status', 'unpaid')->count();
                
                $yearData[] = [
                    $year,
                    number_format($total),
                    number_format($paid),
                    number_format($unpaid),
                    $total > 0 ? round(($paid / $total) * 100, 1) . '%' : '0%'
                ];
            }
            
            $this->table(
                ['Year', 'Total Bills', 'Paid', 'Unpaid', 'Payment Rate'],
                $yearData
            );
            $this->newLine();
        }

        // Membership Fees
        $this->info('🎫 MEMBERSHIP FEES');
        $this->line('─────────────────────────────────────────────────────');
        
        $totalMembership = MembershipFee::count();
        $paidMembership = MembershipFee::where('status', 'paid')->count();
        $unpaidMembership = MembershipFee::where('status', 'unpaid')->count();
        
        $this->table(
            ['Status', 'Count', 'Amount (RM)'],
            [
                ['Total', number_format($totalMembership), number_format($totalMembership * 20, 2)],
                ['Paid', number_format($paidMembership), number_format($paidMembership * 20, 2)],
                ['Unpaid', number_format($unpaidMembership), number_format($unpaidMembership * 20, 2)],
            ]
        );
        $this->newLine();

        // Data Integrity Checks
        $this->info('🔍 DATA INTEGRITY CHECKS');
        $this->line('─────────────────────────────────────────────────────');
        
        $checks = [
            [
                'check' => 'All houses have valid street names',
                'status' => House::whereNotIn('street_name', ['Jalan Tropika 2', 'Jalan Tropika 3', 'Jalan Tropika 4', 'Jalan Tropika 5'])->count() === 0
            ],
            [
                'check' => 'All legacy bills have is_legacy flag',
                'status' => Bill::whereNull('is_legacy')->orWhere('is_legacy', false)->where('bill_year', '<', 2026)->count() === 0
            ],
            [
                'check' => 'All paid bills have paid_at date',
                'status' => Bill::where('status', 'paid')->whereNull('paid_at')->count() === 0
            ],
            [
                'check' => 'All legacy payments have is_legacy flag',
                'status' => Payment::whereNull('is_legacy')->orWhere('is_legacy', false)->where('payment_no', 'LIKE', 'LEG-%')->count() === 0
            ],
            [
                'check' => 'All bills have valid bill_no format',
                'status' => Bill::where('bill_no', 'NOT LIKE', 'BIL-%')->count() === 0
            ],
        ];

        foreach ($checks as $check) {
            $icon = $check['status'] ? '✅' : '❌';
            $this->line("   {$icon} {$check['check']}");
        }
        $this->newLine();

        // Financial Summary
        $this->info('💰 FINANCIAL SUMMARY');
        $this->line('─────────────────────────────────────────────────────');
        
        $totalCollected = Bill::where('status', 'paid')->sum('paid_amount');
        $totalOutstanding = Bill::where('status', 'unpaid')->sum('amount');
        $membershipCollected = MembershipFee::where('status', 'paid')->sum('amount');
        $membershipOutstanding = MembershipFee::where('status', 'unpaid')->sum('amount');

        $this->table(
            ['Category', 'Collected (RM)', 'Outstanding (RM)'],
            [
                ['Annual Fees', number_format($totalCollected, 2), number_format($totalOutstanding, 2)],
                ['Membership Fees', number_format($membershipCollected, 2), number_format($membershipOutstanding, 2)],
                ['TOTAL', number_format($totalCollected + $membershipCollected, 2), number_format($totalOutstanding + $membershipOutstanding, 2)],
            ]
        );
        $this->newLine();

        // Overall Assessment
        $allChecksPassed = collect($checks)->every(fn($check) => $check['status']);
        
        if ($allChecksPassed && $housesCount > 0 && $billsCount > 0) {
            $this->info('╔════════════════════════════════════════════════════╗');
            $this->info('║   ✅ LEGACY DATA VERIFICATION PASSED               ║');
            $this->info('║   System is ready for production                   ║');
            $this->info('╚════════════════════════════════════════════════════╝');
            return Command::SUCCESS;
        } else {
            $this->error('╔════════════════════════════════════════════════════╗');
            $this->error('║   ⚠️  LEGACY DATA VERIFICATION FAILED              ║');
            $this->error('║   Please check the issues above                    ║');
            $this->error('╚════════════════════════════════════════════════════╝');
            return Command::FAILURE;
        }
    }

    private function getStatusIcon(bool $condition): string
    {
        return $condition ? '✅ OK' : '❌ Missing';
    }
}

