<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Ensures service_areas has a polygon (GEOMETRY) column so ST_Contains works.
 * Safe to run multiple times; adds column only if missing.
 */
class EnsureServiceAreasPolygonColumn extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('service_areas')) {
            return;
        }

        $hasColumn = Schema::hasColumn('service_areas', 'polygon');
        if (!$hasColumn) {
            $col = DB::selectOne("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_areas' AND LOWER(COLUMN_NAME) = 'polygon'");
            if ($col) {
                return;
            }
        } else {
            return;
        }

        DB::statement('ALTER TABLE `service_areas` ADD COLUMN `polygon` GEOMETRY NULL AFTER `zoom_level`');
    }

    public function down()
    {
        if (!Schema::hasTable('service_areas') || !Schema::hasColumn('service_areas', 'polygon')) {
            return;
        }
        DB::statement('ALTER TABLE `service_areas` DROP COLUMN `polygon`');
    }
}
