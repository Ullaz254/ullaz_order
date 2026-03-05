<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderVendorStatusOptionIdToOrderVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_vendor_products', 'order_vendor_status_option_id')) {
            Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->bigInteger('order_vendor_status_option_id')->unsigned()->nullable();
            $table->foreign('order_vendor_status_option_id')->references('id')->on('order_status_options')->onDelete('cascade');
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
