<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewthemeIcontoPrefernecesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'deliveryicon')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('deliveryicon')->nullable();
            $table->string('dineinicon')->nullable();
            $table->string('takewayicon')->nullable();
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
