<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScheduledDateTimeToCarts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('carts', 'schedule_type')) {
            Schema::table('carts', function (Blueprint $table) {
                $table->string('schedule_type')->after('currency_id')->nullable();
                $table->dateTimeTz('scheduled_date_time')->after('schedule_type')->nullable();
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
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasColumn('carts', 'schedule_type')) {
                $table->dropColumn('schedule_type');
            }
            if (Schema::hasColumn('carts', 'scheduled_date_time')) {
                $table->dropColumn('scheduled_date_time');
            }
        });
    }
}
