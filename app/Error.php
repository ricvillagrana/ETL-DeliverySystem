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
    public static function from ($id_user) 
    {
        return (\DB::select('SELECT e.* FROM 
        errors e join etls t 
            on e.etl = t.id 
        join users u 
            on t.id_user = u.id 
        where u.id = '.$id_user.'
        AND e.deleted = true ORDER BY id DESC'));
    }
}
