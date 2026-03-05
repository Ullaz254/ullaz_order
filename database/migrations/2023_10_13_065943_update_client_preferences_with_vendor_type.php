<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateClientPreferencesWithVendorType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'car_rental_check')) {
            Schema::table('client_preferences', function (Blueprint $table) {
        $table->tinyInteger('car_rental_check')->after('rental_check')->default(0)->comment('1 for yes, 0 for no');
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
