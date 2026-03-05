<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBrandCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // If table doesn't exist, skip
        if (!Schema::hasTable('brand_categories')) {
            return;
        }

        // If id column already exists, just ensure it's the right type
        if (Schema::hasColumn('brand_categories', 'id')) {
            // Column exists, skip adding it
            return;
        } else {
            // Only add if it doesn't exist
            try {
                Schema::table('brand_categories', function (Blueprint $table) {
                    $table->bigIncrements('id')->first();
                });
            } catch (\Exception $e) {
                \Log::warning('Could not add id column to brand_categories: ' . $e->getMessage());
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
        if (Schema::hasTable('brand_categories') && Schema::hasColumn('brand_categories', 'id')) {
            try {
                Schema::table('brand_categories', function (Blueprint $table) {
                    $table->dropColumn('id');
                });
            } catch (\Exception $e) {
                \Log::warning('Could not drop id column from brand_categories: ' . $e->getMessage());
            }
        }
    }
}
