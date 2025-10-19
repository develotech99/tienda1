<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('ven_id');
            
            $table->date('ven_fecha');
            $table->time('ven_hora');
            
            $table->string('ven_cliente', 200)->nullable();
            
            $table->decimal('ven_total', 12, 2);
            $table->decimal('ven_efectivo', 12, 2);
            $table->decimal('ven_cambio', 12, 2);
            
            
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('caja_id');
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('caja_id')->references('caja_id')->on('caja');
            
            $table->index('ven_fecha');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ventas');
    }
};