<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterVendorsTableForfewFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        if (!Schema::hasColumn('vendors', 'email')) {
            Schema::table('vendors', function (Blueprint $table) {
            $table->string('email')->nullable()->after('address');
            $table->string('website')->nullable()->after('email');
            $table->string('phone_no')->nullable()->after('website');
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
