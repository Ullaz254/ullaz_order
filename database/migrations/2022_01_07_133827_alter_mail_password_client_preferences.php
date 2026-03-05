<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterMailPasswordClientPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'mail_password')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('mail_password', 100)->change();
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
