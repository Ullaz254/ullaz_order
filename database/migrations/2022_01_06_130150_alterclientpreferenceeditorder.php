<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterclientpreferenceeditorder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'is_edit_order_admin')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('is_edit_order_admin')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('is_edit_order_vendor')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('is_edit_order_driver')->nullable()->default(0)->comment('0-No, 1-Yes');
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
