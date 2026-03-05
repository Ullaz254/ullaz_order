<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsServiceAreaForBanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'is_service_area_for_banners')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('is_service_area_for_banners')->default(0)->nullable()->comment('0-Inactive, 1-Active');
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
