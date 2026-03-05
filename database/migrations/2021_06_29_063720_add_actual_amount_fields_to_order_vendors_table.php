<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddActualAmountFieldsToOrderVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_vendors', 'subtotal_amount')) {
            Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 10, 2)->nullable()->after('coupon_code');
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
