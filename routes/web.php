<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\GmailReceiptController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\ProjectInvestmentController;
use App\Http\Controllers\ProjectInvestmentTransactionController;
use App\Http\Controllers\IouController;
use App\Http\Controllers\JobApplicationAnalyticsController;
use App\Http\Controllers\JobApplicationController;
use App\Http\Controllers\JobApplicationInterviewController;
use App\Http\Controllers\JobApplicationKanbanController;
use App\Http\Controllers\JobApplicationOfferController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CycleMenuController;
use App\Http\Controllers\CycleMenuDayController;
use App\Http\Controllers\CycleMenuItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UtilityBillController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CreditNoteController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\InvoicingDashboardController;
use App\Http\Controllers\RecurringInvoiceController;
use App\Http\Controllers\RecurringInvoiceItemController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes - Require Authentication
Route::middleware('auth')->group(function () {
    // Tenant Management Routes (without tenant middleware - needed for initial setup)
    Route::prefix('tenants')->name('tenants.')->group(function () {
        Route::get('/select', [TenantController::class, 'select'])->name('select');
        Route::get('/create', [TenantController::class, 'create'])->name('create');
        Route::post('/', [TenantController::class, 'store'])->name('store');
    });

    // Routes requiring tenant context
    Route::middleware('tenant')->group(function () {
        // Dashboard Routes
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
        Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');

        // Tenant Management Routes (with tenant middleware - require active tenant)
        Route::prefix('tenants')->name('tenants.')->group(function () {
            Route::get('/', [TenantController::class, 'index'])->name('index');
            Route::get('/{tenant}', [TenantController::class, 'show'])->name('show');
            Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
            Route::patch('/{tenant}', [TenantController::class, 'update'])->name('update');
            Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy');
            Route::post('/{tenant}/switch', [TenantController::class, 'switch'])->name('switch');
        });

        // Profile Routes
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

        // Settings Routes
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/account', [SettingsController::class, 'account'])->name('account');
        Route::get('/application', [SettingsController::class, 'application'])->name('application');
        Route::get('/notifications', [SettingsController::class, 'notifications'])->name('notifications');

        // Gmail Receipt Integration
        Route::get('/gmail-receipts', [GmailReceiptController::class, 'settings'])->name('gmail-receipts');
        Route::post('/gmail-receipts/connect', [GmailReceiptController::class, 'connect'])->name('gmail-receipts.connect');
        Route::get('/gmail-receipts/callback', [GmailReceiptController::class, 'callback'])->name('gmail-receipts.callback');
        Route::post('/gmail-receipts/disconnect', [GmailReceiptController::class, 'disconnect'])->name('gmail-receipts.disconnect');
        Route::post('/gmail-receipts/sync', [GmailReceiptController::class, 'sync'])->name('gmail-receipts.sync');
        Route::post('/gmail-receipts/toggle-auto-sync', [GmailReceiptController::class, 'toggleAutoSync'])->name('gmail-receipts.toggle-auto-sync');
        Route::get('/gmail-receipts/emails', [GmailReceiptController::class, 'processedEmails'])->name('gmail-receipts.emails');
        Route::post('/gmail-receipts/emails/{processedEmail}/retry', [GmailReceiptController::class, 'retryEmail'])->name('gmail-receipts.emails.retry');
    });

    // Life Management Platform Routes
    // Analytics routes must come before resource routes to prevent conflicts
    Route::get('subscriptions/analytics/summary', [SubscriptionController::class, 'analyticsSummary'])->name('subscriptions.analytics.summary');
    Route::get('subscriptions/analytics/spending', [SubscriptionController::class, 'spendingAnalytics'])->name('subscriptions.analytics.spending');
    Route::get('subscriptions/analytics/category-breakdown', [SubscriptionController::class, 'categoryBreakdown'])->name('subscriptions.analytics.category-breakdown');

    Route::resource('subscriptions', SubscriptionController::class);
    Route::patch('subscriptions/{subscription}/pause', [SubscriptionController::class, 'pause'])->name('subscriptions.pause');
    Route::patch('subscriptions/{subscription}/resume', [SubscriptionController::class, 'resume'])->name('subscriptions.resume');
    Route::patch('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    Route::resource('contracts', ContractController::class);
    Route::post('contracts/{contract}/terminate', [ContractController::class, 'terminate'])->name('contracts.terminate');
    Route::post('contracts/{contract}/renew', [ContractController::class, 'renew'])->name('contracts.renew');
    Route::post('contracts/{contract}/add-amendment', [ContractController::class, 'addAmendment'])->name('contracts.add-amendment');
    Route::resource('warranties', WarrantyController::class);
    Route::post('warranties/{warranty}/file-claim', [WarrantyController::class, 'fileClaim'])->name('warranties.file-claim');
    Route::patch('warranties/{warranty}/update-claim', [WarrantyController::class, 'updateClaim'])->name('warranties.update-claim');
    Route::post('warranties/{warranty}/transfer', [WarrantyController::class, 'transfer'])->name('warranties.transfer');
    Route::post('warranties/{warranty}/add-maintenance-reminder', [WarrantyController::class, 'addMaintenanceReminder'])->name('warranties.add-maintenance-reminder');

    // Investment Analytics routes must come before resource routes to prevent conflicts
    Route::get('investments/analytics', [InvestmentController::class, 'analyticsDashboard'])->name('investments.analytics');

    // Import routes should come before resource routes to avoid conflicts with {investment} parameter
    Route::get('investments/import', [InvestmentController::class, 'importForm'])->name('investments.import.form');
    Route::post('investments/import', [InvestmentController::class, 'importCsv'])->name('investments.import');

    Route::resource('investments', InvestmentController::class);
    Route::post('investments/{investment}/record-transaction', [InvestmentController::class, 'recordTransaction'])->name('investments.record-transaction');
    Route::post('investments/{investment}/record-buy', [InvestmentController::class, 'recordBuy'])->name('investments.record-buy');
    Route::post('investments/{investment}/record-sell', [InvestmentController::class, 'recordSell'])->name('investments.record-sell');
    Route::post('investments/{investment}/record-dividend', [InvestmentController::class, 'recordDividend'])->name('investments.record-dividend');
    Route::post('investments/{investment}/update-price', [InvestmentController::class, 'updatePrice'])->name('investments.update-price');
    Route::get('investments/goals/index', [InvestmentController::class, 'goalIndex'])->name('investments.goals.index');
    Route::post('investments/goals/store', [InvestmentController::class, 'goalStore'])->name('investments.goals.store');
    Route::patch('investments/goals/{goal}/update', [InvestmentController::class, 'goalUpdate'])->name('investments.goals.update');
    Route::delete('investments/goals/{goal}', [InvestmentController::class, 'goalDestroy'])->name('investments.goals.destroy');
    Route::get('investments/tax-reports/index', [InvestmentController::class, 'taxReportIndex'])->name('investments.tax-reports.index');
    Route::get('investments/tax-reports/capital-gains', [InvestmentController::class, 'capitalGainsReport'])->name('investments.tax-reports.capital-gains');
    Route::get('investments/tax-reports/dividend-income', [InvestmentController::class, 'dividendIncomeReport'])->name('investments.tax-reports.dividend-income');
    Route::get('investments/rebalancing/alerts', [InvestmentController::class, 'rebalancingAlerts'])->name('investments.rebalancing.alerts');
    Route::post('investments/rebalancing/recommendations', [InvestmentController::class, 'rebalancingRecommendations'])->name('investments.rebalancing.recommendations');

    // Project Investment Routes
    Route::get('project-investments/analytics', [ProjectInvestmentController::class, 'analytics'])->name('project-investments.analytics');
    Route::resource('project-investments', ProjectInvestmentController::class);
    Route::post('project-investments/{project_investment}/update-value', [ProjectInvestmentController::class, 'updateValue'])->name('project-investments.update-value');

    // Project Investment Transaction Routes
    Route::get('project-investments/{project_investment}/transactions', [ProjectInvestmentTransactionController::class, 'index'])->name('project-investment-transactions.index');
    Route::get('project-investments/{project_investment}/transactions/create', [ProjectInvestmentTransactionController::class, 'create'])->name('project-investment-transactions.create');
    Route::post('project-investments/{project_investment}/transactions', [ProjectInvestmentTransactionController::class, 'store'])->name('project-investment-transactions.store');
    Route::get('project-investment-transactions/{project_investment_transaction}/edit', [ProjectInvestmentTransactionController::class, 'edit'])->name('project-investment-transactions.edit');
    Route::put('project-investment-transactions/{project_investment_transaction}', [ProjectInvestmentTransactionController::class, 'update'])->name('project-investment-transactions.update');
    Route::delete('project-investment-transactions/{project_investment_transaction}', [ProjectInvestmentTransactionController::class, 'destroy'])->name('project-investment-transactions.destroy');

    Route::resource('expenses', ExpenseController::class);
    Route::get('expenses/analytics', [ExpenseController::class, 'analytics'])->name('expenses.analytics');
    Route::patch('expenses/{expense}/mark-reimbursed', [ExpenseController::class, 'markReimbursed'])->name('expenses.mark-reimbursed');
    Route::post('expenses/{expense}/duplicate', [ExpenseController::class, 'duplicate'])->name('expenses.duplicate');
    Route::post('expenses/bulk-action', [ExpenseController::class, 'bulkAction'])->name('expenses.bulk-action');

    // Budget Analytics routes must come before resource routes to prevent conflicts
    Route::get('budgets/analytics', [BudgetController::class, 'analytics'])->name('budgets.analytics');

    Route::resource('budgets', BudgetController::class);

    // IOU Routes
    Route::resource('ious', IouController::class);
    Route::post('ious/{iou}/record-payment', [IouController::class, 'recordPayment'])->name('ious.record-payment');
    Route::patch('ious/{iou}/mark-paid', [IouController::class, 'markPaid'])->name('ious.mark-paid');
    Route::patch('ious/{iou}/cancel', [IouController::class, 'cancel'])->name('ious.cancel');

    // Utility Bills Analytics routes must come before resource routes to prevent conflicts
    Route::get('utility-bills/analytics/summary', [UtilityBillController::class, 'analyticsSummary'])->name('utility-bills.analytics-summary');
    Route::get('utility-bills/analytics/spending', [UtilityBillController::class, 'spendingAnalytics'])->name('utility-bills.spending-analytics');
    Route::get('utility-bills/analytics/due-date', [UtilityBillController::class, 'dueDateAnalytics'])->name('utility-bills.due-date-analytics');

    Route::resource('utility-bills', UtilityBillController::class);
    Route::patch('utility-bills/{utility_bill}/mark-paid', [UtilityBillController::class, 'markPaid'])->name('utility-bills.mark-paid');
    Route::patch('utility-bills/{utility_bill}/set-auto-pay', [UtilityBillController::class, 'setAutoPay'])->name('utility-bills.set-auto-pay');
    Route::post('utility-bills/{utility_bill}/duplicate', [UtilityBillController::class, 'duplicate'])->name('utility-bills.duplicate');

    // Job Application Routes
    // Analytics and Kanban routes must come before resource routes to prevent conflicts
    Route::get('job-applications/analytics', [JobApplicationAnalyticsController::class, 'index'])->name('job-applications.analytics');
    Route::get('job-applications/analytics/export', [JobApplicationAnalyticsController::class, 'export'])->name('job-applications.analytics.export');
    Route::get('job-applications/kanban', [JobApplicationKanbanController::class, 'index'])->name('job-applications.kanban');
    Route::patch('job-applications/{application}/kanban-status', [JobApplicationKanbanController::class, 'updateStatus'])->name('job-applications.kanban.update-status');

    Route::resource('job-applications', JobApplicationController::class);
    Route::patch('job-applications/{application}/archive', [JobApplicationController::class, 'archive'])->name('job-applications.archive');
    Route::patch('job-applications/{application}/unarchive', [JobApplicationController::class, 'unarchive'])->name('job-applications.unarchive');

    // Nested Interview Routes
    Route::resource('job-applications.interviews', JobApplicationInterviewController::class);
    Route::patch('job-applications/{job_application}/interviews/{interview}/complete', [JobApplicationInterviewController::class, 'complete'])->name('job-applications.interviews.complete');

    // Nested Offer Routes
    Route::resource('job-applications.offers', JobApplicationOfferController::class)->parameters(['offers' => 'offer']);
    Route::patch('job-applications/{job_application}/offers/{offer}/accept', [JobApplicationOfferController::class, 'accept'])->name('job-applications.offers.accept');
    Route::patch('job-applications/{job_application}/offers/{offer}/decline', [JobApplicationOfferController::class, 'decline'])->name('job-applications.offers.decline');

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/data', [NotificationController::class, 'data'])->name('data');
        Route::get('/preferences', [NotificationController::class, 'preferences'])->name('preferences');
        Route::post('/preferences', [NotificationController::class, 'updatePreferences'])->name('preferences.update');
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('/stats', [NotificationController::class, 'stats'])->name('stats');
    });

    // Currency Routes
    Route::prefix('currency')->name('currency.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::get('/freelance-rate-calculator', [CurrencyController::class, 'freelanceRateCalculator'])->name('freelance-rate-calculator');
        Route::post('/refresh-rate', [CurrencyController::class, 'refreshRate'])->name('refresh-rate');
        Route::get('/freshness-info', [CurrencyController::class, 'getFreshnessInfo'])->name('freshness-info');
    });

    // File Management Routes
    Route::prefix('files')->name('files.')->group(function () {
        Route::post('{category}/upload', [FileUploadController::class, 'upload'])->name('upload');
        Route::get('{category}/{filename}/download', [FileUploadController::class, 'download'])->name('download');
        Route::get('{category}/{filename}/view', [FileUploadController::class, 'view'])->name('view');
        Route::get('{category}/{filename}/info', [FileUploadController::class, 'getFileInfo'])->name('info');
        Route::delete('{category}/{filename}', [FileUploadController::class, 'delete'])->name('delete');
        Route::get('types/{category?}', [FileUploadController::class, 'getAllowedTypes'])->name('types');
    });

    // Cycle Menu Routes
    Route::resource('cycle-menus', CycleMenuController::class);
    Route::put('cycle-menu-days/{cycle_menu_day}', [CycleMenuDayController::class, 'update'])->name('cycle-menu-days.update');
    Route::prefix('cycle-menu-items')->name('cycle-menu-items.')->group(function () {
        Route::post('/', [CycleMenuItemController::class, 'store'])->name('store');
        Route::put('{cycle_menu_item}', [CycleMenuItemController::class, 'update'])->name('update');
        Route::delete('{cycle_menu_item}', [CycleMenuItemController::class, 'destroy'])->name('destroy');
        Route::post('reorder', [CycleMenuItemController::class, 'reorder'])->name('reorder');
    });

    // Holidays Routes
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');

    // Invoicing Routes
    Route::prefix('invoicing')->name('invoicing.')->group(function () {
        // Dashboard
        Route::get('/dashboard', [InvoicingDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export/invoices', [InvoicingDashboardController::class, 'exportInvoices'])->name('export.invoices');
        Route::get('/export/payments', [InvoicingDashboardController::class, 'exportPayments'])->name('export.payments');

        // Customers
        Route::resource('customers', CustomerController::class);

        // Invoices
        Route::resource('invoices', InvoiceController::class);

        // Invoice Items (nested resource)
        Route::post('invoices/{invoice}/items', [InvoiceItemController::class, 'store'])
            ->name('invoices.items.store');
        Route::put('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'update'])
            ->name('invoices.items.update');
        Route::delete('invoices/{invoice}/items/{item}', [InvoiceItemController::class, 'destroy'])
            ->name('invoices.items.destroy');

        // Invoice Actions
        Route::post('invoices/{invoice}/issue', [InvoiceController::class, 'issue'])
            ->name('invoices.issue');
        Route::post('invoices/{invoice}/void', [InvoiceController::class, 'void'])
            ->name('invoices.void');

        // Invoice PDF
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'viewPdf'])
            ->name('invoices.pdf.view');
        Route::get('invoices/{invoice}/pdf/download', [InvoiceController::class, 'downloadPdf'])
            ->name('invoices.pdf.download');

        // Invoice Email
        Route::post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])
            ->name('invoices.send-email');
        Route::post('invoices/{invoice}/send-reminder', [InvoiceController::class, 'sendReminder'])
            ->name('invoices.send-reminder');

        // Payments
        Route::post('invoices/{invoice}/payments', [PaymentController::class, 'store'])
            ->name('invoices.payments.store');
        Route::get('invoices/{invoice}/payments/{payment}', [PaymentController::class, 'show'])
            ->name('invoices.payments.show');
        Route::delete('invoices/{invoice}/payments/{payment}', [PaymentController::class, 'destroy'])
            ->name('invoices.payments.destroy');

        // Credit Notes
        Route::resource('credit-notes', CreditNoteController::class);
        Route::post('credit-notes/{credit_note}/apply', [CreditNoteController::class, 'apply'])
            ->name('credit-notes.apply');

        // Tax Rates
        Route::resource('tax-rates', TaxRateController::class)->except(['show']);

        // Discounts
        Route::resource('discounts', DiscountController::class)->except(['show']);

        // Recurring Invoices
        Route::resource('recurring-invoices', RecurringInvoiceController::class);
        Route::post('recurring-invoices/{recurring_invoice}/pause', [RecurringInvoiceController::class, 'pause'])
            ->name('recurring-invoices.pause');
        Route::post('recurring-invoices/{recurring_invoice}/resume', [RecurringInvoiceController::class, 'resume'])
            ->name('recurring-invoices.resume');
        Route::post('recurring-invoices/{recurring_invoice}/cancel', [RecurringInvoiceController::class, 'cancel'])
            ->name('recurring-invoices.cancel');
        Route::post('recurring-invoices/{recurring_invoice}/generate-now', [RecurringInvoiceController::class, 'generateNow'])
            ->name('recurring-invoices.generate-now');

        // Recurring Invoice Items
        Route::post('recurring-invoices/{recurring_invoice}/items', [RecurringInvoiceItemController::class, 'store'])
            ->name('recurring-invoices.items.store');
        Route::put('recurring-invoices/{recurring_invoice}/items/{item}', [RecurringInvoiceItemController::class, 'update'])
            ->name('recurring-invoices.items.update');
        Route::delete('recurring-invoices/{recurring_invoice}/items/{item}', [RecurringInvoiceItemController::class, 'destroy'])
            ->name('recurring-invoices.items.destroy');
    });
    }); // End of tenant middleware group
}); // End of auth middleware group
