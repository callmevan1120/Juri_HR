<?php

namespace Database\Seeders;

use App\Models\AccountingAccount;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class AccountingMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('accounting_accounts') || ! Schema::hasTable('companies')) {
            return;
        }

        Company::query()->each(function (Company $company): void {
            foreach ($this->accounts() as $account) {
                AccountingAccount::query()->updateOrCreate([
                    'company_id' => $company->id,
                    'code' => $account['code'],
                ], [
                    'name' => $account['name'],
                    'type' => $account['type'],
                    'normal_balance' => $this->normalBalance($account['type']),
                    'is_active' => true,
                    'metadata' => [
                        'seeded' => true,
                        'source' => 'real_master_data',
                    ],
                ]);
            }
        });
    }

    /**
     * @return list<array{code:string,name:string,type:string}>
     */
    private function accounts(): array
    {
        return [
            ['code' => '1100', 'name' => 'Cash / Bank', 'type' => AccountingAccount::TYPE_ASSET],
            ['code' => '1200', 'name' => 'Inventory Asset', 'type' => AccountingAccount::TYPE_ASSET],
            ['code' => '1300', 'name' => 'Accounts Receivable', 'type' => AccountingAccount::TYPE_ASSET],
            ['code' => '2100', 'name' => 'Output Tax Payable', 'type' => AccountingAccount::TYPE_LIABILITY],
            ['code' => '2200', 'name' => 'Inventory Clearing', 'type' => AccountingAccount::TYPE_LIABILITY],
            ['code' => '2300', 'name' => 'Accounts Payable', 'type' => AccountingAccount::TYPE_LIABILITY],
            ['code' => '3100', 'name' => 'Owner Equity', 'type' => AccountingAccount::TYPE_EQUITY],
            ['code' => '4100', 'name' => 'Sales Revenue', 'type' => AccountingAccount::TYPE_REVENUE],
            ['code' => '5100', 'name' => 'Cost of Goods Sold', 'type' => AccountingAccount::TYPE_EXPENSE],
            ['code' => '5200', 'name' => 'Employee Reimbursements', 'type' => AccountingAccount::TYPE_EXPENSE],
            ['code' => '5300', 'name' => 'Purchase Expenses', 'type' => AccountingAccount::TYPE_EXPENSE],
        ];
    }

    private function normalBalance(string $type): string
    {
        return in_array($type, [AccountingAccount::TYPE_ASSET, AccountingAccount::TYPE_EXPENSE], true)
            ? 'debit'
            : 'credit';
    }
}
