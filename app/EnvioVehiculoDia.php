<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EnvioVehiculoDia extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM envio_vehiculo_dias cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'envio_vehiculo_dias'
        AND er.deleted = false
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM envio_vehiculo_dias cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'envio_vehiculo_dias'
        AND er.solved = $solved
        ");
    }
}
