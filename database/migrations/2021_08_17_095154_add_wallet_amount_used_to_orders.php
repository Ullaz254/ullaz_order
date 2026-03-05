<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletAmountUsedToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'wallet_amount_used')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('wallet_amount_used', 12, 2)->unsigned()->default(0)->after('total_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'wallet_amount_used')) {
                $table->dropColumn('wallet_amount_used');
            }
        });
    }
}
