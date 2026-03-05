<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToCabBookingLayoutBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('cab_booking_layout_banners', 'type')) {
            Schema::table('cab_booking_layout_banners', function (Blueprint $table) {
            $table->tinyInteger('type')->default(2)->comment("1-Web, 2-App");
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
