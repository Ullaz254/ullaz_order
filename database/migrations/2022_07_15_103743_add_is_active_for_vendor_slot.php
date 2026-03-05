<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveForVendorSlot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('service_areas', 'is_active_for_vendor_slot')) {
            Schema::table('service_areas', function (Blueprint $table) {
            $table->tinyInteger('is_active_for_vendor_slot')->nullable()->default(0)->comments('0=Inactive, 1=Active');
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
