<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_movimientos', function (Blueprint $table) {
            $table->id('mov_id');
            $table->unsignedBigInteger('prod_id');
            $table->enum('mov_tipo', ['Entrada', 'Salida', 'Ajuste'])->comment('Tipo de movimiento');
            $table->integer('mov_cantidad');
            $table->integer('mov_stock_anterior');
            $table->integer('mov_stock_nuevo');
            $table->string('mov_motivo', 200);
            $table->text('mov_observacion')->nullable();
            $table->timestamps();
            
            $table->foreign('prod_id')
                  ->references('prod_id')
                  ->on('productos')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_movimientos');
    }
};