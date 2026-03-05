<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tagsandtagtranslations extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tags')) {
            Schema::create('tags', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tag_translations')) {
            Schema::create('tag_translations', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->mediumText('slug')->nullable();
                $table->bigInteger('language_id')->unsigned();
                $table->bigInteger('tag_id')->unsigned();
                $table->timestamps();

                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
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
        Schema::dropIfExists('tags');
        Schema::dropIfExists('tag_translations');
    }
}
