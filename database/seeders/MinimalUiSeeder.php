<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Inserts the minimum rows required for the store front (home page) to load
 * when clients, client_preferences, languages, or client_languages are empty.
 * Safe to run multiple times: only inserts when each table has no rows.
 */
class MinimalUiSeeder extends Seeder
{
    public const CLIENT_CODE = 'drivarr';

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $this->seedLanguage();
        $this->seedClient();
        $this->seedClientPreference();
        $this->seedClientLanguage();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function seedLanguage(): void
    {
        if (DB::table('languages')->exists()) {
            return;
        }
        DB::table('languages')->insert([
            'id'         => 1,
            'sort_code'  => 'en',
            'name'       => 'English',
            'nativeName' => 'English',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedClient(): void
    {
        if (DB::table('clients')->exists()) {
            return;
        }
        DB::table('clients')->insert([
            'id'           => 1,
            'name'         => 'Drivarr Client',
            'email'        => 'admin@drivarr.com',
            'phone_number' => null,
            'password'     => Hash::make('password'),
            'code'         => self::CLIENT_CODE,
            'is_deleted'   => 0,
            'is_blocked'   => 0,
            'status'      => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function seedClientPreference(): void
    {
        if (DB::table('client_preferences')->exists()) {
            return;
        }
        $now = now();
        $row = [
            'id'                => 1,
            'client_code'       => self::CLIENT_CODE,
            'is_hyperlocal'     => 0,
            'Default_latitude'  => 28.5355,
            'Default_longitude' => 77.3910,
            'delivery_check'    => 1,
            'dinein_check'      => 0,
            'takeaway_check'    => 0,
            'rental_check'      => 0,
            'pick_drop_check'   => 0,
            'on_demand_check'   => 0,
            'laundry_check'     => 0,
            'appointment_check' => 0,
            'p2p_check'         => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];
        if ($this->hasColumn('client_preferences', 'car_rental_check')) {
            $row['car_rental_check'] = 0;
        }
        DB::table('client_preferences')->insert($row);
    }

    private function seedClientLanguage(): void
    {
        if (DB::table('client_languages')->exists()) {
            return;
        }
        $languageId = DB::table('languages')->min('id') ?? 1;
        DB::table('client_languages')->insert([
            'client_code'  => self::CLIENT_CODE,
            'language_id'  => $languageId,
            'is_primary'   => 1,
            'is_active'    => 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function hasColumn(string $table, string $column): bool
    {
        return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
    }
}
