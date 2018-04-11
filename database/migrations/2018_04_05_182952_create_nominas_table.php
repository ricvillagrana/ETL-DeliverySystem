<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nominas', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('id_empleado');
            $table->boolean('estado');
            $table->date('fecha_inicial');
            $table->date('fecha_final');
            $table->float('sueldo_base', 12, 3);
            $table->float('salario_diario', 12, 3);
            $table->float('horas_extra', 12, 3);
            $table->integer('dias_aguinaldo');
            $table->integer('dias_festivos');
            $table->float('bonos_produccion', 12, 3);
            $table->float('reparto_utilidades', 12, 3);
            $table->float('total', 12, 3);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nominas');
    }
}
