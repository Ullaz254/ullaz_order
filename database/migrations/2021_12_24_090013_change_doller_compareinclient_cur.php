<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDollerCompareinclientCur extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only change if column exists
        if (Schema::hasColumn('client_currencies', 'doller_compare')) {
            try {
                Schema::table('client_currencies', function (Blueprint $table) {
                    $table->decimal('doller_compare', 8, 7)->nullable()->change();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not change doller_compare column: ' . $e->getMessage());
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
        if (Schema::hasColumn('client_currencies', 'doller_compare')) {
            try {
                Schema::table('client_currencies', function (Blueprint $table) {
                    $table->decimal('doller_compare', 8, 2)->nullable()->change();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not reverse doller_compare column: ' . $e->getMessage());
            }
        }
    }
}
