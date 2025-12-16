<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Bill;
use App\Models\FeeConfiguration;
use App\Models\House;
use App\Models\HouseMember;
use App\Models\HouseOccupancy;
use App\Models\Payment;
use App\Models\Resident;
use App\Models\SystemNotification;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Buat data demo dengan bil untuk tahun 2023, 2024, dan 2025
     */
    public function run(): void
    {
        $this->command->info('🏠 Mula seeding data demo...');

        // ==========================================
        // 1. SYSTEM SETTINGS
        // ==========================================
        $this->command->info('⚙️ Setup system settings...');
        $this->seedSystemSettings();

        // ==========================================
        // 2. ADMIN USERS
        // ==========================================
        $this->command->info('👤 Buat admin users...');
        $this->seedAdminUsers();

        // ==========================================
        // 3. FEE CONFIGURATIONS (2023, 2024, 2025)
        // ==========================================
        $this->command->info('💰 Setup yuran untuk setiap tahun...');
        $this->seedFeeConfigurations();

        // ==========================================
        // 4. HOUSES
        // ==========================================
        $this->command->info('🏘️ Buat rumah-rumah...');
        $houses = $this->seedHouses();

        // ==========================================
        // 5. RESIDENTS & MEMBERSHIPS
        // ==========================================
        $this->command->info('👨‍👩‍👧‍👦 Buat penduduk dan keahlian...');
        $this->seedResidentsAndMemberships($houses);

        // ==========================================
        // 6. BILLS FOR 2023, 2024, 2025
        // ==========================================
        $this->command->info('📄 Generate bil untuk 2023, 2024, 2025...');
        $this->seedBills($houses);

        // ==========================================
        // 7. PAYMENTS
        // ==========================================
        $this->command->info('💳 Buat rekod pembayaran...');
        $this->seedPayments($houses);

        // ==========================================
        // 8. NOTIFICATIONS
        // ==========================================
        $this->command->info('🔔 Buat notifikasi...');
        $this->seedNotifications();

        // ==========================================
        // 9. SUMMARY
        // ==========================================
        $this->printSummary();
    }

    private function seedSystemSettings(): void
    {
        // ToyyibPay settings (sandbox)
        SystemSetting::set('toyyibpay_secret_key', 'your-sandbox-secret-key', 'string', 'toyyibpay', 'ToyyibPay Secret Key');
        SystemSetting::set('toyyibpay_category_code', 'your-category-code', 'string', 'toyyibpay', 'ToyyibPay Category Code');
        SystemSetting::set('toyyibpay_sandbox', '1', 'boolean', 'toyyibpay', 'Use Sandbox Mode');

        // Telegram settings
        SystemSetting::set('telegram_enabled', '0', 'boolean', 'telegram', 'Enable Telegram Notifications');
        SystemSetting::set('telegram_bot_token', '', 'string', 'telegram', 'Telegram Bot Token');
        SystemSetting::set('telegram_chat_id', '', 'string', 'telegram', 'Telegram Chat ID');
    }

    private function seedAdminUsers(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Admin Utama',
            'email' => 'admin@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Treasurer
        $treasurer = User::create([
            'name' => 'En. Ahmad (Bendahari)',
            'email' => 'bendahari@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'treasurer',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Auditor
        $auditor = User::create([
            'name' => 'Pn. Fatimah (Pemeriksa)',
            'email' => 'auditor@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'auditor',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        AuditLog::logAction('seed_admins', 'Created admin users via seeder');
    }

    private function seedFeeConfigurations(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first();

        // Yuran 2023 - RM15/bulan
        FeeConfiguration::create([
            'name' => 'Yuran Penyelenggaraan 2023',
            'amount' => 15.00,
            'effective_from' => Carbon::create(2023, 1, 1),
            'effective_until' => Carbon::create(2023, 12, 31),
            'description' => 'Yuran penyelenggaraan bulanan untuk tahun 2023',
            'is_active' => true,
            'created_by' => $superAdmin->id,
        ]);

        // Yuran 2024 - RM18/bulan
        FeeConfiguration::create([
            'name' => 'Yuran Penyelenggaraan 2024',
            'amount' => 18.00,
            'effective_from' => Carbon::create(2024, 1, 1),
            'effective_until' => Carbon::create(2024, 12, 31),
            'description' => 'Yuran penyelenggaraan bulanan untuk tahun 2024',
            'is_active' => true,
            'created_by' => $superAdmin->id,
        ]);

        // Yuran 2025 - RM20/bulan
        FeeConfiguration::create([
            'name' => 'Yuran Penyelenggaraan 2025',
            'amount' => 20.00,
            'effective_from' => Carbon::create(2025, 1, 1),
            'effective_until' => null, // Ongoing
            'description' => 'Yuran penyelenggaraan bulanan untuk tahun 2025',
            'is_active' => true,
            'created_by' => $superAdmin->id,
        ]);

        $this->command->info('   - 2023: RM15/bulan');
        $this->command->info('   - 2024: RM18/bulan');
        $this->command->info('   - 2025: RM20/bulan');
    }

    private function seedHouses(): array
    {
        $streets = [
            'Jalan Tropika 2',
            'Jalan Tropika 3',
            'Jalan Tropika 4',
            'Jalan Tropika 5',
        ];

        $houses = [];

        // 20 rumah berdaftar dan aktif (billable)
        for ($i = 1; $i <= 20; $i++) {
            $houses[] = House::create([
                'house_no' => $i,
                'street_name' => $streets[array_rand($streets)],
                'is_registered' => true,
                'is_active' => true,
                'status' => 'occupied',
            ]);
        }

        // 5 rumah berdaftar tapi tidak aktif
        for ($i = 21; $i <= 25; $i++) {
            House::create([
                'house_no' => $i,
                'street_name' => $streets[array_rand($streets)],
                'is_registered' => true,
                'is_active' => false,
                'status' => 'vacant',
            ]);
        }

        // 5 rumah tidak berdaftar
        for ($i = 26; $i <= 30; $i++) {
            House::create([
                'house_no' => $i,
                'street_name' => $streets[array_rand($streets)],
                'is_registered' => false,
                'is_active' => true,
                'status' => 'occupied',
            ]);
        }

        $this->command->info('   - 20 rumah billable');
        $this->command->info('   - 5 rumah inactive');
        $this->command->info('   - 5 rumah unregistered');

        return $houses;
    }

    private function seedResidentsAndMemberships(array $houses): void
    {
        $names = [
            ['Ahmad bin Hassan', 'ahmad1@gmail.com'],
            ['Siti Aminah binti Ali', 'siti2@gmail.com'],
            ['Muhammad Faiz bin Ibrahim', 'faiz3@gmail.com'],
            ['Nurul Izzah binti Omar', 'nurul4@gmail.com'],
            ['Mohd Razak bin Yusof', 'razak5@gmail.com'],
            ['Aishah binti Mahmud', 'aishah6@gmail.com'],
            ['Zulkifli bin Abdullah', 'zul7@gmail.com'],
            ['Noraini binti Ismail', 'noraini8@gmail.com'],
            ['Hafiz bin Ramli', 'hafiz9@gmail.com'],
            ['Salmah binti Daud', 'salmah10@gmail.com'],
            ['Kamal bin Samad', 'kamal11@gmail.com'],
            ['Rosnah binti Karim', 'rosnah12@gmail.com'],
            ['Azman bin Salleh', 'azman13@gmail.com'],
            ['Faridah binti Mansor', 'faridah14@gmail.com'],
            ['Ismail bin Bakar', 'ismail15@gmail.com'],
            ['Halimah binti Hashim', 'halimah16@gmail.com'],
            ['Jamal bin Isa', 'jamal17@gmail.com'],
            ['Mariam binti Yusuf', 'mariam18@gmail.com'],
            ['Nasir bin Latif', 'nasir19@gmail.com'],
            ['Sarina binti Rahman', 'sarina20@gmail.com'],
        ];

        foreach ($houses as $index => $house) {
            $nameData = $names[$index];

            // Create User
            $user = User::create([
                'name' => $nameData[0],
                'email' => $nameData[1],
                'password' => Hash::make('password'),
                'role' => 'resident',
                'language_preference' => 'bm',
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            // Create Resident
            $resident = Resident::create([
                'user_id' => $user->id,
                'name' => $nameData[0],
                'email' => $nameData[1],
                'phone' => '01' . rand(10000000, 99999999),
                'ic_number' => rand(700101, 990101) . '-' . rand(10, 14) . '-' . rand(1000, 9999),
                'language_preference' => 'bm',
            ]);

            // Create House Occupancy (Owner)
            HouseOccupancy::create([
                'house_id' => $house->id,
                'resident_id' => $resident->id,
                'role' => 'owner',
                'start_date' => Carbon::create(2020, 1, 1),
                'end_date' => null,
                'is_payer' => true,
            ]);

            // Create House Member
            HouseMember::create([
                'house_id' => $house->id,
                'resident_id' => $resident->id,
                'relationship' => 'owner',
                'can_view_bills' => true,
                'can_pay' => true,
                'status' => 'active',
                'approved_by' => User::where('role', 'super_admin')->first()->id,
                'approved_at' => now(),
            ]);

            // Add spouse for some houses (50%)
            if ($index % 2 === 0) {
                $spouseUser = User::create([
                    'name' => 'Pasangan ' . $nameData[0],
                    'email' => 'spouse_' . $nameData[1],
                    'password' => Hash::make('password'),
                    'role' => 'resident',
                    'language_preference' => 'bm',
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                $spouse = Resident::create([
                    'user_id' => $spouseUser->id,
                    'name' => 'Pasangan ' . $nameData[0],
                    'email' => 'spouse_' . $nameData[1],
                    'phone' => '01' . rand(10000000, 99999999),
                    'language_preference' => 'bm',
                ]);

                HouseMember::create([
                    'house_id' => $house->id,
                    'resident_id' => $spouse->id,
                    'relationship' => 'spouse',
                    'can_view_bills' => true,
                    'can_pay' => true,
                    'status' => 'active',
                    'approved_by' => User::where('role', 'super_admin')->first()->id,
                    'approved_at' => now(),
                ]);
            }
        }
    }

    private function seedBills(array $houses): void
    {
        $fee2023 = FeeConfiguration::where('name', 'like', '%2023%')->first();
        $fee2024 = FeeConfiguration::where('name', 'like', '%2024%')->first();
        $fee2025 = FeeConfiguration::where('name', 'like', '%2025%')->first();

        $billCount = 0;

        foreach ($houses as $house) {
            // ==========================================
            // BILLS 2023 (Jan - Dec) - RM15/bulan
            // Status: Semua PAID (tahun lama)
            // ==========================================
            for ($month = 1; $month <= 12; $month++) {
                Bill::create([
                    'house_id' => $house->id,
                    'fee_configuration_id' => $fee2023->id,
                    'bill_no' => Bill::generateBillNo(2023, $month, $house->id),
                    'bill_year' => 2023,
                    'bill_month' => $month,
                    'amount' => 15.00,
                    'status' => 'paid',
                    'paid_amount' => 15.00,
                    'due_date' => Carbon::create(2023, $month, 1)->endOfMonth(),
                    'paid_at' => Carbon::create(2023, $month, rand(15, 28)),
                ]);
                $billCount++;
            }

            // ==========================================
            // BILLS 2024 (Jan - Dec) - RM18/bulan
            // Status: Mixed (some paid, some partial, some unpaid)
            // ==========================================
            for ($month = 1; $month <= 12; $month++) {
                // Simulate different payment patterns
                $houseIndex = array_search($house, $houses);
                
                if ($houseIndex < 10) {
                    // First 10 houses: All paid in 2024
                    $status = 'paid';
                    $paidAmount = 18.00;
                    $paidAt = Carbon::create(2024, $month, rand(10, 25));
                } elseif ($houseIndex < 15) {
                    // Houses 11-15: Paid until Jun, unpaid after
                    if ($month <= 6) {
                        $status = 'paid';
                        $paidAmount = 18.00;
                        $paidAt = Carbon::create(2024, $month, rand(10, 25));
                    } else {
                        $status = 'unpaid';
                        $paidAmount = 0;
                        $paidAt = null;
                    }
                } elseif ($houseIndex < 18) {
                    // Houses 16-18: Some partial payments
                    if ($month <= 4) {
                        $status = 'paid';
                        $paidAmount = 18.00;
                        $paidAt = Carbon::create(2024, $month, rand(10, 25));
                    } elseif ($month <= 8) {
                        $status = 'partial';
                        $paidAmount = 9.00;
                        $paidAt = null;
                    } else {
                        $status = 'unpaid';
                        $paidAmount = 0;
                        $paidAt = null;
                    }
                } else {
                    // Houses 19-20: All unpaid (problematic houses)
                    $status = 'unpaid';
                    $paidAmount = 0;
                    $paidAt = null;
                }

                Bill::create([
                    'house_id' => $house->id,
                    'fee_configuration_id' => $fee2024->id,
                    'bill_no' => Bill::generateBillNo(2024, $month, $house->id),
                    'bill_year' => 2024,
                    'bill_month' => $month,
                    'amount' => 18.00,
                    'status' => $status,
                    'paid_amount' => $paidAmount,
                    'due_date' => Carbon::create(2024, $month, 1)->endOfMonth(),
                    'paid_at' => $paidAt,
                ]);
                $billCount++;
            }

            // ==========================================
            // BILLS 2025 (Jan - Dec) - RM20/bulan
            // Status: Current year, various statuses
            // ==========================================
            $currentMonth = now()->month;
            
            for ($month = 1; $month <= 12; $month++) {
                $houseIndex = array_search($house, $houses);
                
                if ($month > $currentMonth) {
                    // Future months - skip or create as unpaid
                    continue;
                }
                
                // Current year payment patterns
                if ($houseIndex < 5) {
                    // Top 5 houses: Always pay on time
                    $status = 'paid';
                    $paidAmount = 20.00;
                    $paidAt = Carbon::create(2025, $month, rand(1, 10));
                } elseif ($houseIndex < 12) {
                    // Houses 6-12: Pay but sometimes late
                    if ($month < $currentMonth) {
                        $status = 'paid';
                        $paidAmount = 20.00;
                        $paidAt = Carbon::create(2025, $month, rand(15, 28));
                    } else {
                        // Current month still unpaid
                        $status = 'unpaid';
                        $paidAmount = 0;
                        $paidAt = null;
                    }
                } elseif ($houseIndex < 17) {
                    // Houses 13-17: Behind on payments
                    if ($month <= $currentMonth - 2) {
                        $status = 'paid';
                        $paidAmount = 20.00;
                        $paidAt = Carbon::create(2025, $month + 1, rand(1, 15));
                    } else {
                        $status = 'unpaid';
                        $paidAmount = 0;
                        $paidAt = null;
                    }
                } else {
                    // Houses 18-20: Have not paid 2025 at all
                    $status = 'unpaid';
                    $paidAmount = 0;
                    $paidAt = null;
                }

                Bill::create([
                    'house_id' => $house->id,
                    'fee_configuration_id' => $fee2025->id,
                    'bill_no' => Bill::generateBillNo(2025, $month, $house->id),
                    'bill_year' => 2025,
                    'bill_month' => $month,
                    'amount' => 20.00,
                    'status' => $status,
                    'paid_amount' => $paidAmount,
                    'due_date' => Carbon::create(2025, $month, 1)->endOfMonth(),
                    'paid_at' => $paidAt,
                ]);
                $billCount++;
            }
        }

        $this->command->info("   - Total bil dijana: {$billCount}");
    }

    private function seedPayments(array $houses): void
    {
        $paymentCount = 0;

        foreach ($houses as $index => $house) {
            $owner = $house->occupancies()->where('role', 'owner')->first();
            if (!$owner) continue;

            $resident = $owner->resident;
            
            // Create some successful payments
            $paidBills = Bill::where('house_id', $house->id)
                ->where('status', 'paid')
                ->take(5)
                ->get();

            foreach ($paidBills->chunk(3) as $billChunk) {
                $totalAmount = $billChunk->sum('amount');
                
                $payment = Payment::create([
                    'house_id' => $house->id,
                    'resident_id' => $resident->id,
                    'payment_no' => Payment::generatePaymentNo(),
                    'amount' => $totalAmount,
                    'status' => 'success',
                    'payment_type' => 'selected_months',
                    'toyyibpay_billcode' => 'DEMO' . strtoupper(substr(md5(uniqid()), 0, 8)),
                    'toyyibpay_ref' => 'REF' . rand(100000, 999999),
                    'toyyibpay_transaction_id' => 'TXN' . rand(100000, 999999),
                    'paid_at' => $billChunk->first()->paid_at,
                ]);

                // Attach bills to payment
                foreach ($billChunk as $bill) {
                    $payment->bills()->attach($bill->id, ['amount' => $bill->amount]);
                }

                $paymentCount++;
            }
        }

        $this->command->info("   - Total pembayaran: {$paymentCount}");
    }

    private function seedNotifications(): void
    {
        $residents = User::where('role', 'resident')->take(10)->get();

        foreach ($residents as $user) {
            // Welcome notification
            SystemNotification::notify(
                $user,
                'Selamat Datang ke E-Yuran',
                'Akaun anda telah diaktifkan. Anda boleh mula melihat dan membayar bil.',
                'success'
            );

            // Random bill reminder
            if (rand(0, 1)) {
                SystemNotification::notifyWarning(
                    $user,
                    'Peringatan Bil',
                    'Anda mempunyai bil tertunggak. Sila buat pembayaran segera.'
                );
            }
        }

        // Admin notifications
        $admins = User::whereIn('role', ['super_admin', 'treasurer'])->get();
        foreach ($admins as $admin) {
            SystemNotification::notify(
                $admin,
                'Laporan Harian',
                'Kutipan hari ini: RM' . rand(100, 500) . '.00',
                'info'
            );
        }
    }

    private function printSummary(): void
    {
        $this->command->newLine();
        $this->command->info('=' . str_repeat('=', 50));
        $this->command->info('📊 SUMMARY DATA SEEDING');
        $this->command->info('=' . str_repeat('=', 50));
        
        $this->command->table(
            ['Kategori', 'Jumlah'],
            [
                ['Users (Total)', User::count()],
                ['- Super Admin', User::where('role', 'super_admin')->count()],
                ['- Treasurer', User::where('role', 'treasurer')->count()],
                ['- Auditor', User::where('role', 'auditor')->count()],
                ['- Resident', User::where('role', 'resident')->count()],
                ['', ''],
                ['Houses (Total)', House::count()],
                ['- Billable', House::billable()->count()],
                ['- Inactive', House::where('is_active', false)->count()],
                ['- Unregistered', House::where('is_registered', false)->count()],
                ['', ''],
                ['Bills (Total)', Bill::count()],
                ['- 2023 Bills', Bill::where('bill_year', 2023)->count()],
                ['- 2024 Bills', Bill::where('bill_year', 2024)->count()],
                ['- 2025 Bills', Bill::where('bill_year', 2025)->count()],
                ['', ''],
                ['Bills Status', ''],
                ['- Paid', Bill::where('status', 'paid')->count()],
                ['- Unpaid', Bill::where('status', 'unpaid')->count()],
                ['- Partial', Bill::where('status', 'partial')->count()],
                ['', ''],
                ['Payments', Payment::count()],
                ['Notifications', SystemNotification::count()],
            ]
        );

        // Financial summary
        $this->command->newLine();
        $this->command->info('💰 RINGKASAN KEWANGAN');
        $this->command->info('-' . str_repeat('-', 50));

        $totalCollection = Bill::where('status', 'paid')->sum('paid_amount');
        $totalOutstanding = Bill::whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $collection2023 = Bill::where('bill_year', 2023)->where('status', 'paid')->sum('paid_amount');
        $collection2024 = Bill::where('bill_year', 2024)->where('status', 'paid')->sum('paid_amount');
        $collection2025 = Bill::where('bill_year', 2025)->where('status', 'paid')->sum('paid_amount');

        $outstanding2024 = Bill::where('bill_year', 2024)
            ->whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;
        $outstanding2025 = Bill::where('bill_year', 2025)
            ->whereIn('status', ['unpaid', 'partial'])
            ->selectRaw('SUM(amount - paid_amount) as total')
            ->value('total') ?? 0;

        $this->command->table(
            ['Tahun', 'Kutipan (RM)', 'Tunggakan (RM)'],
            [
                ['2023', number_format($collection2023, 2), '0.00'],
                ['2024', number_format($collection2024, 2), number_format($outstanding2024, 2)],
                ['2025', number_format($collection2025, 2), number_format($outstanding2025, 2)],
                ['', '', ''],
                ['JUMLAH', number_format($totalCollection, 2), number_format($totalOutstanding, 2)],
            ]
        );

        $this->command->newLine();
        $this->command->info('🔑 LOGIN CREDENTIALS:');
        $this->command->info('-' . str_repeat('-', 50));
        $this->command->line('Super Admin : admin@tropika.my / password');
        $this->command->line('Bendahari   : bendahari@tropika.my / password');
        $this->command->line('Pemeriksa   : auditor@tropika.my / password');
        $this->command->line('Penduduk    : ahmad1@gmail.com / password');
        $this->command->newLine();
        $this->command->info('✅ Seeding selesai!');
    }
}

