<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\House;
use App\Models\Resident;
use App\Models\HouseOccupancy;
use App\Models\HouseMember;
use App\Models\FeeConfiguration;
use App\Models\Bill;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Untuk demo data lengkap dengan bil 2023, 2024, 2025
        // Guna: php artisan db:seed --class=DemoDataSeeder
        
        // Create System Settings
        $this->createSystemSettings();

        // Create Fee Configuration
        $this->createFeeConfiguration();

        // Create Admin Users
        $this->createAdminUsers();

        // Create Houses
        $this->createHouses();

        // Create Sample Resident
        $this->createSampleResident();
    }

    private function createSystemSettings(): void
    {
        SystemSetting::set('toyyibpay_secret_key', '', 'string', 'toyyibpay', 'ToyyibPay Secret Key');
        SystemSetting::set('toyyibpay_category_code', '', 'string', 'toyyibpay', 'ToyyibPay Category Code');
        SystemSetting::set('toyyibpay_sandbox', 'true', 'boolean', 'toyyibpay', 'Use Sandbox Mode');
        SystemSetting::set('telegram_bot_token', '', 'string', 'telegram', 'Telegram Bot Token');
        SystemSetting::set('telegram_chat_id', '', 'string', 'telegram', 'Telegram Chat ID');
        SystemSetting::set('telegram_enabled', 'false', 'boolean', 'telegram', 'Enable Telegram Notifications');
    }

    private function createFeeConfiguration(): void
    {
        // Fee configuration akan dibuat oleh import:legacy-data command
        // Ini hanya untuk development/testing tanpa import
        FeeConfiguration::create([
            'name' => 'Yuran Bulanan 2026',
            'amount' => 10.00,
            'effective_from' => '2026-01-01',
            'effective_until' => null,
            'description' => 'Yuran bulanan perumahan Taman Tropika Kajang',
            'is_active' => true,
        ]);
    }

    private function createAdminUsers(): void
    {
        // Super Admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Treasurer
        User::create([
            'name' => 'Bendahari',
            'email' => 'bendahari@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'treasurer',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Auditor
        User::create([
            'name' => 'Pemeriksa',
            'email' => 'auditor@tropika.my',
            'password' => Hash::make('password'),
            'role' => 'auditor',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function createHouses(): void
    {
        $streets = [
            'Jalan Tropika 2',
            'Jalan Tropika 3',
            'Jalan Tropika 4',
            'Jalan Tropika 5',
        ];

        foreach ($streets as $street) {
            for ($i = 1; $i <= 20; $i++) {
                House::create([
                    'house_no' => $i,
                    'street_name' => $street,
                    'is_registered' => $i <= 15, // First 15 houses are registered
                    'is_active' => true,
                    'status' => $i <= 18 ? 'occupied' : 'vacant',
                ]);
            }
        }
    }

    private function createSampleResident(): void
    {
        // Create a sample resident user
        $user = User::create([
            'name' => 'Penduduk',
            'email' => 'ahmad1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'resident',
            'language_preference' => 'bm',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create resident profile
        $resident = Resident::create([
            'user_id' => $user->id,
            'name' => 'Ahmad bin Abdullah',
            'email' => 'ahmad1@gmail.com',
            'phone' => '0123456789',
            'ic_number' => '800101-01-1234',
            'language_preference' => 'bm',
        ]);

        // Get first house
        $house = House::first();

        // Create house occupancy (owner)
        HouseOccupancy::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'role' => 'owner',
            'start_date' => '2020-01-01',
            'is_payer' => true,
        ]);

        // Create house member
        HouseMember::create([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'relationship' => 'owner',
            'can_view_bills' => true,
            'can_pay' => true,
            'status' => 'active',
            'approved_at' => now(),
        ]);

        // Generate bills for this house
        $feeConfig = FeeConfiguration::first();
        $currentYear = now()->year;
        
        for ($month = 1; $month <= now()->month; $month++) {
            Bill::create([
                'house_id' => $house->id,
                'fee_configuration_id' => $feeConfig->id,
                'bill_no' => Bill::generateBillNo($currentYear, $month, $house->id),
                'bill_year' => $currentYear,
                'bill_month' => $month,
                'amount' => $feeConfig->amount,
                'status' => $month < now()->month - 2 ? 'paid' : 'unpaid',
                'paid_amount' => $month < now()->month - 2 ? $feeConfig->amount : 0,
                'due_date' => now()->setYear($currentYear)->setMonth($month)->endOfMonth(),
                'paid_at' => $month < now()->month - 2 ? now()->setMonth($month)->endOfMonth() : null,
            ]);
        }
    }
}
