<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('banners')) {
            Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->longText('description')->nullable();
            $table->string('image', 150);
            $table->tinyInteger('validity_on')->default(1)->comment('1 - yes, 0 - no');
            $table->tinyInteger('sorting')->default(1);
            $table->tinyInteger('status')->default(1)->comment('1 - active, 0 - pending, 2 - blocked');
            $table->dateTime('start_date_time')->nullable();
            $table->dateTime('end_date_time')->nullable();
            $table->bigInteger('redirect_category_id')->unsigned()->nullable();
            $table->bigInteger('redirect_vendor_id')->unsigned()->nullable();
            $table->string('link')->nullable();
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
