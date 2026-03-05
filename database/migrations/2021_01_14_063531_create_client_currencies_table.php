<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('client_currencies')) {
            Schema::create('client_currencies', function (Blueprint $table) {
            $table->string('client_code', 10)->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->tinyInteger('is_primary')->default(0)->comment('1 for yes, 0 for no');
            $table->decimal('doller_compare')->nullable();
            $table->timestamps();
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
