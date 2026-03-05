<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientPreferenceTaxPriceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'tax_price_type')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->tinyInteger('tax_price_type')->default(0)->comment('0-Exclusive, 1-Inclusive');
                });
            } catch (\Exception $e) {
                // If row size error, log and skip
                \Log::warning('Could not add tax_price_type column due to row size limit: ' . $e->getMessage());
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
            if (Schema::hasColumn('client_preferences', 'tax_price_type')) {
                $table->dropColumn('tax_price_type');
            }
        });
    }
}
