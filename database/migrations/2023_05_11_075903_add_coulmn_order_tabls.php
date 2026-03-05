<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoulmnOrderTabls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'old_payable_amount')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->decimal('old_payable_amount',10,2)->default(0);
            $table->decimal('total_waiting_time',10,2)->default(0);
            $table->decimal('total_waiting_price',10,2)->default(0);
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
