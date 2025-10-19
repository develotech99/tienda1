<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id('vendet_id');
            
            $table->unsignedBigInteger('ven_id');
            $table->unsignedBigInteger('prod_id');
            
            $table->integer('vendet_cantidad');
            $table->decimal('vendet_precio', 10, 2);
            $table->decimal('vendet_total', 10, 2);
            
            $table->timestamps();
            
            $table->foreign('ven_id')->references('ven_id')->on('ventas')->onDelete('cascade');
            $table->foreign('prod_id')->references('prod_id')->on('productos');
            
            $table->index('ven_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('venta_detalles');
    }
};