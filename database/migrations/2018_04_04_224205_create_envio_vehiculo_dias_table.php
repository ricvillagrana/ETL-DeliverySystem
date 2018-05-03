<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnvioVehiculoDiasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('envio_vehiculo_dias', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_envio');
            $table->integer('id_vehiculo_dia');
            $table->timestamps();
            $table->integer('etl');
            $table->boolean('deleted')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('envio_vehiculo_dias');
    }
}
