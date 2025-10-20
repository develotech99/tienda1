<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Modificar el ENUM de la tabla caja_movimientos
        DB::statement("ALTER TABLE caja_movimientos MODIFY COLUMN cajamov_tipo ENUM('Apertura', 'Cierre', 'Venta', 'Ingreso', 'Egreso') NOT NULL");
    }

    public function down()
    {
        // Revertir a los valores originales
        DB::statement("ALTER TABLE caja_movimientos MODIFY COLUMN cajamov_tipo ENUM('Apertura', 'Cierre', 'Venta') NOT NULL");
        
        // Opcional: eliminar registros con los nuevos tipos si existen
        DB::table('caja_movimientos')
            ->whereIn('cajamov_tipo', ['Ingreso', 'Egreso'])
            ->delete();
    }
};