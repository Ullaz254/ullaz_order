<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookARideFieldsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'type')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->comment('0=none, 1=cab book for friend');
            $table->string('friend_name')->nullable();
            $table->string('friend_phone_number')->nullable();
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
