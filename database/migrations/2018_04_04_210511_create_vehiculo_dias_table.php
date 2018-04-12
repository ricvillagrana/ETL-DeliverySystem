<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiculoDiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehiculo_dias', function (Blueprint $table) {
            $table->integer('id');
            $table->string('nombre_trabajador');
            $table->date('fecha');
            $table->float('gas_inicial', 12, 3);
            $table->float('gas_final', 12, 3);
            $table->float('km_inicial', 12, 3);
            $table->float('km_final', 12, 3);
            $table->time('hora_inicio');
            $table->time('hora_fin')->nullable();
            $table->float('gas_consumida', 12, 3);
            $table->float('km_recorridos', 12, 3);
            $table->timestamps();
            $table->integer('etl');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehiculo_dias');
    }
}
