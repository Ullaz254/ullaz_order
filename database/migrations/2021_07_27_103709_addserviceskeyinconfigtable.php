<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addserviceskeyinconfigtable extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'need_dispacher_home_other_service')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->string('need_dispacher_home_other_service')->nullable();
            });
        }
        if (!Schema::hasColumn('client_preferences', 'dispacher_home_other_service_key')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->string('dispacher_home_other_service_key')->nullable();
            });
        }
        if (!Schema::hasColumn('client_preferences', 'dispacher_home_other_service_key_url')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->string('dispacher_home_other_service_key_url')->nullable();
            });
        }
        if (!Schema::hasColumn('client_preferences', 'dispacher_home_other_service_key_code')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->string('dispacher_home_other_service_key_code')->nullable();
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
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('need_dispacher_home_other_service');
            $table->dropColumn('dispacher_home_other_service_key');
            $table->dropColumn('dispacher_home_other_service_key_url');
            $table->dropColumn('dispacher_home_other_service_key_code');
        });
    }
}
