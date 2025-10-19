<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id('cajamov_id');

            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ven_id')->nullable();
            
            $table->enum('cajamov_tipo', ['Apertura', 'Cierre', 'Venta']);
            $table->decimal('cajamov_monto', 12, 2);
            $table->decimal('cajamov_saldo_final', 12, 2);
            
            $table->string('cajamov_desc', 200);
            
            $table->timestamps();
            
            $table->foreign('caja_id')->references('caja_id')->on('caja');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('ven_id')->references('ven_id')->on('ventas');
            
            $table->index('cajamov_tipo');
        });
    }

    public function down()
    {
        Schema::dropIfExists('caja_movimientos');
    }
};