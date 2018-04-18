<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM empleados cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'empleados'
        AND er.solved = $solved
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM empleados cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'empleados'
        AND er.solved = $solved
        ");
    }
}
