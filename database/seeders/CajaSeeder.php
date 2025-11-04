<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;  
use Illuminate\Database\Seeder;

class CajaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('caja')->insert([
            'caja_nombre' => 'Caja Principal',
            'caja_saldo' => 0,
            'caja_estado' => 'Abierta',
            'created_at' => now(),
            'updated_at'=> now()
        ]);
    }
}
