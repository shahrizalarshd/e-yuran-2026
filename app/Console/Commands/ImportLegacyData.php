<?php

namespace App\Console\Commands;

use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\MembershipFee;
use App\Models\Payment;
use App\Models\HouseOccupancy;
use App\Models\HouseMember;
use App\Models\Resident;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportLegacyData extends Command
{
    protected $signature = 'import:legacy-data 
                            {--dry-run : Run without making changes}
                            {--force : Skip confirmation prompt}
                            {--skip-houses : Skip importing houses}
                            {--skip-bills : Skip importing bills}
                            {--skip-membership : Skip importing membership fees}';

    protected $description = 'Import legacy payment data from Excel files (2017-2025)';

    private array $validStreets = [2, 3, 4, 5];
    private float $monthlyFee = 10.00;
    private float $membershipFee = 20.00;
    private array $houses = [];
    private array $paymentData = [];
    private array $membershipData = [];

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('==============================================');
        $this->info('  LEGACY DATA IMPORT - Taman Tropika Kajang');
        $this->info('==============================================');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        // Step 1: Load and parse Excel files
        $this->info('📂 Loading Excel files...');
        $this->loadExcelData();

        $this->info("   Found {$this->countHouses()} houses in valid streets (Jalan 2-5)");
        $this->newLine();

        // Step 2: Show summary before import
        $this->showImportSummary();

        if (!$isDryRun && !$this->option('force') && !$this->confirm('Proceed with import?')) {
            $this->warn('Import cancelled.');
            return Command::SUCCESS;
        }

        if ($isDryRun) {
            $this->info('✅ Dry run completed. No changes made.');
            return Command::SUCCESS;
        }

        // Step 3: Clear existing data (except users)
        $this->clearExistingData();

        // Step 4: Create fee configuration
        $this->createFeeConfiguration();

        // Step 5: Import houses
        if (!$this->option('skip-houses')) {
            $this->importHouses();
        }

        // Step 6: Import bills and payments
        if (!$this->option('skip-bills')) {
            $this->importBillsAndPayments();
        }

        // Step 7: Import membership fees
        if (!$this->option('skip-membership')) {
            $this->importMembershipFees();
        }

        // Step 8: Show final summary
        $this->showFinalSummary();

        return Command::SUCCESS;
    }

    private function loadExcelData(): void
    {
        $basePath = base_path();

        // Load Rekod Register for house master list
        $registerFile = $basePath . '/Fail Yuran Tahunan dan Daftar Keahlian PPTT - sent to Marwelies 2 Sept 2022.xlsx';
        $this->loadHousesFromRegister($registerFile);

        // Load payment data from all years
        $paymentFile = $basePath . '/Rekod Bayaran Yuran 2017-2024.xlsx';
        $this->loadPaymentData($paymentFile, ['2017', '2018', '2019', '2020', '2021', '2022', '2023', '2024']);

        // Load 2024 data (separate file - may have updates)
        $file2024 = $basePath . '/Penyata Yuran 2024.xlsx';
        if (file_exists($file2024)) {
            $this->loadSingleYearPaymentData($file2024, 2024);
        }

        // Load 2025 data
        $file2025 = $basePath . '/Penyata Yuran 2025.xlsx';
        if (file_exists($file2025)) {
            $this->loadSingleYearPaymentData($file2025, 2025);
        }

        // Load membership data from 2017
        $this->loadMembershipData($paymentFile);
    }

    private function loadHousesFromRegister(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('Rekod register');

        if (!$sheet) {
            $this->error('Could not find "Rekod register" sheet');
            return;
        }

        $data = $sheet->toArray();

        foreach ($data as $index => $row) {
            if ($index === 0) continue; // Skip header

            $houseNo = $row[3] ?? null; // Column D - No
            $jalan = $row[4] ?? null;   // Column E - Jalan
            $status = $row[5] ?? null;  // Column F - Status Rumah
            $nama = $row[6] ?? null;    // Column G - Nama

            if (empty($houseNo) || empty($jalan)) continue;

            // Convert to integers, handle special cases like "3A"
            $jalanNum = is_numeric($jalan) ? (int) $jalan : null;

            if (!$jalanNum || !in_array($jalanNum, $this->validStreets)) {
                continue;
            }

            $houseNoStr = is_numeric($houseNo) ? (string) (int) $houseNo : (string) $houseNo;

            $key = "{$houseNoStr}/{$jalanNum}";
            $this->houses[$key] = [
                'house_no' => $houseNoStr,
                'jalan' => $jalanNum,
                'street_name' => "Jalan Tropika {$jalanNum}",
                'status' => $status,
                'owner_name' => $nama,
            ];
        }
    }

    private function loadPaymentData(string $filePath, array $years): void
    {
        $spreadsheet = IOFactory::load($filePath);

        foreach ($years as $year) {
            $sheet = $spreadsheet->getSheetByName($year);
            if (!$sheet) continue;

            $data = $sheet->toArray();
            $this->parsePaymentSheet($data, (int) $year);
        }
    }

    private function loadSingleYearPaymentData(string $filePath, int $year): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();
        $this->parsePaymentSheet($data, $year);
    }

    private function parsePaymentSheet(array $data, int $year): void
    {
        foreach ($data as $index => $row) {
            if ($index < 6) continue; // Skip header rows

            $houseId = $row[3] ?? null; // Column D - No Rumah

            if (empty($houseId) || strtolower(trim((string)$houseId)) === 'total') {
                continue;
            }

            $houseIdStr = trim((string) $houseId);

            // Parse months (columns F to Q = indices 5 to 16)
            $monthsPaid = [];
            for ($m = 0; $m < 12; $m++) {
                $value = $row[5 + $m] ?? null;
                $monthsPaid[$m + 1] = (is_numeric($value) && (float) $value === 10.0);
            }

            // Get payment reference/note
            $reference = null;
            for ($col = 18; $col <= 22; $col++) {
                if (!empty($row[$col]) && !is_numeric($row[$col])) {
                    $reference = trim((string) $row[$col]);
                    break;
                }
            }

            if (!isset($this->paymentData[$houseIdStr])) {
                $this->paymentData[$houseIdStr] = [];
            }

            $this->paymentData[$houseIdStr][$year] = [
                'months_paid' => $monthsPaid,
                'reference' => $reference,
            ];
        }
    }

    private function loadMembershipData(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheetByName('2017');

        if (!$sheet) return;

        $data = $sheet->toArray();

        foreach ($data as $index => $row) {
            if ($index < 6) continue;

            $houseId = $row[3] ?? null;
            $membershipPaid = $row[4] ?? null; // Column E - Yuran Keahlian

            if (empty($houseId) || strtolower(trim((string)$houseId)) === 'total') {
                continue;
            }

            $houseIdStr = trim((string) $houseId);
            $nama = $row[2] ?? null;

            $this->membershipData[$houseIdStr] = [
                'paid' => is_numeric($membershipPaid) && (float) $membershipPaid === 20.0,
                'owner_name' => $nama,
            ];
        }
    }

    private function countHouses(): int
    {
        return count($this->houses);
    }

    private function showImportSummary(): void
    {
        $totalBills = 0;
        $paidBills = 0;
        $unpaidBills = 0;

        foreach ($this->houses as $key => $house) {
            for ($year = 2017; $year <= 2025; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $totalBills++;

                    $isPaid = false;
                    if (isset($this->paymentData[$key][$year]['months_paid'][$month])) {
                        $isPaid = $this->paymentData[$key][$year]['months_paid'][$month];
                    }

                    if ($isPaid) {
                        $paidBills++;
                    } else {
                        $unpaidBills++;
                    }
                }
            }
        }

        $membershipPaid = 0;
        $membershipUnpaid = 0;
        foreach ($this->houses as $key => $house) {
            if (isset($this->membershipData[$key]) && $this->membershipData[$key]['paid']) {
                $membershipPaid++;
            } else {
                $membershipUnpaid++;
            }
        }

        $this->newLine();
        $this->info('📊 IMPORT SUMMARY');
        $this->info('─────────────────────────────────────────────');
        $this->table(
            ['Item', 'Count', 'Amount'],
            [
                ['Houses to import', count($this->houses), '-'],
                ['Total bills (2017-2025)', number_format($totalBills), 'RM ' . number_format($totalBills * $this->monthlyFee, 2)],
                ['Paid bills', number_format($paidBills), 'RM ' . number_format($paidBills * $this->monthlyFee, 2)],
                ['Unpaid bills', number_format($unpaidBills), 'RM ' . number_format($unpaidBills * $this->monthlyFee, 2)],
                ['Membership fees (paid)', $membershipPaid, 'RM ' . number_format($membershipPaid * $this->membershipFee, 2)],
                ['Membership fees (unpaid)', $membershipUnpaid, 'RM ' . number_format($membershipUnpaid * $this->membershipFee, 2)],
            ]
        );
        $this->newLine();
    }

    private function clearExistingData(): void
    {
        $this->info('🗑️  Clearing existing data...');

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Clear in order to avoid FK issues
        DB::table('payment_bill')->delete();
        Payment::query()->delete();
        Bill::query()->delete();
        MembershipFee::query()->delete();
        HouseMember::query()->delete();
        HouseOccupancy::query()->delete();
        Resident::query()->delete();
        House::query()->delete();
        FeeConfiguration::query()->delete();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $this->info('   ✓ Cleared houses, bills, payments, membership fees');
        $this->info('   ✓ Users and admin accounts preserved');
        $this->newLine();
    }

    private function createFeeConfiguration(): void
    {
        $this->info('⚙️  Creating fee configurations...');

        // Legacy fee (2017-2025)
        FeeConfiguration::create([
            'name' => 'Yuran Bulanan (Legacy)',
            'amount' => $this->monthlyFee,
            'effective_from' => '2017-01-01',
            'effective_until' => '2025-12-31',
            'description' => 'Yuran bulanan legacy dari rekod Excel',
            'is_active' => true,
        ]);

        // Current fee (2026 onwards)
        FeeConfiguration::create([
            'name' => 'Yuran Bulanan 2026',
            'amount' => $this->monthlyFee,
            'effective_from' => '2026-01-01',
            'effective_until' => null,
            'description' => 'Yuran bulanan perumahan Taman Tropika Kajang',
            'is_active' => true,
        ]);

        $this->info('   ✓ Created legacy fee config (RM10/month, 2017-2025)');
        $this->info('   ✓ Created current fee config (RM10/month, 2026+)');
        $this->newLine();
    }

    private function importHouses(): void
    {
        $this->info('🏠 Importing houses...');
        $bar = $this->output->createProgressBar(count($this->houses));

        $imported = 0;
        foreach ($this->houses as $key => $data) {
            House::create([
                'house_no' => $data['house_no'],
                'street_name' => $data['street_name'],
                'is_registered' => true,
                'is_active' => true,
                'status' => 'occupied',
            ]);
            $imported++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Imported {$imported} houses");
        $this->newLine();
    }

    private function importBillsAndPayments(): void
    {
        $this->info('📄 Importing bills and payments (2017-2025)...');

        $houses = House::all()->keyBy(function ($house) {
            return $house->house_no . '/' . preg_replace('/[^0-9]/', '', $house->street_name);
        });

        $feeConfig = FeeConfiguration::where('name', 'Yuran Bulanan (Legacy)')->first();

        $totalBills = 0;
        $paidBills = 0;
        $paymentsCreated = 0;

        $bar = $this->output->createProgressBar(count($houses) * 9 * 12); // 9 years, 12 months

        foreach ($houses as $key => $house) {
            for ($year = 2017; $year <= 2025; $year++) {
                for ($month = 1; $month <= 12; $month++) {
                    $isPaid = false;
                    $reference = null;

                    if (isset($this->paymentData[$key][$year])) {
                        $isPaid = $this->paymentData[$key][$year]['months_paid'][$month] ?? false;
                        $reference = $this->paymentData[$key][$year]['reference'] ?? null;
                    }

                    // Create bill
                    $bill = Bill::create([
                        'house_id' => $house->id,
                        'fee_configuration_id' => $feeConfig->id,
                        'bill_no' => Bill::generateBillNo($year, $month, $house->id),
                        'bill_year' => $year,
                        'bill_month' => $month,
                        'amount' => $this->monthlyFee,
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'paid_amount' => $isPaid ? $this->monthlyFee : 0,
                        'due_date' => sprintf('%04d-%02d-28', $year, $month),
                        'paid_at' => $isPaid ? sprintf('%04d-%02d-28', $year, $month) : null,
                        'is_legacy' => true,
                    ]);

                    $totalBills++;

                    // Create payment record for paid bills
                    if ($isPaid) {
                        $paidBills++;

                        $payment = Payment::create([
                            'house_id' => $house->id,
                            'resident_id' => null,
                            'payment_no' => sprintf('LEG-%04d%02d-%05d', $year, $month, $house->id),
                            'amount' => $this->monthlyFee,
                            'status' => 'success',
                            'payment_type' => 'current_month',
                            'paid_at' => sprintf('%04d-%02d-28', $year, $month),
                            'is_legacy' => true,
                            'payment_method' => 'legacy',
                            'legacy_reference' => $reference,
                        ]);

                        // Link payment to bill
                        $payment->bills()->attach($bill->id, ['amount' => $this->monthlyFee]);

                        $paymentsCreated++;
                    }

                    $bar->advance();
                }
            }
        }

        $bar->finish();
        $this->newLine();
        $this->info("   ✓ Created {$totalBills} bills");
        $this->info("   ✓ Marked {$paidBills} bills as paid");
        $this->info("   ✓ Created {$paymentsCreated} payment records");
        $this->newLine();
    }

    private function importMembershipFees(): void
    {
        $this->info('🎫 Importing membership fees...');

        $houses = House::all()->keyBy(function ($house) {
            return $house->house_no . '/' . preg_replace('/[^0-9]/', '', $house->street_name);
        });

        $paid = 0;
        $unpaid = 0;

        foreach ($houses as $key => $house) {
            $membershipInfo = $this->membershipData[$key] ?? null;
            $isPaid = $membershipInfo && $membershipInfo['paid'];
            $ownerName = $membershipInfo['owner_name'] ?? ($this->houses[$key]['owner_name'] ?? null);

            MembershipFee::create([
                'house_id' => $house->id,
                'resident_id' => null,
                'amount' => $this->membershipFee,
                'status' => $isPaid ? 'paid' : 'unpaid',
                'paid_at' => $isPaid ? '2017-01-01' : null,
                'is_legacy' => true,
                'legacy_owner_name' => $ownerName,
                'fee_year' => 2017,
            ]);

            if ($isPaid) {
                $paid++;
            } else {
                $unpaid++;
            }
        }

        $this->info("   ✓ Created {$paid} paid membership fees");
        $this->info("   ✓ Created {$unpaid} unpaid membership fees");
        $this->newLine();
    }

    private function showFinalSummary(): void
    {
        $this->newLine();
        $this->info('═══════════════════════════════════════════════');
        $this->info('  ✅ IMPORT COMPLETED SUCCESSFULLY');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        $this->table(
            ['Table', 'Count'],
            [
                ['Houses', House::count()],
                ['Bills (Total)', Bill::count()],
                ['Bills (Paid)', Bill::where('status', 'paid')->count()],
                ['Bills (Unpaid)', Bill::where('status', 'unpaid')->count()],
                ['Payments', Payment::count()],
                ['Membership Fees', MembershipFee::count()],
            ]
        );

        $totalOutstanding = Bill::where('status', 'unpaid')->sum('amount');
        $totalCollected = Bill::where('status', 'paid')->sum('amount');

        $this->newLine();
        $this->info('💰 FINANCIAL SUMMARY');
        $this->info("   Total Collected (Legacy): RM " . number_format($totalCollected, 2));
        $this->info("   Total Outstanding: RM " . number_format($totalOutstanding, 2));
        $this->newLine();
    }
}

