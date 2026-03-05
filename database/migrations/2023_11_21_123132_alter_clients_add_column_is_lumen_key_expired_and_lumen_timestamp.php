<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterClientsAddColumnIsLumenKeyExpiredAndLumenTimestamp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'is_lumen_key_expired')) {
            Schema::table('clients', function (Blueprint $table) {
            $table->dateTime('lumen_timestamp')->nullable();
            $table->tinyInteger('is_lumen_key_expired')->default(0);
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
