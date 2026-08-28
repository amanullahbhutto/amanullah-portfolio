# Finance & Program Modules

This project now includes two admin-only modules integrated into the existing Laravel 11 / Bootstrap 5 admin panel.

## 1) Investors & Profit Sharing

Sidebar dropdown:
- Dashboard
- Investors
- Investments
- Profit Sharing
- Profit Payments
- Withdrawals
- Reports

Profit formula:
`Net Profit = Total Sales - Product Cost - Business Expenses`

Investor allocation:
`Investor Profit = Net Profit × Investor Profit Share %`

Owner allocation:
`Owner Profit = Net Profit - Total Investor Profit`

Because the supplied project did not contain an existing sales/cost accounting module, the Profit Sharing screen accepts the period's Total Sales, Product Cost and Business Expenses when calculating/confirming a profit period. Confirmed periods and investor allocations are stored permanently.

## 2) Programs

Sidebar dropdown:
- Programs
- Contributions
- Expense Categories
- Expenses
- Transactions
- Reports

Program formula:
`Remaining Balance = Total Money Received - Total Expenses`

Contributors support Name, Father Name, From, multiple separate payments and details. Expense categories are fully database-driven and can be quick-added from the Add Expense modal.

## AJAX

Create, update and delete actions for the new CRUD screens use the existing Fetch/AJAX + Bootstrap modal + toast pattern. Search/filter list refreshes do not require a full page reload.

## Roles & Permissions

The new permissions are created by migration `2026_08_26_000004_create_business_and_program_permissions.php` and assigned only to the existing `admin` role. Sidebar dropdowns and routes are also admin-only.

## Installation

After deploying the updated project files and taking a database backup, run:

```bash
php artisan migrate
php artisan permission:cache-reset
php artisan optimize:clear
```

If this is a fresh installation, the normal project seeder also contains the new permissions.

## Validation performed

- PHP syntax checks passed for all new controllers, models and migrations.
- Admin route registration was verified with Laravel route listing.
- Blade templates compile successfully through Laravel's Blade compiler.
- JavaScript passes Node syntax validation.

A full database migration was not executed inside the build container because the available PHP runtime does not include a PDO SQLite driver and no project MySQL server is available inside the container.
