<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $salesReturn = Account::where('system_key_name', 'sales-return')->first();

        if (!$salesReturn) {
            // Find parent account for Income (e.g. ID 39 or Income parent)
            $incomeParent = Account::where('system_key_name', 'sales-revenue')->first();
            $parentId = $incomeParent ? $incomeParent->parent_id : 39;

            Account::create([
                'parent_id'         => $parentId,
                'account_code'      => 4150,
                'account_name'      => 'Sales Return',
                'account_type'      => 'Income',
                'default_type'      => 'System',
                'system_key_name'   => 'sales-return',
                'balance_type'      => 'Debit',
                'status'            => 'active',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Account::where('system_key_name', 'sales-return')->delete();
    }
};
