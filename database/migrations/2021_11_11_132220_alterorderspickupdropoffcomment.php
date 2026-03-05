<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterorderspickupdropoffcomment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'comment_for_pickup_driver')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->mediumText('comment_for_pickup_driver')->nullable();
            $table->mediumText('comment_for_dropoff_driver')->nullable();
            $table->mediumText('comment_for_vendor')->nullable();
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
