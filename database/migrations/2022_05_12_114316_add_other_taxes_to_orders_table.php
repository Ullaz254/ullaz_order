<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherTaxesToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('orders', 'total_other_taxes')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->text('total_other_taxes')->nullable()->comment('Other taxes like tax on fixed fee, service fee, container changes, delivery fee etc.');
            $table->integer('type')->default(0)->nullable()->comment('0=none, 1=cab book for friend')->change();
        // DB::statement("ALTER TABLE `orders` MODIFY `type` tinyint UNSIGNED NULL default(0) comment '0=none, 1=cab book for friend'");
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
