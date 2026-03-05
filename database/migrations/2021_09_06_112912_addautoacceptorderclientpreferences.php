<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Addautoacceptorderclientpreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Skip if column already exists to avoid row size error
        if (!Schema::hasColumn('client_preferences', 'auto_accept_order')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->tinyInteger('auto_accept_order')->nullable()->default(0)->comment('0-No, 1-Yes');
                });
            } catch (\Exception $e) {
                // If row size error, log and skip
                \Log::warning('Could not add auto_accept_order column due to row size limit: ' . $e->getMessage());
            }
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
            if (Schema::hasColumn('client_preferences', 'auto_accept_order')) {
                $table->dropColumn('auto_accept_order');
            }
        });
    }
}
