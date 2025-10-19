<?php
// tu migración de caja - SIN el insert

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('caja', function (Blueprint $table) {
            $table->id('caja_id');
            $table->string('caja_nombre', 100)->default('Caja Principal');
            $table->decimal('caja_saldo', 12, 2)->default(0);
            $table->enum('caja_estado', ['Abierta', 'Cerrada'])->default('Cerrada');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('caja');
    }
};