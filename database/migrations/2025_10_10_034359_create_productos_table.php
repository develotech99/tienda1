<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id('prod_id');
            $table->string('prod_codigo', 50)->unique()->comment('Código de barras o código único');
            $table->string('prod_nombre', 200);
            $table->text('prod_descripcion')->nullable();
            $table->decimal('prod_precio_compra', 10, 2)->default(0);
            $table->decimal('prod_precio_venta', 10, 2)->default(0);
            $table->integer('prod_stock_minimo')->default(5);
            $table->integer('prod_stock_actual')->default(0);
            $table->string('prod_imagen')->nullable();
            $table->unsignedBigInteger('tprod_id')->comment('Tipo de producto');
            $table->enum('prod_situacion', ['Activo', 'Inactivo'])->default('Activo');
            $table->timestamps();
            
            $table->foreign('tprod_id')
                  ->references('tprod_id')
                  ->on('producto_tipo')
                  ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productos');
    }
};