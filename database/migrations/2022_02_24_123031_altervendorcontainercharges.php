<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Altervendorcontainercharges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        if (!Schema::hasColumn('vendors', 'need_container_charges')) {
            Schema::table('vendors', function (Blueprint $table) {
            $table->tinyInteger('need_container_charges')->nullable()->default(0)->comment('0-No, 1-Yes');
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
