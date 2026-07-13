<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\AuthController;

// Auth routes (guests only)
Route::middleware(['guest', 'no.cache'])->group(function () {
    Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',    [AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,2');
    Route::get('/register/success', function () {
        return view('auth.registered');
    })->name('register.success');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post')->middleware('throttle:5,1');
    Route::get('/register/verify',  [AuthController::class, 'showVerify'])->name('register.verify');
    Route::post('/register/verify', [AuthController::class, 'verifyAndRegister'])->name('register.verify.post');
    // Password reset via security question
    Route::post('/forgot-password/question', [AuthController::class, 'getSecurityQuestion'])->name('forgot.question');
    Route::post('/forgot-password/verify',   [AuthController::class, 'checkSecurityQuestion'])->name('forgot.verify');
    Route::post('/forgot-password/reset',    [AuthController::class, 'resetPasswordByQuestion'])->name('forgot.reset');
    Route::post('/forgot-password/send-email', [AuthController::class, 'sendPasswordResetEmail'])->name('forgot.email');
});

// Root redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Tripping Schedule Form (public — no login required)
Route::get('/tripping', [App\Http\Controllers\TripScheduleController::class, 'show'])->name('tripping');
Route::post('/tripping', [App\Http\Controllers\TripScheduleController::class, 'store'])->name('tripping.store');
Route::post('/api/tripping/save-team', [App\Http\Controllers\TripScheduleController::class, 'saveTeam'])->name('tripping.save-team')->middleware('auth');
Route::get('/api/tripping/clients', [App\Http\Controllers\TripScheduleController::class, 'searchClients']);
Route::get('/api/tripping/client-details', [App\Http\Controllers\TripScheduleController::class, 'clientDetails']);
Route::get('/api/tripping/agent-details', [App\Http\Controllers\TripScheduleController::class, 'agentDetails']);
Route::get('/api/tripping/properties', [App\Http\Controllers\TripScheduleController::class, 'searchProperties']);
Route::get('/api/tripping/property-details', [App\Http\Controllers\TripScheduleController::class, 'propertyDetails']);
Route::get('/api/tripping/check-duplicate', [App\Http\Controllers\TripScheduleController::class, 'checkDuplicate']);
Route::get('/api/tripping/clients', [App\Http\Controllers\TripScheduleController::class, 'searchClients']);
Route::get('/api/tripping/properties', [App\Http\Controllers\TripScheduleController::class, 'searchProperties']);

// Catch POST / for browsers with cached old form action
Route::post('/', function () {
    return redirect()->route('login');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', function() { return redirect()->route('login'); });
Route::get('/api/session-check', [AuthController::class, 'sessionCheck'])->name('session.check');

// Protected routes
// after
Route::middleware(['auth', 'no.cache'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('page.visible');

    // Summary Report
    Route::get('/summary-report', [App\Http\Controllers\SummaryReportController::class, 'index'])->name('summary-report')->middleware('page.visible');
    Route::get('/summary-report-yearly', [App\Http\Controllers\SummaryReportController::class, 'yearly'])->name('summary-report.yearly')->middleware('page.visible');
    Route::post('/api/summary-report/update', [App\Http\Controllers\SummaryReportController::class, 'update']);

    // ArkCrest Sales (Commission Income)
    Route::get('/arkcrest-sales', [App\Http\Controllers\ArkcrestSalesController::class, 'index'])->name('arkcrest-sales');
    Route::post('/api/arkcrest-sales/{id}/rate', [App\Http\Controllers\ArkcrestSalesController::class, 'saveRate'])->name('arkcrest-sales.rate');

    // Departments (Departmental Expenses)
    Route::get('/departments', [App\Http\Controllers\DepartmentalExpensesController::class, 'index'])->name('departments.admin')->middleware('page.visible');
    Route::get('/liquidation-print', [App\Http\Controllers\DepartmentalExpensesController::class, 'printLiquidation'])->name('liquidation.print');
    Route::get('/departmental-expenses/{id}/view-form', [App\Http\Controllers\DepartmentalExpensesController::class, 'viewForm'])->name('departmental-expenses.view-form');
    Route::post('/api/departmental-expenses', [App\Http\Controllers\DepartmentalExpensesController::class, 'store']);
    Route::put('/api/departmental-expenses/{id}', [App\Http\Controllers\DepartmentalExpensesController::class, 'update']);
    Route::delete('/api/departmental-expenses/{id}', [App\Http\Controllers\DepartmentalExpensesController::class, 'destroy']);

    // Autocomplete API Routes
    Route::get('/api/autocomplete/departments', [App\Http\Controllers\DepartmentalExpensesController::class, 'getDepartments']);
    Route::get('/api/autocomplete/categories', [App\Http\Controllers\DepartmentalExpensesController::class, 'getCategories']);

    // Old Department Routes
    Route::get('/departments/sales', [DepartmentController::class, 'sales'])->name('departments.sales');
    Route::get('/departments/hr', [DepartmentController::class, 'hr'])->name('departments.hr');
    Route::get('/departments/finance', [DepartmentController::class, 'finance'])->name('departments.finance');
    Route::get('/departments/executive', [DepartmentController::class, 'executive'])->name('departments.executive');

    // API Routes for Department Operations
    Route::post('/api/departments/add', [DepartmentController::class, 'addDepartment']);
    Route::delete('/api/departments/{id}/delete', [DepartmentController::class, 'deleteDepartment']);
    Route::post('/api/departments/{id}/budget', [DepartmentController::class, 'updateBudget']);
    Route::post('/api/departments/{id}/categories', [DepartmentController::class, 'addCategory']);
    Route::post('/api/departments/{id}/add-category-with-amount', [DepartmentController::class, 'addCategoryWithAmount']);
    Route::post('/api/departments/{id}/expenses', [DepartmentController::class, 'addExpense']);
    Route::put('/api/expenses/{id}', [DepartmentController::class, 'updateExpense']);
    Route::delete('/api/expenses/{id}', [DepartmentController::class, 'deleteExpense']);
    Route::delete('/api/categories/{id}', [DepartmentController::class, 'deleteCategory']);

    // Settings
    Route::get('/settings', [App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/deleted/bulk-restore', [SettingsController::class, 'bulkRestoreRecords'])->name('settings.deleted.bulk-restore');
    Route::post('/settings/deleted/bulk-delete', [SettingsController::class, 'bulkDeleteRecords'])->name('settings.deleted.bulk-delete');

    // Edit History / Audit Trail (Administrator only — dedicated controller & route)
    Route::get('/settings/edit-history', [App\Http\Controllers\Admin\EditHistoryController::class, 'index'])
        ->name('settings.edit-history')->middleware('admin');
    Route::post('/settings/notifications', [App\Http\Controllers\SettingsController::class, 'saveNotifications'])->name('settings.notifications');
    Route::post('/settings/smtp', [App\Http\Controllers\SettingsController::class, 'saveSmtp'])->name('settings.smtp');
    Route::post('/settings/profile', [App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/password', [App\Http\Controllers\SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/security-question', [App\Http\Controllers\SettingsController::class, 'updateSecurityQuestion'])->name('settings.security-question');
    Route::post('/settings/employee-info', [App\Http\Controllers\SettingsController::class, 'updateEmployeeInfo'])->name('settings.employee-info');
    Route::post('/settings/users/{id}/employee-info', [App\Http\Controllers\SettingsController::class, 'updateUserEmployeeInfo'])->name('settings.users.employee-info');
    Route::post('/settings/visibility', [App\Http\Controllers\SettingsController::class, 'savePageVisibility'])->name('settings.visibility');
    Route::post('/settings/privacy', [App\Http\Controllers\SettingsController::class, 'savePrivacyPolicy'])->name('settings.privacy');
    Route::post('/settings/deleted-records/{logId}/restore', [App\Http\Controllers\SettingsController::class, 'restoreRecord'])->name('settings.deleted.restore');
    Route::delete('/settings/deleted-records/{logId}', [App\Http\Controllers\SettingsController::class, 'permanentDeleteRecord'])->name('settings.deleted.purge');
    Route::post('/settings/deleted-records/bulk-restore', [App\Http\Controllers\SettingsController::class, 'bulkRestoreRecords'])->name('settings.deleted.bulkRestore');
    Route::post('/settings/deleted-records/bulk-delete', [App\Http\Controllers\SettingsController::class, 'bulkDeleteRecords'])->name('settings.deleted.bulkDelete');
    Route::post('/expenses/{id}/restore', [App\Http\Controllers\DepartmentalExpensesController::class, 'restore'])->name('expenses.restore');
    Route::delete('/expenses/{id}/purge', [App\Http\Controllers\DepartmentalExpensesController::class, 'purge'])->name('expenses.purge');
    Route::post('/settings/period-lock', [App\Http\Controllers\SettingsController::class, 'lockPeriod'])->name('settings.period-lock.store');
    Route::delete('/settings/period-lock/{id}', [App\Http\Controllers\SettingsController::class, 'unlockPeriod'])->name('settings.period-lock.destroy');

    // Backup & Restore
    Route::get('/settings/backup', [App\Http\Controllers\BackupController::class, 'index'])->name('backup.index');
    Route::post('/settings/backup/create-csv', [App\Http\Controllers\BackupController::class, 'createCsv'])->name('backup.create-csv');
    Route::post('/settings/backup/create-pdf', [App\Http\Controllers\BackupController::class, 'createPdf'])->name('backup.create-pdf');
    Route::post('/settings/backup/upload-restore', [App\Http\Controllers\BackupController::class, 'uploadAndRestore'])->name('backup.upload-restore');
    Route::get('/settings/backup/{filename}/download', [App\Http\Controllers\BackupController::class, 'download'])->name('backup.download');
    Route::post('/settings/backup/{filename}/restore', [App\Http\Controllers\BackupController::class, 'restore'])->name('backup.restore');
    Route::delete('/settings/backup/{filename}', [App\Http\Controllers\BackupController::class, 'destroy'])->name('backup.destroy');

    // Export Records
    Route::get('/admin/export', [App\Http\Controllers\AdminExportController::class, 'index'])->name('admin.export');
    Route::post('/admin/export/download', [App\Http\Controllers\AdminExportController::class, 'download'])->name('admin.export.download');

    // User management (admin only)
    Route::get('/settings/users', [App\Http\Controllers\SettingsController::class, 'users'])->name('settings.users');
    Route::post('/settings/users/{id}/approve', [App\Http\Controllers\SettingsController::class, 'approveUser'])->name('settings.users.approve');
    Route::post('/settings/users/{id}/reject', [App\Http\Controllers\SettingsController::class, 'rejectUser'])->name('settings.users.reject');
    Route::post('/settings/users/{id}/role', [App\Http\Controllers\SettingsController::class, 'updateRole'])->name('settings.users.role');
    Route::delete('/settings/users/{id}', [App\Http\Controllers\SettingsController::class, 'removeUser'])->name('settings.users.remove');
    Route::post('/settings/employee/add', [App\Http\Controllers\SettingsController::class, 'addEmployeeRecord'])->name('settings.employee.add');

    // Forms
    Route::get('/human-resource', [App\Http\Controllers\HumanResourceController::class, 'index'])->name('human-resource')->middleware('page.visible');
    Route::get('/human-resource/employee-data', [App\Http\Controllers\HumanResourceController::class, 'employeeData'])->name('hr.employee-data')->middleware('page.visible');
    Route::get('/human-resource/contact-list', [App\Http\Controllers\HumanResourceController::class, 'contactList'])->name('hr.contact-list')->middleware('page.visible');

    // HR Forms (save/load)
    Route::post('/api/hr-forms', [App\Http\Controllers\HrFormController::class, 'store'])->name('hr-forms.store');
    Route::get('/api/hr-forms', [App\Http\Controllers\HrFormController::class, 'index'])->name('hr-forms.index');
    Route::delete('/api/hr-forms/{id}', [App\Http\Controllers\HrFormController::class, 'destroy'])->name('hr-forms.destroy');
    Route::get('/forms', [App\Http\Controllers\FormsController::class, 'index'])->name('forms')->middleware('page.visible');
    Route::get('/forms/site-visit', [App\Http\Controllers\FormsController::class, 'siteVisit'])->name('forms.site-visit');
    Route::get('/api/forms/control-number', [App\Http\Controllers\FormsController::class, 'nextControlNumber']);
    Route::post('/api/forms/control-number/increment', [App\Http\Controllers\FormsController::class, 'incrementControlNumber']);
    Route::post('/api/forms/budget-request/submit', [App\Http\Controllers\FormsController::class, 'submitBudgetRequest']);

    // Sales & Marketing
    Route::get('/sales-marketing', [App\Http\Controllers\SalesMarketingController::class, 'index'])->name('sales-marketing')->middleware('page.visible');
    Route::get('/sales-marketing/{id}', [App\Http\Controllers\SalesMarketingController::class, 'show'])->name('sales-marketing.show');

    // Property List
    Route::get('/property-list', [App\Http\Controllers\SalesMarketingController::class, 'propertyList'])->name('property-list');

    // Client Database
    Route::get('/client-database', [App\Http\Controllers\SalesMarketingController::class, 'clientDatabase'])->name('client-database');
    Route::get('/api/client-database/{id}/prefill', [App\Http\Controllers\SalesMarketingController::class, 'prefillCommission']);
    Route::get('/api/client-database/check-duplicate', [App\Http\Controllers\SalesMarketingController::class, 'checkDuplicate']);
    Route::get('/reserved-clients', [App\Http\Controllers\SalesMarketingController::class, 'reservedClients'])->name('reserved-clients');
    Route::post('/reserved-clients/add', [App\Http\Controllers\SalesMarketingController::class, 'storeReservedClient'])->name('reserved-clients.store');
    Route::put('/reserved-clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'updateReservedClient'])->name('reserved-clients.update');
    Route::delete('/reserved-clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'destroyReservedClient'])->name('reserved-clients.destroy');
    Route::get('/api/reserved-clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'getReservedClient']);
    // Clients (contact info)
    Route::post('/clients', [App\Http\Controllers\SalesMarketingController::class, 'storeClient'])->name('clients.store');
    Route::put('/clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'updateClient'])->name('clients.update');
    Route::delete('/clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'destroyClient'])->name('clients.destroy');
    Route::get('/api/clients/{id}', [App\Http\Controllers\SalesMarketingController::class, 'getClient']);
    Route::post('/client-database', [App\Http\Controllers\SalesMarketingController::class, 'store'])->name('client-database.store');
    Route::put('/client-database/{id}', [App\Http\Controllers\SalesMarketingController::class, 'update'])->name('client-database.update');
    Route::patch('/client-database/{id}/status', [App\Http\Controllers\SalesMarketingController::class, 'updateClientStatus'])->name('client-database.status');
    Route::patch('/client-database/{id}/downpayment-status', [App\Http\Controllers\SalesMarketingController::class, 'updateDownpaymentStatus'])->name('client-database.downpayment-status');
    Route::patch('/client-database/{id}/downpayment-installment', [App\Http\Controllers\SalesMarketingController::class, 'updateDownpaymentInstallment'])->name('client-database.downpayment-installment');
    Route::get('/api/client-database/{id}/installments', [App\Http\Controllers\SalesMarketingController::class, 'getInstallments']);
    Route::post('/api/client-database/{id}/installments/setup', [App\Http\Controllers\SalesMarketingController::class, 'setupInstallments']);
    Route::patch('/api/installments/{id}/amount', [App\Http\Controllers\SalesMarketingController::class, 'updateInstallmentAmount']);
    Route::patch('/api/installments/{id}/paid', [App\Http\Controllers\SalesMarketingController::class, 'markInstallmentPaid']);
    Route::patch('/api/installments/{id}/unpaid', [App\Http\Controllers\SalesMarketingController::class, 'unmarkInstallmentPaid']);
    Route::delete('/client-database/{id}', [App\Http\Controllers\SalesMarketingController::class, 'destroy'])->name('client-database.destroy');

    // Site Visit Database
    Route::get('/site-visit-database', [App\Http\Controllers\TripScheduleController::class, 'database'])->name('site-visit-database');
    Route::get('/api/site-visit-database/pending', [App\Http\Controllers\TripScheduleController::class, 'pendingJson']);
    Route::get('/api/trips/{id}/prefill', [App\Http\Controllers\TripScheduleController::class, 'prefillData']);
    Route::patch('/site-visit-database/{id}/status', [App\Http\Controllers\TripScheduleController::class, 'updateStatus'])->name('site-visit-database.status');
    Route::patch('/site-visit-database/{id}/approve', [App\Http\Controllers\TripScheduleController::class, 'approve'])->name('site-visit-database.approve');
    Route::patch('/site-visit-database/{id}/reject', [App\Http\Controllers\TripScheduleController::class, 'reject'])->name('site-visit-database.reject');
    Route::patch('/site-visit-database/{id}/cancel', [App\Http\Controllers\TripScheduleController::class, 'cancel'])->name('site-visit-database.cancel');
    Route::patch('/site-visit-database/{id}/done', [App\Http\Controllers\TripScheduleController::class, 'markDone'])->name('site-visit-database.done');
    Route::get('/site-visit-database/{id}/reserve', [App\Http\Controllers\TripScheduleController::class, 'reserve'])->name('site-visit-database.reserve');
    Route::patch('/site-visit-database/{id}/reschedule', [App\Http\Controllers\TripScheduleController::class, 'reschedule'])->name('site-visit-database.reschedule');
    Route::delete('/site-visit-database/{id}', [App\Http\Controllers\TripScheduleController::class, 'destroy'])->name('site-visit-database.destroy');

    // Team Management (admin only)
    Route::post('/settings/teams', [App\Http\Controllers\SettingsController::class, 'storeTeam'])->name('settings.teams.store');
    Route::delete('/settings/teams/{id}', [App\Http\Controllers\SettingsController::class, 'destroyTeam'])->name('settings.teams.destroy');
    Route::put('/settings/teams/{id}', [App\Http\Controllers\SettingsController::class, 'updateTeam'])->name('settings.teams.update');
    Route::post('/settings/teams/{id}/quota', [App\Http\Controllers\SettingsController::class, 'setTeamQuota'])->name('settings.teams.quota');
    Route::delete('/settings/quotas/{id}', [App\Http\Controllers\SettingsController::class, 'destroyQuota'])->name('settings.quotas.destroy');
    Route::post('/settings/agents', [App\Http\Controllers\SettingsController::class, 'storeAgent'])->name('settings.agents.store');
    Route::delete('/settings/agents/{id}', [App\Http\Controllers\SettingsController::class, 'destroyAgent'])->name('settings.agents.destroy');
    Route::patch('/settings/agents/{id}', [App\Http\Controllers\SettingsController::class, 'updateAgent'])->name('settings.agents.update');
    Route::post('/settings/agents/{id}/toggle', [App\Http\Controllers\SettingsController::class, 'toggleAgentStatus'])->name('settings.agents.toggle');

    // Property Management (admin only)
    Route::post('/settings/properties', [App\Http\Controllers\SettingsController::class, 'storeProperty'])->name('settings.properties.store');
    Route::delete('/settings/properties/{id}', [App\Http\Controllers\SettingsController::class, 'destroyProperty'])->name('settings.properties.destroy');
    Route::get('/api/settings/properties', [App\Http\Controllers\SettingsController::class, 'getProperties'])->name('settings.properties.index');

    // Permission Requests
    Route::post('/api/permission-requests', [App\Http\Controllers\PermissionRequestController::class, 'store'])->name('permission-requests.store');
    Route::post('/api/permission-requests/{id}/review', [App\Http\Controllers\PermissionRequestController::class, 'review'])->name('permission-requests.review');
    Route::get('/api/permission-requests/check', [App\Http\Controllers\PermissionRequestController::class, 'check']);
    Route::get('/api/permission-requests/pending', [App\Http\Controllers\PermissionRequestController::class, 'pending']);
    Route::get('/api/permission-requests/by-notif/{notifId}', [App\Http\Controllers\PermissionRequestController::class, 'byNotif']);
    Route::get('/commission-dashboard', [App\Http\Controllers\CommissionMonitoringController::class, 'dashboard'])->name('commission-dashboard');
    Route::get('/commission-monitoring', [App\Http\Controllers\CommissionMonitoringController::class, 'index'])->name('commission-monitoring')->middleware('page.visible');
    Route::post('/commission-monitoring', [App\Http\Controllers\CommissionMonitoringController::class, 'store'])->name('commission-monitoring.store');
    Route::get('/api/commission-monitoring/{id}', [App\Http\Controllers\CommissionMonitoringController::class, 'show']);
    Route::put('/api/commission-monitoring/{id}', [App\Http\Controllers\CommissionMonitoringController::class, 'update']);
    Route::put('/commission-monitoring/{id}', [App\Http\Controllers\CommissionMonitoringController::class, 'update'])->name('commission-monitoring.update');
    Route::delete('/commission-monitoring/{id}', [App\Http\Controllers\CommissionMonitoringController::class, 'destroy'])->name('commission-monitoring.destroy');
    Route::post('/commission-monitoring/bulk-delete', [App\Http\Controllers\CommissionMonitoringController::class, 'bulkDestroy'])->name('commission-monitoring.bulk-delete');

    // Calendar
    Route::get('/calendar', [App\Http\Controllers\CalendarController::class, 'index'])->name('calendar')->middleware('page.visible');
    Route::get('/sales-calendar', [App\Http\Controllers\CalendarController::class, 'salesCalendar'])->name('sales-calendar');

    // Global Search API
    Route::get('/api/global-search', [App\Http\Controllers\GlobalSearchController::class, 'search']);

    // Notes
    Route::post('/notes', [App\Http\Controllers\NotesController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{id}', [App\Http\Controllers\NotesController::class, 'destroy'])->name('notes.destroy');
    Route::post('/notes/{id}/done', [App\Http\Controllers\NotesController::class, 'done'])->name('notes.done');
    Route::post('/notes/{id}/snooze', [App\Http\Controllers\NotesController::class, 'snooze'])->name('notes.snooze');
    Route::post('/notes/done-by-title', [App\Http\Controllers\NotesController::class, 'doneByTitle'])->name('notes.doneByTitle');

    // ARC Personnel Contact List
    Route::post('/settings/personnel-contacts', [App\Http\Controllers\SettingsController::class, 'storePersonnelContact'])->name('settings.personnel-contacts.store');
    Route::put('/settings/personnel-contacts/{id}', [App\Http\Controllers\SettingsController::class, 'updatePersonnelContact'])->name('settings.personnel-contacts.update');
    Route::delete('/settings/personnel-contacts/{id}', [App\Http\Controllers\SettingsController::class, 'destroyPersonnelContact'])->name('settings.personnel-contacts.destroy');
    Route::post('/api/personnel-contacts/reorder', [App\Http\Controllers\SettingsController::class, 'reorderPersonnelContacts']);

    // Notifications
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::get('/api/user-visibility/{id}', [App\Http\Controllers\SettingsController::class, 'getUserVisibility']);
    Route::post('/notifications/clear-all', [App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clearAll');
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::get('/api/notifications/count', [App\Http\Controllers\NotificationController::class, 'count'])->name('notifications.count');
    Route::get('/api/notifications/latest', [App\Http\Controllers\NotificationController::class, 'latest'])->name('notifications.latest');

    // Online presence
    Route::post('/api/ping', function () {
        auth()->user()->update(['last_seen_at' => now()]);
        return response()->json(['ok' => true]);
    })->name('api.ping');

    Route::get('/api/online-users', function () {
        $onlineIds = \App\Models\User::whereNotNull('last_seen_at')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->pluck('id');
        return response()->json($onlineIds);
    })->name('api.online-users');

}); // end auth middleware

// External scheduler trigger (called by GitHub Actions)
Route::post('/api/run-reminders', function (\Illuminate\Http\Request $request) {
    $secret = config('app.scheduler_secret');
    $token  = $request->bearerToken();
    if (!$secret || $token !== $secret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    \Artisan::call('events:send-reminders');
    \Artisan::call('notes:send-reminders');
    \Artisan::call('commissions:send-reminders');
    return response()->json(['ok' => true, 'output' => \Artisan::output()]);
});