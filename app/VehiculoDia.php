<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VehiculoDia extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
            SELECT* FROM vehiculo_dias vd join errors er
                ON vd.id = er.id_error
            WHERE er.table = 'vehiculo_dias'
            AND er.solved = $solved
        ");
    }
}
