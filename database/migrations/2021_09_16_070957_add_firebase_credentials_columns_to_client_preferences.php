<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFirebaseCredentialsColumnsToClientPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'fcm_server_key')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('fcm_server_key',512)->default(Null)->nullable();
            $table->string('fcm_api_key')->default(Null)->nullable();
            $table->string('fcm_auth_domain')->default(Null)->nullable();
            $table->string('fcm_project_id')->default(Null)->nullable();
            $table->string('fcm_storage_bucket')->default(Null)->nullable();
            $table->string('fcm_messaging_sender_id')->default(Null)->nullable();
            $table->string('fcm_app_id')->default(Null)->nullable();
            $table->string('fcm_measurement_id')->default(Null)->nullable();
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
