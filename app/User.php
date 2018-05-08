<?php

namespace App;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * Auth method
     * 
     * @return boolean
     */
    public static function auth(User $user){
        if($userLogged = \DB::select('SELECT * FROM users WHERE (email = "'.$user->email.'" OR username = "'.$user->email.'") AND password = "'.$user->password.'"'))
        {
            session(['user' => (Object)$userLogged[0]]);
            return true;
        }
        return false;
    }

    public static function getRole ($id) {
        $name = \DB::select('SELECT r.name FROM roles r join users u on u.id_role = r.id WHERE u.id = '.$id);
        if($name != null)
            return ($name[0])->name;
        return "Ninguno";
    }

    public static function getRoleSections ($id) {
        $sections = \DB::select('SELECT s.name FROM roles r JOIN users u ON u.id_role = r.id JOIN role_sections rs ON rs.id_role = r.id JOIN sections s ON s.id = rs.id_section WHERE u.id = '.$id);
        return $sections;
    }
    public static function hasSection ($id_user, $section_name) {
        $sections = \DB::select('SELECT count(s.name) as num FROM roles r JOIN users u ON u.id_role = r.id JOIN role_sections rs ON rs.id_role = r.id JOIN sections s ON s.id = rs.id_section WHERE u.id = '.$id_user.' AND s.name = "'.$section_name.'"');
        return (($sections)[0]->num >= 1);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
}
