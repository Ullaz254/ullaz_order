<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSubscriptionPlansUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('subscription_plans_user', 'type_id')) {
            Schema::table('subscription_plans_user', function (Blueprint $table) {
            $table->integer('type_id')->nullable()->after('sort_order');
            $table->integer('order_limit')->nullable()->after('type_id');
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
