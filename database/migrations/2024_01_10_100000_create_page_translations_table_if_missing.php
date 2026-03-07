<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageTranslationsTableIfMissing extends Migration
{
    /**
     * Run the migrations.
     * Creates page_translations if missing (no migration in codebase actually creates it).
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('page_translations')) {
            return;
        }

        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id');
            $table->unsignedBigInteger('language_id');
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->tinyInteger('is_published')->default(0);
            $table->tinyInteger('type_of_form')->default(0)->comment('0 for none; 1 for vendor registration; 2 for driver registration;');
            $table->timestamps();
        });

        Schema::table('page_translations', function (Blueprint $table) {
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_translations');
    }
}
