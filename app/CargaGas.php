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
            AND er.solved = $solved
        ");
    }
}
