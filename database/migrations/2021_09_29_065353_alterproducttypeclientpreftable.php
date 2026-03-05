<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterproducttypeclientpreftable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if product_type exists and rename it
        if (Schema::hasColumn('client_preferences', 'product_type') && 
            !Schema::hasColumn('client_preferences', 'business_type')) {
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->renameColumn('product_type', 'business_type');
                });
            } catch (\Exception $e) {
                \Log::warning('Could not rename product_type column: ' . $e->getMessage());
            }
        } elseif (!Schema::hasColumn('client_preferences', 'business_type')) {
            // If product_type doesn't exist, just add business_type
            try {
                Schema::table('client_preferences', function (Blueprint $table) {
                    $table->string('business_type', 255)->nullable()->comment('cab_booking')->after('id');
                });
            } catch (\Exception $e) {
                \Log::warning('Could not add business_type column: ' . $e->getMessage());
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
        if (Schema::hasColumn('client_preferences', 'business_type')) {
            try {
                if (Schema::hasColumn('client_preferences', 'product_type')) {
                    Schema::table('client_preferences', function (Blueprint $table) {
                        $table->dropColumn('business_type');
                    });
                } else {
                    Schema::table('client_preferences', function (Blueprint $table) {
                        $table->renameColumn('business_type', 'product_type');
                    });
                }
            } catch (\Exception $e) {
                \Log::warning('Could not reverse business_type column: ' . $e->getMessage());
            }
        }
    }
}
