<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Devoluciones extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
            SELECT * FROM devoluciones d join errors er
                ON d.id = er.id_error
            WHERE er.table = 'devoluciones'
            AND er.solved = $solved
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM devoluciones cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'devoluciones'
        AND er.solved = $solved
        ");
    }
}
