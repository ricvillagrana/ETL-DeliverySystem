<?php

namespace App;

use App\Error;
use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    protected $fillable = ['table', 'id_error', 'field', 'comment', 'solved', 'auto_fix', 'etl'];

    public static function solved () 
    {
        return Error::where('solved', '=', true)->get();
    }
}
