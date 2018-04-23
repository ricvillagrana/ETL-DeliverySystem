<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Misc extends Model
{
    public static function cast_float($number){
        return number_format((float)preg_replace('/[^A-Za-z0-9\.]/', '', $number), 2, '.', '');
    }

    public static function contains_number($str){
        if(strcspn($str, '0123456789') != strlen($str))
            return true;
        return false;
    }
    public static function delete_numbers($str){
        return preg_replace('/[0-9]+/', '', $str);
    }
}
