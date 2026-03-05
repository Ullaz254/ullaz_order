<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(){
        // Don't drop table if it exists - just add missing column
        if (Schema::hasTable('pages')) {
            if (!Schema::hasColumn('pages', 'is_published')) {
                Schema::table('pages', function (Blueprint $table) {
                    // Add column without 'after' clause to avoid dependency issues
                    $table->tinyInteger('is_published')->default(0)->comment('0 draft and 1 for published');
                });
            }
        } else {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->mediumText('title');
                $table->mediumText('slug');
                $table->longText('description');
                $table->mediumText('meta_title')->nullable();
                $table->mediumText('meta_keyword')->nullable();;
                $table->mediumText('meta_description')->nullable();
                $table->tinyInteger('is_published')->default(0)->comment('0 draft and 1 for published');
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
        //
    }
}
