<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addlaundrykeysinpreftable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'need_laundry_service')) {
            Schema::table('client_preferences', function (Blueprint $table) {
         
            $table->tinyInteger('need_laundry_service')->default(0)->comment('0 - no, 1 - yes');
            $table->string('laundry_service_key')->nullable();
            $table->string('laundry_service_key_url')->nullable();
            $table->string('laundry_service_key_code')->nullable();
            $table->string('laundry_pickup_team')->nullable();
            $table->string('laundry_dropoff_team')->nullable();
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
