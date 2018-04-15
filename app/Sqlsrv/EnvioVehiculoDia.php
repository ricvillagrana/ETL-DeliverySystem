<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class EnvioVehiculoDia extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id_envio', 'id_vehiculo_dia'];
}
