<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripSubscriberIdColumnInSubscriptionInvoicesUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('subscription_invoices_user', 'strip_subscriber_id')) {
            Schema::table('subscription_invoices_user', function (Blueprint $table) {
            $table->string('strip_subscriber_id')->nullable();
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
