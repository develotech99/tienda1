<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->enum('ven_estado', ['Activa', 'Anulada'])->default('Activa');
            $table->unsignedBigInteger('user_anulacion_id')->nullable();
            $table->timestamp('fecha_anulacion')->nullable();
            $table->text('motivo_anulacion')->nullable();
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['ven_estado', 'user_anulacion_id', 'fecha_anulacion', 'motivo_anulacion']);
        });
    }
};