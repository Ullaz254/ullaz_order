<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsWishlistToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'show_wishlist')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->tinyInteger('show_wishlist')->after('enquire_mode')->default(0);
            });
        }
        if (!Schema::hasColumn('client_preferences', 'show_icons')) {
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->tinyInteger('show_icons')->after('enquire_mode')->default(0);
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
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('show_wishlist');
            $table->dropColumn('show_icons');
        });
    }
}
