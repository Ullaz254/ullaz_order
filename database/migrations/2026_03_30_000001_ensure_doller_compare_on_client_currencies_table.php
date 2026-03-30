<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hostinger / restored DBs may omit columns present in migrations.
 * Application code expects doller_compare on client_currencies (legacy spelling).
 */
class EnsureDollerCompareOnClientCurrenciesTable extends Migration
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

        DB::table('client_currencies')->whereNull('doller_compare')->update(['doller_compare' => 1]);
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
}
