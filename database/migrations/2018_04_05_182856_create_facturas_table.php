<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('id_cliente');
            $table->datetime('fecha');
            $table->text('descripcion');
            $table->float('importe', 12, 3);
            $table->float('descuento', 12, 3);
            $table->float('subtotal', 12, 3);
            $table->float('iva', 12, 3);
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
        Schema::dropIfExists('facturas');
    }
}
