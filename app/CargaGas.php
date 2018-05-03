<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CargaGas extends Model
{
    public $hasError = false;
    protected $fillable = [];
    public static function solved ($solved = true) 
    {
        return \DB::select("
        SELECT * FROM carga_gas cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'carga_gas'
        AND er.deleted = false
        ");
    }
    public static function solvedClean ($solved = true) 
    {
        return \DB::select("
        SELECT cg.* FROM carga_gas cg join errors er
        ON cg.id = er.id_error
        WHERE er.table = 'carga_gas'
        AND er.solved = $solved
        ");
    }
}
