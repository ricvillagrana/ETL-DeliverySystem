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
        if($userLogged = User::where('email', $user->email)->where('password', $user->password)->first())
        {
            session(['user' => $userLogged]);
            return true;
        }
        return false;
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
