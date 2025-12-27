<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HouseController;
use App\Http\Controllers\Admin\ResidentController;
use App\Http\Controllers\Admin\BillController as AdminBillController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\FeeConfigurationController;
use App\Http\Controllers\Admin\MembershipFeeController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Resident\DashboardController as ResidentDashboardController;
use App\Http\Controllers\Resident\BillController as ResidentBillController;
use App\Http\Controllers\Resident\PaymentController as ResidentPaymentController;
use App\Http\Controllers\Resident\HouseSettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Health check endpoint for Docker
Route::get('/health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'database' => 'connected'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Database connection failed'
        ], 503);
    }
})->name('health');

// Welcome page
Route::get('/', function () {
    if (auth()->check()) {
        return auth()->user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('resident.dashboard');
    }
    return view('welcome');
})->name('home');

// Language switcher
Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Payment callbacks (no auth required - ToyyibPay redirects here)
Route::get('/payment/callback', [PaymentCallbackController::class, 'callback'])->name('payment.callback');
Route::post('/payment/webhook', [PaymentCallbackController::class, 'webhook'])->name('payment.webhook');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard redirect based on role
    Route::get('/dashboard', function () {
        return auth()->user()->isAdmin() 
            ? redirect()->route('admin.dashboard')
            : redirect()->route('resident.dashboard');
    })->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('/{notification}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // ADMIN ROUTES (Super Admin, Treasurer, Auditor)
    // ==========================================
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        
        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Houses (Super Admin only for CUD, others can view)
        Route::resource('houses', HouseController::class)->except(['store', 'update', 'destroy']);
        Route::get('/houses/create', [HouseController::class, 'create'])->name('houses.create')->middleware('role:super_admin');
        Route::post('/houses', [HouseController::class, 'store'])->name('houses.store')->middleware('role:super_admin');
        Route::put('/houses/{house}', [HouseController::class, 'update'])->name('houses.update')->middleware('role:super_admin');
        Route::delete('/houses/{house}', [HouseController::class, 'destroy'])->name('houses.destroy')->middleware('role:super_admin');
        Route::post('/houses/{house}/assign-owner', [HouseController::class, 'assignOwner'])
            ->name('houses.assign-owner')
            ->middleware('role:super_admin');
        Route::post('/houses/{house}/assign-tenant', [HouseController::class, 'assignTenant'])
            ->name('houses.assign-tenant')
            ->middleware('role:super_admin');

        // Residents
        Route::get('/residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::get('/residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::get('/verifications/pending', [ResidentController::class, 'pendingVerifications'])->name('verifications.pending');
        Route::post('/verifications/{houseMember}/approve', [ResidentController::class, 'approve'])
            ->name('verifications.approve')
            ->middleware('role:super_admin,treasurer');
        Route::post('/verifications/{houseMember}/reject', [ResidentController::class, 'reject'])
            ->name('verifications.reject')
            ->middleware('role:super_admin,treasurer');
        Route::patch('/members/{houseMember}/permissions', [ResidentController::class, 'updatePermissions'])
            ->name('members.permissions')
            ->middleware('role:super_admin');

        // Bills
        Route::get('/bills', [AdminBillController::class, 'index'])->name('bills.index');
        Route::get('/bills/generate', [AdminBillController::class, 'generateForm'])
            ->name('bills.generate.form')
            ->middleware('role:super_admin');
        Route::post('/bills/generate-yearly', [AdminBillController::class, 'generateYearly'])
            ->name('bills.generate.yearly')
            ->middleware('role:super_admin');
        Route::get('/bills/outstanding', [AdminBillController::class, 'outstanding'])->name('bills.outstanding');
        Route::get('/bills/{bill}', [AdminBillController::class, 'show'])->name('bills.show');
        Route::get('/bills/{bill}/edit', [AdminBillController::class, 'edit'])
            ->name('bills.edit')
            ->middleware('role:super_admin,treasurer');
        Route::patch('/bills/{bill}', [AdminBillController::class, 'update'])
            ->name('bills.update')
            ->middleware('role:super_admin,treasurer');
        Route::delete('/bills/{bill}', [AdminBillController::class, 'destroy'])
            ->name('bills.destroy')
            ->middleware('role:super_admin');

        // Payments
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/reconciliation', [AdminPaymentController::class, 'reconciliation'])
            ->name('payments.reconciliation')
            ->middleware('role:super_admin,treasurer');
        Route::get('/payments/report', [AdminPaymentController::class, 'report'])->name('payments.report');
        Route::get('/payments/{payment}', [AdminPaymentController::class, 'show'])->name('payments.show');

        // Fee Configuration (Super Admin & Treasurer)
        Route::resource('fees', FeeConfigurationController::class)
            ->middleware('role:super_admin,treasurer');

        // Membership Fees (Super Admin & Treasurer)
        Route::middleware('role:super_admin,treasurer')->group(function () {
            // Membership Fee Configuration (must be before {membershipFee} routes)
            Route::get('/membership-fees/config', [MembershipFeeController::class, 'configIndex'])->name('membership-fees.config.index');
            Route::get('/membership-fees/config/create', [MembershipFeeController::class, 'configCreate'])->name('membership-fees.config.create');
            Route::post('/membership-fees/config', [MembershipFeeController::class, 'configStore'])->name('membership-fees.config.store');
            Route::get('/membership-fees/config/{configuration}/edit', [MembershipFeeController::class, 'configEdit'])->name('membership-fees.config.edit');
            Route::put('/membership-fees/config/{configuration}', [MembershipFeeController::class, 'configUpdate'])->name('membership-fees.config.update');
            Route::delete('/membership-fees/config/{configuration}', [MembershipFeeController::class, 'configDestroy'])->name('membership-fees.config.destroy');
            
            // Membership Fee CRUD
            Route::get('/membership-fees', [MembershipFeeController::class, 'index'])->name('membership-fees.index');
            Route::get('/membership-fees/{membershipFee}/edit', [MembershipFeeController::class, 'edit'])->name('membership-fees.edit');
            Route::put('/membership-fees/{membershipFee}', [MembershipFeeController::class, 'update'])->name('membership-fees.update');
            Route::patch('/membership-fees/{membershipFee}/mark-paid', [MembershipFeeController::class, 'markAsPaid'])->name('membership-fees.mark-paid');
        });

        // Settings (Super Admin only)
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('/settings/toyyibpay', [SettingController::class, 'updateToyyibPay'])->name('settings.toyyibpay');
            Route::post('/settings/telegram', [SettingController::class, 'updateTelegram'])->name('settings.telegram');
            Route::post('/settings/telegram/test', [SettingController::class, 'testTelegram'])->name('settings.telegram.test');
        });

        // Audit Logs (Super Admin & Auditor)
        Route::middleware('role:super_admin,auditor')->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
            Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        });
    });

    // ==========================================
    // RESIDENT ROUTES
    // ==========================================
    Route::prefix('resident')->name('resident.')->middleware('role:resident')->group(function () {
        
        // Dashboard
        Route::get('/', [ResidentDashboardController::class, 'index'])->name('dashboard');
        Route::post('/select-house/{house}', [ResidentDashboardController::class, 'selectHouse'])->name('select-house');

        // Bills
        Route::get('/bills', [ResidentBillController::class, 'index'])->name('bills.index');
        Route::get('/bills/{bill}', [ResidentBillController::class, 'show'])->name('bills.show');

        // Payments
        Route::get('/payments', [ResidentPaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [ResidentPaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments/confirm', [ResidentPaymentController::class, 'confirm'])->name('payments.confirm');
        Route::post('/payments', [ResidentPaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}', [ResidentPaymentController::class, 'show'])->name('payments.show');

        // House Settings (Owner only - for setting payer)
        Route::get('/house-settings', [HouseSettingsController::class, 'index'])->name('house-settings.index');
        Route::post('/house-settings/{house}/payer', [HouseSettingsController::class, 'updatePayer'])->name('house-settings.update-payer');
    });
});

require __DIR__.'/auth.php';
