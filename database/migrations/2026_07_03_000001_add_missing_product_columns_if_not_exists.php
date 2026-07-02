<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingProductColumnsIfNotExists extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'minimum_order_count')) {
                $table->integer('minimum_order_count')->default(1);
            }
            if (!Schema::hasColumn('products', 'batch_count')) {
                $table->integer('batch_count')->default(1);
            }
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'minimum_order_count')) {
                $table->dropColumn('minimum_order_count');
            }
            if (Schema::hasColumn('products', 'batch_count')) {
                $table->dropColumn('batch_count');
            }
        });
    }
}
