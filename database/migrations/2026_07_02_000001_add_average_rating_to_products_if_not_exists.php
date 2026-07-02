<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAverageRatingToProductsIfNotExists extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('products', 'averageRating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('averageRating', 4, 2)->nullable()->after('has_variant');
                $table->index('averageRating');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('products', 'averageRating')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex(['averageRating']);
                $table->dropColumn('averageRating');
            });
        }
    }
}
