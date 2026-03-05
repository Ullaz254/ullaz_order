<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterApplePlaystoreLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
   {
       if (!Schema::hasColumn('client_preferences', 'android_app_link')) {
            Schema::table('client_preferences', function (Blueprint $table) {
           $table->mediumText('android_app_link')->nullable();
           $table->mediumText('ios_link')->nullable();
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
