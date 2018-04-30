<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Privilege extends Model
{
    /**
     * of
     * 
     * @return Privileges
     */
     public static function of($id_user){
        return \DB::select('SELECT p.* FROM users u JOIN users_privileges up ON u.id = up.id_user JOIN privileges p ON p.id = up.id_privilege WHERE u.id = '.$id_user);
    }
    /**
     * check
     * 
     * @return boolean
     */
     public static function check($id_user, $id_privilege){
        return \DB::table('users_privileges')->where([
            ['id_user', $id_user],
            ['id_privilege', $id_privilege]
            ])->get()->count() != 0;
    }
    public static function checkName($id_user, $name){
        return \DB::table('users_privileges')->where([
            ['id_user', $id_user],
            ['id_privilege', (\App\Privilege::where('name', $name)->first())->id]
            ])->get()->count() != 0;
    }
}
