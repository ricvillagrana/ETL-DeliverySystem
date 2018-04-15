<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id','id_orden','nombre_cliente','firmado_por','fecha','folio_factura','estatus'];
    protected $dateFormat = 'Y-m-d H:i:s';
}
