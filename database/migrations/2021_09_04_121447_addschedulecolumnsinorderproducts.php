<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addschedulecolumnsinorderproducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_vendor_products', 'schedule_type')) {
                $table->string('schedule_type')->nullable();
            }
            if (!Schema::hasColumn('order_vendor_products', 'scheduled_date_time')) {
                $table->dateTime('scheduled_date_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            if (Schema::hasColumn('order_vendor_products', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }
            if (Schema::hasColumn('order_vendor_products', 'scheduled_date_time')) {
                $table->dropColumn('scheduled_date_time');
            }
        });
    }
}
