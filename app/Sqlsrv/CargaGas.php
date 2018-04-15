<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class CargaGas extends Model
{
    protected $connection = 'sqlsrv';
    public $hasError = false;
    protected $fillable = ['id','nombre_trabajador','nombre_estacion','cantidad','precio_litro','total','fecha'];
}
