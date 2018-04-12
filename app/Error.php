<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Error extends Model
{
    protected $fillable = ['table', 'id_error', 'field', 'comment', 'solved', 'etl'];
}
