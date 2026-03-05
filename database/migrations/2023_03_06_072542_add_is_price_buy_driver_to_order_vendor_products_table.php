<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsPriceBuyDriverToOrderVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_vendor_products', 'is_price_buy_driver')) {
            Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->tinyInteger('is_price_buy_driver')->nullable()->default(0)->comment('0-No, 1-Yes');
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
