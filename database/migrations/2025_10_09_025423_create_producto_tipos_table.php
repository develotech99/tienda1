<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('producto_tipo', function (Blueprint $table) {
            $table->id('tprod_id');
            $table->string('tprod_nombre', 100);
            $table->text('tprod_desc')->nullable();
            $table->tinyInteger(column: 'tprod_situacion')->default(1)->comment('1=Activo, 0=Inactivo');
            $table->index('tprod_nombre');
            $table->index('tprod_situacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto_tipo');
    }
};
