<?php

namespace App\Http\Controllers;

use \App\Role;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function manage ()
    {
        if(session('user') === null)
        return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['roles'] = Role::all();
        return view('panel.manage_roles', $data);
    }
}
