<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Etl extends Model
{
    public static function totalRowsDWH () 
    {
        $rows = 0;
        $rows += \App\Sqlsrv\CargaGas::all()->count();
        $rows += \App\Sqlsrv\Devoluciones::all()->count();
        $rows += \App\Sqlsrv\Empleado::all()->count();
        $rows += \App\Sqlsrv\Envio::all()->count();
        $rows += \App\Sqlsrv\Ordenes::all()->count();
        $rows += \App\Sqlsrv\VehiculoDia::all()->count();
        return $rows;
    }
}
