<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TblUserDatVaultAddFieldIsDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_data_vaults') && !Schema::hasColumn('user_data_vaults', 'is_default')) {
            Schema::table('user_data_vaults', function (Blueprint $table) {
                $table->tinyInteger('is_default')->default(0)->comment('0-No, 1-Yes');
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
        Schema::table('user_data_vaults', function (Blueprint $table) {
            if (Schema::hasColumn('user_data_vaults', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
}
