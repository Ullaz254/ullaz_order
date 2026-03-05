<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDriverRegistrationDocumentTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('driver_registration_document_translations', 'slug')) {
            Schema::table('driver_registration_document_translations', function (Blueprint $table) {
                $table->mediumText('slug')->after('name');
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
        Schema::table('driver_registration_document_translations', function (Blueprint $table) {
            if (Schema::hasColumn('driver_registration_document_translations', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
}
