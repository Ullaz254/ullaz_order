<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterdefaultvalueofminimumordercount extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only change if column exists
        if (Schema::hasColumn('products', 'minimum_order_count')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->integer('minimum_order_count')->default(1)->change();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not change minimum_order_count column: ' . $e->getMessage());
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
        if (Schema::hasColumn('products', 'minimum_order_count')) {
            try {
                Schema::table('products', function (Blueprint $table) {
                    $table->integer('minimum_order_count')->nullable()->change();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not reverse minimum_order_count column: ' . $e->getMessage());
            }
        }
    }
}
