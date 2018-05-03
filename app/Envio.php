<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM envios cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'envios'
        AND er.solved = $solved
        AND er.deleted = false
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM envios cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'envios'
        AND er.solved = $solved
        ");
    }

}
