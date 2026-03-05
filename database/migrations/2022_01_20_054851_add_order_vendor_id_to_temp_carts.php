<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderVendorIdToTempCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('temp_carts', 'is_submitted')) {
            Schema::table('temp_carts', function (Blueprint $table) {
            $table->unsignedBigInteger('order_vendor_id')->nullable();
            $table->unsignedBigInteger('address_id')->nullable();
            $table->tinyInteger('is_submitted')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('is_approved')->nullable()->default(0)->comment('0-No, 1-Yes');
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
