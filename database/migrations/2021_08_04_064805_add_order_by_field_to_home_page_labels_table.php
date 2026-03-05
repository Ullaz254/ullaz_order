<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderByFieldToHomePageLabelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('home_page_labels', 'order_by')) {
            Schema::table('home_page_labels', function (Blueprint $table) {
                $table->tinyInteger('order_by')->after('is_active')->nullable();
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
        Schema::table('home_page_labels', function (Blueprint $table) {
            if (Schema::hasColumn('home_page_labels', 'order_by')) {
                $table->dropColumn('order_by');
            }
        });
    }
}
