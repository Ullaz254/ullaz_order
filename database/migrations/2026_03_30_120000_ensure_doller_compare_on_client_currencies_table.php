<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Hostinger / imported DBs may be missing doller_compare or use dollar_compare.
 * Application code expects doller_compare (legacy spelling) on client_currencies.
 */
class EnsureDollerCompareOnClientCurrenciesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('client_currencies')) {
            return;
        }

        if (Schema::hasColumn('client_currencies', 'doller_compare')) {
            return;
        }

        if (Schema::hasColumn('client_currencies', 'dollar_compare')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                DB::statement(
                    'ALTER TABLE client_currencies CHANGE dollar_compare doller_compare DECIMAL(8,8) NULL'
                );
            }

            return;
        }

        Schema::table('client_currencies', function (Blueprint $table) {
            $table->decimal('doller_compare', 8, 8)->nullable();
        });
    }

    public function down(): void
    {
        // Non-destructive: do not drop business-critical columns on rollback.
    }
}
