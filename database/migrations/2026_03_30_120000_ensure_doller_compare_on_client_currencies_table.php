<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Production DBs imported from older dumps may lack doller_compare while
 * application code always selects it (ClientPreference::currency()).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_currencies')) {
            return;
        }

        if (! Schema::hasColumn('client_currencies', 'doller_compare')) {
            Schema::table('client_currencies', function (Blueprint $table) {
                $table->decimal('doller_compare', 14, 8)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_currencies')) {
            return;
        }

        if (Schema::hasColumn('client_currencies', 'doller_compare')) {
            Schema::table('client_currencies', function (Blueprint $table) {
                $table->dropColumn('doller_compare');
            });
        }
    }
};
