<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addpickupdeliverykeysinclientprefereance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'pickup_delivery_service_key')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('pickup_delivery_service_key')->nullable();
            $table->string('pickup_delivery_service_key_url')->nullable();
            $table->string('pickup_delivery_service_key_code')->nullable();
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
