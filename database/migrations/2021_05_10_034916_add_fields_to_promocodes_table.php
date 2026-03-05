<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPromocodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('promocode_restrictions', 'vendor_id')) {
            Schema::table('promocode_restrictions', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
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
