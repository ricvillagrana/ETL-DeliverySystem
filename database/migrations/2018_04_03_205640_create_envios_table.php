<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnviosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('envios', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('id_orden');
            $table->string('nombre_cliente');
            $table->string('firmado_por')->nullable();
            $table->date('fecha');
            $table->string('folio_factura');
            $table->string('estatus');
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
        Schema::dropIfExists('envios');
    }
}
