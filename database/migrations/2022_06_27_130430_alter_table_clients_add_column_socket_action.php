<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientsAddColumnSocketAction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('clients', 'admin_chat')) {
            Schema::table('clients', function (Blueprint $table) {
            $table->integer('admin_chat')->default(2);
            $table->integer('driver_chat')->default(2);
            $table->integer('customer_chat')->default(2);
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
