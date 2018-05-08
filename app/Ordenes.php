<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Ordenes extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM ordenes cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'ordenes'
        AND er.deleted = false
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM ordenes cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'ordenes'
        AND er.solved = $solved
        ");
    }
}
