<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToCartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            if (!Schema::hasColumn('cart_products', 'schedule_type')) {
                $table->string('schedule_type')->nullable();
            }
            if (!Schema::hasColumn('cart_products', 'scheduled_date_time')) {
                $table->dateTimeTz('scheduled_date_time')->nullable();
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
        Schema::table('cart_products', function (Blueprint $table) {
            if (Schema::hasColumn('cart_products', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }
            if (Schema::hasColumn('cart_products', 'scheduled_date_time')) {
                $table->dropColumn('scheduled_date_time');
            }
        });
    }
}
