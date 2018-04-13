<?php

namespace App\Http\Controllers;

use App\Error;
use Illuminate\Http\Request;

class EtlController extends Controller
{
    public function etl (Request $request) 
    {
        if(session('user') === null)
            return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['user'] = session('user');
        return view('panel.etl', $data);
    }
    public function begin () 
    {
        if(session('user') != null):
            $etl = new \App\Etl;
            $etl->id_user = session('user')->id;
            $etl->save();
            session(['id_etl' => $etl->id]);
        else:
            return redirect('/')->with('error', 'Debes iniciar sesión.');
        endif;
    }
    public function errors () {
        if(session('user') === null)
            return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['user'] = session('user');
        $data['errors'] = Error::all();
        $data['error_quantity'] = Error::all()->count();
        return view('panel.errors', $data);
    }
}
