<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpiryDateToProductVariantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('product_variants', 'expiry_date')) {
            Schema::table('product_variants', function (Blueprint $table) {
            $table->date('expiry_date')->nullable();
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
