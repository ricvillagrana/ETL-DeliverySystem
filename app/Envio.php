<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Envio extends Model
{
    public static function solved ($solved = true) 
    {
        return \DB::select("
            SELECT * FROM envios e join errors er
                ON e.id = er.id_error
            WHERE er.table = 'envios'
            AND er.solved = $solved
        ");
    }
}
