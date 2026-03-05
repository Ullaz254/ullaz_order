<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderIdToProductBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_bookings', 'order_id')) {
            Schema::table('product_bookings', function (Blueprint $table) {
            $table->bigInteger('order_id')->unsigned()->nullable();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
