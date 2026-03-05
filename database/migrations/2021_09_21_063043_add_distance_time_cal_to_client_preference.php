<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistanceTimeCalToClientPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'distance_unit_for_time')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('distance_unit_for_time', 50)->nullable();
            $table->unsignedInteger('distance_to_time_multiplier')->nullable()->default(0);
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
