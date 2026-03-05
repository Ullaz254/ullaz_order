<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterOrderVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_vendor_products', 'payable_amount')) {
            Schema::table('order_vendor_products', function (Blueprint $table) {
            DB::statement("ALTER TABLE `order_vendor_products` CHANGE `price` `price` decimal(8,2) NULL AFTER `image`");
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
