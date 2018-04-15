<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class Devoluciones extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id','id_orden', 'id_prenda', 'nombre_cliente', 'razon', 'cantidad'];
}
