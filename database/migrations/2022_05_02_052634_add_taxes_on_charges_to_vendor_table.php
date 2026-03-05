<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxesOnChargesToVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('vendors', 'service_charges_tax')) {
            Schema::table('vendors', function (Blueprint $table) {
            $table->tinyInteger('service_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->tinyInteger('delivery_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->tinyInteger('container_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->unsignedBigInteger('service_charges_tax_id')->default(0);
            $table->unsignedBigInteger('delivery_charges_tax_id')->default(0);
            $table->unsignedBigInteger('container_charges_tax_id')->default(0);
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
