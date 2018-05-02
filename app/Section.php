<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    public static function check($id_role, $id_section){
        return \DB::table('role_sections')->where([
            ['id_role', $id_role],
            ['id_section', $id_section]
            ])->get()->count() != 0;
    }
}
