<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatcherStatusToVendorOrderProductStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('vendor_order_product_statuses', 'dispatcher_status_option_id')) {
            Schema::table('vendor_order_product_statuses', function (Blueprint $table) {
            $table->bigInteger('dispatcher_status_option_id')->unsigned()->nullable()->afert('order_status_option_id');
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
