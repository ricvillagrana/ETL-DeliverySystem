<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdenesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ordenes', function (Blueprint $table) {
            $table->integer('id')->unique();
            $table->string('nombre_cliente');
            $table->datetime('fecha');
            $table->float('subtotal', 12, 3);
            $table->float('iva', 12, 3);
            $table->float('total', 12, 3);
            $table->string('tipo_pago');
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
        Schema::dropIfExists('ordenes');
    }
}
