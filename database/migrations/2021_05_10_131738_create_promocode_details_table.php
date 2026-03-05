<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePromocodeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('promocodes', 'restriction_on')) {
            Schema::table('promocodes', function (Blueprint $table) {
            $table->tinyInteger('restriction_on')->default(0)->comment('0- product, 1-vendor')->nullable();
            $table->tinyInteger('restriction_type')->default(0)->comment('0- Include, 1-Exclude')->nullable();
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
