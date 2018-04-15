<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class Ordenes extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id', 'nombre_cliente', 'fecha', 'subtotal', 'iva', 'total', 'tipo_pago'];
    protected $dateFormat = 'Y-m-d H:i:s';
}
