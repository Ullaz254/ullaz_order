<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToOrderVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_vendor_products', 'order_status_option_id')) {
            Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->tinyInteger('order_status_option_id')->nullable();
            $table->tinyInteger('dispatcher_status_option_id')->nullable();
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
