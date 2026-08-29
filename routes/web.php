<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\PortfolioContentController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VisitorLogController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\DateOfBirthController;
use App\Http\Controllers\Admin\ExpenseCategoryController;
use App\Http\Controllers\Admin\InvestmentController;
use App\Http\Controllers\Admin\InvestmentWithdrawalController;
use App\Http\Controllers\Admin\InvestorController;
use App\Http\Controllers\Admin\InvestorDashboardController;
use App\Http\Controllers\Admin\InvestorLedgerController;
use App\Http\Controllers\Admin\InvestorReportController;
use App\Http\Controllers\Admin\KhataCustomerController;
use App\Http\Controllers\Admin\KhataTransactionController;
use App\Http\Controllers\Admin\NamazAttendanceController;
use App\Http\Controllers\Admin\NamazDashboardController;
use App\Http\Controllers\Admin\NamazSettingController;
use App\Http\Controllers\Admin\ProfitPaymentController;
use App\Http\Controllers\Admin\ProfitSharingController;
use App\Http\Controllers\Admin\ProgramContributionController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\ProgramExpenseController;
use App\Http\Controllers\Admin\ProgramReportController;
use App\Http\Controllers\Admin\ProgramTransactionController;
use App\Http\Controllers\Admin\TasbeehAdminController;
use App\Http\Controllers\Admin\ZikrCounterController;
use App\Http\Controllers\Admin\ZikrDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PortfolioController;
use App\Http\Middleware\TrackVisitor;
use Illuminate\Support\Facades\Route;

Route::middleware(TrackVisitor::class)->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [PortfolioController::class, 'about'])->name('about');
    Route::get('/projects', [PortfolioController::class, 'projects'])->name('projects.index');
    Route::get('/projects/{project}', [PortfolioController::class, 'project'])->name('projects.show');
    Route::get('/blog', [PortfolioController::class, 'posts'])->name('posts.index');
    Route::get('/blog/{post}', [PortfolioController::class, 'post'])->name('posts.show');
    Route::get('/services', [PortfolioController::class, 'services'])->name('services.index');
    Route::get('/contact', [ContactController::class, 'create'])->name('contact.create');
});
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:6,1')->name('contact.store');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->middleware('throttle:5,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegistration'])->middleware('throttle:3,10')->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('date-of-births', DateOfBirthController::class);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/experience-source', [ProfileController::class, 'updateExperienceSource'])->name('profile.experience-source');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::patch('/messages/read-all', [MessageController::class, 'markAllRead'])->name('messages.read-all');
    Route::get('/messages/{contactMessage}', [MessageController::class, 'show'])->name('messages.show');
    Route::delete('/messages/{contactMessage}', [MessageController::class, 'destroy'])->name('messages.destroy');
    Route::get('/visitors', [VisitorLogController::class, 'index'])->name('visitors.index');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('permissions.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('permissions.store');
    Route::get('/permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    Route::get('/permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit');
    Route::put('/permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update');
    Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy');

    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance/run', [MaintenanceController::class, 'run'])->name('maintenance.run');

    // Khata System (Ledger & Accounts)
    Route::get('/khata', [KhataCustomerController::class, 'index'])->name('khata.index');
    Route::post('/khata/customers', [KhataCustomerController::class, 'store'])->name('khata.customers.store');
    Route::get('/khata/{khataCustomer}', [KhataCustomerController::class, 'show'])->name('khata.show');
    Route::put('/khata/customers/{khataCustomer}', [KhataCustomerController::class, 'update'])->name('khata.customers.update');
    Route::delete('/khata/customers/{khataCustomer}', [KhataCustomerController::class, 'destroy'])->name('khata.customers.destroy');

    Route::post('/khata/transactions', [KhataTransactionController::class, 'store'])->name('khata.transactions.store');
    Route::put('/khata/transactions/{khataTransaction}', [KhataTransactionController::class, 'update'])->name('khata.transactions.update');
    Route::delete('/khata/transactions/{khataTransaction}', [KhataTransactionController::class, 'destroy'])->name('khata.transactions.destroy');

    // Namaz Attendance Management
    Route::get('/namaz-attendance', [NamazAttendanceController::class, 'index'])->name('namaz.attendance.index');
    Route::post('/namaz-attendance/status', [NamazAttendanceController::class, 'updateStatus'])->name('namaz.attendance.status');
    Route::post('/namaz-attendance/users/{user}/start-date', [NamazAttendanceController::class, 'updateStartDate'])->name('namaz.attendance.start-date');
    Route::post('/namaz-attendance/day', [NamazAttendanceController::class, 'updateDay'])->name('namaz.attendance.day');
    Route::delete('/namaz-attendance/{attendance}', [NamazAttendanceController::class, 'destroy'])->name('namaz.attendance.destroy');

    Route::get('/namaz-attendance/dashboard', [NamazDashboardController::class, 'index'])->name('namaz.dashboard.index');

    Route::get('/namaz-settings', [NamazSettingController::class, 'index'])->name('namaz.settings.index');
    Route::post('/namaz-settings', [NamazSettingController::class, 'update'])->name('namaz.settings.update');

    // Zikr & Tasbeeh Tracking
    Route::get('/zikr', [ZikrDashboardController::class, 'index'])->name('zikr.index');
    Route::get('/zikr/tasbeeh/{tasbeeh}', [ZikrCounterController::class, 'show'])->name('zikr.counter.show');
    Route::post('/zikr/tasbeeh/{tasbeeh}/increment', [ZikrCounterController::class, 'increment'])->name('zikr.counter.increment');
    Route::post('/zikr/tasbeeh/{tasbeeh}/manual', [ZikrCounterController::class, 'manual'])->name('zikr.counter.manual');
    Route::post('/zikr/tasbeeh/{tasbeeh}/reset', [ZikrCounterController::class, 'reset'])->name('zikr.counter.reset');
    Route::post('/zikr/tasbeeh/{tasbeeh}/start-date', [ZikrCounterController::class, 'updateStartDate'])->name('zikr.counter.start-date');
    Route::post('/zikr/complete-all-today', [ZikrCounterController::class, 'completeAllToday'])->name('zikr.complete-all-today');
    Route::post('/zikr/reset-all', [ZikrCounterController::class, 'resetAll'])->name('zikr.reset-all');
    Route::post('/zikr/reset-lifetime', [ZikrCounterController::class, 'resetLifetime'])->name('zikr.reset-lifetime');
    Route::post('/zikr/settings', [ZikrDashboardController::class, 'updateSettings'])->name('zikr.settings.update');

    // Admin Tasbeeh Definitions CRUD
    Route::get('/tasbeehs', [TasbeehAdminController::class, 'index'])->name('tasbeehs.index');
    Route::post('/tasbeehs', [TasbeehAdminController::class, 'store'])->name('tasbeehs.store');
    Route::put('/tasbeehs/{tasbeeh}', [TasbeehAdminController::class, 'update'])->name('tasbeehs.update');
    Route::patch('/tasbeehs/{tasbeeh}/toggle', [TasbeehAdminController::class, 'toggle'])->name('tasbeehs.toggle');
    Route::delete('/tasbeehs/{tasbeeh}', [TasbeehAdminController::class, 'destroy'])->name('tasbeehs.destroy');

    Route::get('/investor-dashboard', InvestorDashboardController::class)->name('investor-dashboard');
    Route::get('/investors', [InvestorController::class, 'index'])->name('investors.index');
    Route::get('/investors/{investor}/ledger', InvestorLedgerController::class)->name('investors.ledger');
    Route::post('/investors', [InvestorController::class, 'store'])->name('investors.store');
    Route::put('/investors/{investor}', [InvestorController::class, 'update'])->name('investors.update');
    Route::delete('/investors/{investor}', [InvestorController::class, 'destroy'])->name('investors.destroy');

    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
    Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
    Route::put('/investments/{investment}', [InvestmentController::class, 'update'])->name('investments.update');
    Route::delete('/investments/{investment}', [InvestmentController::class, 'destroy'])->name('investments.destroy');

    Route::get('/profit-sharing', [ProfitSharingController::class, 'index'])->name('profit-sharing.index');
    Route::post('/profit-sharing/preview', [ProfitSharingController::class, 'preview'])->name('profit-sharing.preview');
    Route::post('/profit-sharing', [ProfitSharingController::class, 'store'])->name('profit-sharing.store');

    Route::get('/profit-payments', [ProfitPaymentController::class, 'index'])->name('profit-payments.index');
    Route::post('/profit-payments', [ProfitPaymentController::class, 'store'])->name('profit-payments.store');
    Route::get('/investment-withdrawals', [InvestmentWithdrawalController::class, 'index'])->name('investment-withdrawals.index');
    Route::post('/investment-withdrawals', [InvestmentWithdrawalController::class, 'store'])->name('investment-withdrawals.store');
    Route::get('/investor-reports', [InvestorReportController::class, 'index'])->name('investor-reports.index');

    Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
    Route::post('/programs', [ProgramController::class, 'store'])->name('programs.store');
    Route::put('/programs/{program}', [ProgramController::class, 'update'])->name('programs.update');
    Route::delete('/programs/{program}', [ProgramController::class, 'destroy'])->name('programs.destroy');

    Route::get('/program-contributions', [ProgramContributionController::class, 'index'])->name('program-contributions.index');
    Route::get('/program-contributions/suggest-contributors', [ProgramContributionController::class, 'suggestContributors'])->name('program-contributions.suggest-contributors');
    Route::post('/program-contributions', [ProgramContributionController::class, 'store'])->name('program-contributions.store');
    Route::put('/program-contributions/{contribution}', [ProgramContributionController::class, 'update'])->name('program-contributions.update');
    Route::delete('/program-contributions/{contribution}', [ProgramContributionController::class, 'destroy'])->name('program-contributions.destroy');

    Route::get('/expense-categories', [ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
    Route::post('/expense-categories', [ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
    Route::put('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'update'])->name('expense-categories.update');
    Route::delete('/expense-categories/{expenseCategory}', [ExpenseCategoryController::class, 'destroy'])->name('expense-categories.destroy');

    Route::resource('cities', CityController::class);

    Route::get('/program-expenses', [ProgramExpenseController::class, 'index'])->name('program-expenses.index');
    Route::post('/program-expenses', [ProgramExpenseController::class, 'store'])->name('program-expenses.store');
    Route::put('/program-expenses/{expense}', [ProgramExpenseController::class, 'update'])->name('program-expenses.update');
    Route::delete('/program-expenses/{expense}', [ProgramExpenseController::class, 'destroy'])->name('program-expenses.destroy');
    Route::get('/program-transactions', [ProgramTransactionController::class, 'index'])->name('program-transactions.index');
    Route::get('/program-reports', [ProgramReportController::class, 'index'])->name('program-reports.index');

    Route::get('/content/{type}', [PortfolioContentController::class, 'index'])->name('content.index');
    Route::get('/content/{type}/create', [PortfolioContentController::class, 'create'])->name('content.create');
    Route::post('/content/{type}', [PortfolioContentController::class, 'store'])->name('content.store');
    Route::get('/content/{type}/{id}/edit', [PortfolioContentController::class, 'edit'])->name('content.edit');
    Route::put('/content/{type}/{id}', [PortfolioContentController::class, 'update'])->name('content.update');
    Route::delete('/content/{type}/{id}', [PortfolioContentController::class, 'destroy'])->name('content.destroy');
});
