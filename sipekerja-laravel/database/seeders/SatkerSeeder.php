<?php

namespace Database\Seeders;

use App\Models\Satker;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SatkerSeeder extends Seeder
{
    public function run(): void
    {
        // Create provinsi satker
        $provinsi = Satker::firstOrCreate(
            ['type' => 'provinsi'],
            [
                'id'        => Str::uuid(),
                'name'      => 'BPS Provinsi Sulawesi Tengah',
                'type'      => 'provinsi',
                'kode'      => 'PROV',
                'is_active' => true,
            ]
        );

        // Assign all existing users without satker_id to provinsi
        User::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);

        // Assign all existing teams without satker_id to provinsi
        Team::whereNull('satker_id')->update(['satker_id' => $provinsi->id]);

        $this->command->info("Satker provinsi dibuat: {$provinsi->name} ({$provinsi->id})");
        $this->command->info("Semua user dan tim existing di-assign ke satker provinsi.");
    }
}
