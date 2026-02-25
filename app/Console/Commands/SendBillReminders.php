<?php

namespace App\Console\Commands;

use App\Mail\BillPaymentReminder;
use App\Mail\BillOverdueReminder;
use App\Models\Bill;
use App\Models\House;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendBillReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:send-reminders 
                            {--type=all : Type of reminders to send (all, unpaid, overdue)}
                            {--dry-run : Preview without actually sending emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email reminders for unpaid and overdue bills';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->option('type');
        $dryRun = $this->option('dry-run');

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║       PENGHANTARAN PERINGATAN YURAN                    ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        if ($dryRun) {
            $this->warn("⚠️  DRY RUN MODE - Tiada email akan dihantar");
            $this->newLine();
        }

        $stats = [
            'unpaid_sent' => 0,
            'overdue_sent' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $houses = House::whereHas('bills', function ($query) {
                $query->whereIn('status', ['unpaid', 'partial']);
            })
            ->with(['bills' => function ($query) {
                $query->whereIn('status', ['unpaid', 'partial'])
                    ->orderBy('bill_year')
                    ->orderBy('bill_month');
            }])
            ->get();

        $this->info("📊 Rumah dengan bil tertunggak: " . $houses->count());
        $this->newLine();

        $progressBar = $this->output->createProgressBar($houses->count());
        $progressBar->start();

        foreach ($houses as $house) {
            $progressBar->advance();

            // Get user associated with this house
            $user = $this->getHouseUser($house);
            
            if (!$user || !$user->email) {
                $stats['skipped']++;
                continue;
            }

            $unpaidBills = $house->bills->filter(fn($bill) => !$bill->is_overdue);
            $overdueBills = $house->bills->filter(fn($bill) => $bill->is_overdue);

            try {
                // Send overdue reminder if there are overdue bills
                if (in_array($type, ['all', 'overdue']) && $overdueBills->count() > 0) {
                    $totalOverdue = $overdueBills->sum('outstanding_amount');
                    $oldestOverdueDays = (int) now()->diffInDays($overdueBills->min('due_date'));

                    if (!$dryRun) {
                        Mail::to($user->email)->queue(new BillOverdueReminder(
                            $user,
                            $house,
                            $overdueBills,
                            $totalOverdue,
                            $oldestOverdueDays
                        ));
                    }
                    $stats['overdue_sent']++;
                }
                // Send regular reminder for unpaid (not overdue) bills
                elseif (in_array($type, ['all', 'unpaid']) && $unpaidBills->count() > 0) {
                    $totalOutstanding = $unpaidBills->sum('outstanding_amount');

                    if (!$dryRun) {
                        Mail::to($user->email)->queue(new BillPaymentReminder(
                            $user,
                            $house,
                            $unpaidBills,
                            $totalOutstanding
                        ));
                    }
                    $stats['unpaid_sent']++;
                }
            } catch (\Exception $e) {
                $stats['errors']++;
                Log::error('Failed to send bill reminder', [
                    'house_id' => $house->id,
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║                    📊 RINGKASAN                        ║");
        $this->info("╠════════════════════════════════════════════════════════╣");
        $this->info("║  Peringatan Bil Belum Bayar : " . str_pad($stats['unpaid_sent'], 24) . "║");
        $this->info("║  Peringatan Bil Tertunggak  : " . str_pad($stats['overdue_sent'], 24) . "║");
        $this->info("║  Dilangkau (tiada email)    : " . str_pad($stats['skipped'], 24) . "║");
        $this->info("║  Ralat                      : " . str_pad($stats['errors'], 24) . "║");
        $this->info("╚════════════════════════════════════════════════════════╝");

        $totalSent = $stats['unpaid_sent'] + $stats['overdue_sent'];

        if (!$dryRun && $totalSent > 0) {
            AuditLog::logAction(
                'send_bill_reminders',
                "Sent {$totalSent} bill reminder emails (Unpaid: {$stats['unpaid_sent']}, Overdue: {$stats['overdue_sent']})"
            );
        }

        if ($stats['errors'] > 0) {
            $this->warn("⚠️  Terdapat {$stats['errors']} ralat. Sila semak log untuk butiran.");
        }

        return Command::SUCCESS;
    }

    /**
     * Get the user associated with a house
     */
    private function getHouseUser(House $house): ?User
    {
        // Get active member occupancy
        $occupancy = $house->activeMemberOccupancy();
        
        if (!$occupancy || !$occupancy->resident_id) {
            return null;
        }

        return User::where('resident_id', $occupancy->resident_id)->first();
    }
}


