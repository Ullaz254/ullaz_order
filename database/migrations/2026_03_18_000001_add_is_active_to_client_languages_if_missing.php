<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveToClientLanguagesIfMissing extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_languages') && !Schema::hasColumn('client_languages', 'is_active')) {
            Schema::table('client_languages', function (Blueprint $table) {
                $table->tinyInteger('is_active')->default(1)->after('is_primary')->comment('1 for yes, 0 for no');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_languages') && Schema::hasColumn('client_languages', 'is_active')) {
            Schema::table('client_languages', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
}
