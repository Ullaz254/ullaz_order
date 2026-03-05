<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeTypeOfCostPriceInProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_variants', 'cost_price')) {
            Schema::table('product_variants', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 4)->nullable()->change();
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
