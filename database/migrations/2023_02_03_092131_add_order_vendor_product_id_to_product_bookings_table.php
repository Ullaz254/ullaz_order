<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderVendorProductIdToProductBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_bookings', 'order_vendor_product_id')) {
            Schema::table('product_bookings', function (Blueprint $table) {
            $table->bigInteger('order_vendor_product_id')->unsigned()->nullable();

            $table->foreign('order_vendor_product_id')->references('id')->on('order_vendor_products')->onDelete('cascade');
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
