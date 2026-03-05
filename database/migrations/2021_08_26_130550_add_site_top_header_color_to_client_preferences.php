<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSiteTopHeaderColorToClientPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'site_top_header_color')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->string('site_top_header_color')->nullable();
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
            if (Schema::hasColumn('client_preferences', 'site_top_header_color')) {
                $table->dropColumn('site_top_header_color');
            }
        });
    }
}
