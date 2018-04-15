<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class VehiculoDia extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id', 'nombre_trabajador', 'fecha', 'gas_inicial', 'gas_final', 'km_inicial', 'km_final', 'hora_inicio', 'hora_fin', 'gas_consumida', 'km_recorridos',];
}
