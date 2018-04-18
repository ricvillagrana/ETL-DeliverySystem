<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehiculoDia extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM vehiculo_dias cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'vehiculo_dias'
        AND er.solved = $solved
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM vehiculo_dias cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'vehiculo_dias'
        AND er.solved = $solved
        ");
    }
}
