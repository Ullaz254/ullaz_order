<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorFcmServerKeyToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'vendor_fcm_server_key')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->string('vendor_fcm_server_key', 512)->nullable();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not add vendor_fcm_server_key column: ' . $e->getMessage());
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
            if (Schema::hasColumn('client_preferences', 'vendor_fcm_server_key')) {
                $table->dropColumn('vendor_fcm_server_key');
            }
        });
    }
}
