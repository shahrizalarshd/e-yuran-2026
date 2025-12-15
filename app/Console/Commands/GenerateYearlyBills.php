<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class GenerateYearlyBills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bills:generate-yearly 
                            {year? : The year to generate bills for (defaults to current year)}
                            {--force : Force generation even if bills already exist}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate bills for all 12 months of the specified year for all billable houses';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        $year = $this->argument('year') ?? now()->year;

        $this->info("╔════════════════════════════════════════════════════════╗");
        $this->info("║       PENJANAAN BIL TAHUNAN AUTOMATIK                  ║");
        $this->info("╚════════════════════════════════════════════════════════╝");
        $this->newLine();

        $this->info("Tahun: {$year}");
        $this->info("Menjana bil untuk semua 12 bulan...");
        $this->newLine();

        $result = $billingService->generateYearlyBills((int) $year);

        if ($result['success']) {
            $this->newLine();
            $this->info("╔════════════════════════════════════════════════════════╗");
            $this->info("║                    ✅ BERJAYA!                         ║");
            $this->info("╠════════════════════════════════════════════════════════╣");
            $this->info("║  Bil dijana    : " . str_pad($result['generated'], 36) . "║");
            $this->info("║  Rumah         : " . str_pad($result['houses'], 36) . "║");
            $this->info("╚════════════════════════════════════════════════════════╝");
            
            return Command::SUCCESS;
        }

        $this->error("Gagal: {$result['message']}");
        
        if (!empty($result['errors'])) {
            $this->newLine();
            $this->warn("Errors:");
            foreach ($result['errors'] as $error) {
                $this->line("  - {$error}");
            }
        }

        return Command::FAILURE;
    }
}

