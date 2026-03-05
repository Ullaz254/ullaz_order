<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatcherToOrderLongTermServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('order_long_term_service_schedules', 'web_hook_code')) {
            Schema::table('order_long_term_service_schedules', function (Blueprint $table) {
            $table->string('web_hook_code')->nullable()->comment('dispatcher weweb_hook_codev  dispatch');
            $table->string('dispatch_traking_url')->nullable()->comment('product dispatch');
            $table->string('dispatcher_status_option_id')->nullable()->comment('product dispatch');
            $table->string('order_status_option_id')->nullable()->comment('product dispatch');
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
