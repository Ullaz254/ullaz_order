<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddP2piconToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('client_preferences', 'p2picon')) {
            Schema::table('client_preferences', function (Blueprint $table) {
            $table->text('p2picon')->nullable()->after('laundryicon');
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
