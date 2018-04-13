<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Misc extends Model
{
    public static function cast_float($number){
        return number_format((float)preg_replace('/[^A-Za-z0-9\.]/', '', $number), 2, '.', '');
    }
}
