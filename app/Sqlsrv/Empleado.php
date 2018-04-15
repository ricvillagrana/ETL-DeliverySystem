<?php

namespace App\Sqlsrv;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $connection = 'sqlsrv';
    protected $fillable = ['id', 'nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'rfc', 'domicilio', 'municipio', 'estado', 'fecha_inicio', 'fecha_nac'];
}
