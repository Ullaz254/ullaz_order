<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProductsTableForEnquireMod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        if (!Schema::hasColumn('products', 'inquiry_only')) {
            Schema::table('products', function (Blueprint $table) {
                // Check if averageRating exists, if not add column at the end
                if (Schema::hasColumn('products', 'averageRating')) {
                    $table->tinyInteger('inquiry_only')->nullable()->after('averageRating')->default(0);
                } else {
                    $table->tinyInteger('inquiry_only')->nullable()->default(0);
                }
            });
        }
        if (!Schema::hasColumn('client_preferences', 'enquire_mode')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->tinyInteger('enquire_mode')->nullable()->after('id')->default(0);
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
        //
    }
}
