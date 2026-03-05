<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToLoyaltyCards extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('loyalty_cards', 'image')) {
            Schema::table('loyalty_cards', function (Blueprint $table) {
                $table->string('image')->nullable();
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
        Schema::table('loyalty_cards', function (Blueprint $table) {
            if (Schema::hasColumn('loyalty_cards', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
}
