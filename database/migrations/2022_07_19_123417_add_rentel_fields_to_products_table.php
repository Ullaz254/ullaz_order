<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRentelFieldsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('products', 'minimum_duration')) {
            Schema::table('products', function (Blueprint $table) {
            //
            $table->string('minimum_duration')->nullable();
            $table->string('additional_increments')->nullable();
            $table->string('buffer_time_duration')->nullable();
            $table->tinyInteger('is_fix_check_in_time')->nullable()->default(0);
            $table->string('check_in_time')->nullable();
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
