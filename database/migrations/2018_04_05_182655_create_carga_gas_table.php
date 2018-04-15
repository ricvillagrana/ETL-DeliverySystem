<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCargaGasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carga_gas', function (Blueprint $table) {
            $table->integer('id')->unique();
            $table->string('nombre_trabajador');
            $table->string('nombre_estacion');
            $table->float('cantidad', 12, 3);
            $table->float('precio_litro', 12, 3);
            $table->float('total', 12, 3);
            $table->datetime('fecha');
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
        Schema::dropIfExists('carga_gas');
    }
}
