<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressIsCarToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'address_is_car')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->tinyInteger('address_is_car')->nullable()->default(0)->comment('0-No, 1-Yes');
                });
            } catch (\Exception $e) {
                // If row size error, log and skip
                \Log::warning('Could not add address_is_car column due to row size limit: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('client_preferences', 'address_is_car')) {
                $table->dropColumn('address_is_car');
            }
        });
    }
}
