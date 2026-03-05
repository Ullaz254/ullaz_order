<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrdersAddColumnLoyalityPointsEarnedOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'loyalty_points_earned_order')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->decimal('loyalty_points_earned_order', $precision = 10, $scale = 2)->after('loyalty_points_earned')->nullable();
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
        // Reverse migration if needed
    }
}
