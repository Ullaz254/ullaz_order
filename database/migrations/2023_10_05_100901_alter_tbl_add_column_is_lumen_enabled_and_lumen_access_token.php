<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTblAddColumnIsLumenEnabledAndLumenAccessToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'is_lumen_enabled')) {
            Schema::table('clients', function (Blueprint $table) {
            $table->tinyInteger('is_lumen_enabled')->default(0);
            $table->string('lumen_access_token', 60)->nullable();
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
