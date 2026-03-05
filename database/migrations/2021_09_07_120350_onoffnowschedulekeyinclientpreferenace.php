<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Onoffnowschedulekeyinclientpreferenace extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if the column exists before renaming
        if (Schema::hasColumn('client_preferences', 'auto_accept_order')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->renameColumn('auto_accept_order', 'off_scheduling_at_cart');
            });
        } else {
            // If the column doesn't exist, just add the new column
            if (!Schema::hasColumn('client_preferences', 'off_scheduling_at_cart')) {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->tinyInteger('off_scheduling_at_cart')->nullable()->default(0)->comment('0-No, 1-Yes');
                });
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
        if (Schema::hasColumn('client_preferences', 'off_scheduling_at_cart')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                if (Schema::hasColumn('client_preferences', 'auto_accept_order')) {
                    $table->renameColumn('off_scheduling_at_cart', 'auto_accept_order');
                } else {
                    $table->dropColumn('off_scheduling_at_cart');
                }
            });
        }
    }
}
