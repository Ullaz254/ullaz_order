<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductTypeToClientPreference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'product_type')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->string('product_type', 255)->nullable()->comment('cab_booking')->after('id');
                });
            } catch (\Exception $e) {
                // If row size error, log and skip
                \Log::warning('Could not add product_type column due to row size limit: ' . $e->getMessage());
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
        Schema::table('client_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('client_preferences', 'product_type')) {
                $table->dropColumn('product_type');
            }
        });
    }
}
