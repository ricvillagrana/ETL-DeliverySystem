<?php

namespace App\Http\Controllers;

use App\User;
use App\Sources;
use App\SourcesLocal;
use App\CargaGas;
use App\Envio;
use App\VehiculoDia;
use App\EnvioVehiculoDia;
use App\Devoluciones;
use App\Ordenes;
use App\Empleado;
use App\Sqlsrv;
use App\Etl;
use App\Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class UsersController extends Controller
{
    /**
     * Login
     * 
     * @return  Login view
     */
    public function login () { 
        if(session('user') != null)
            return redirect('/dashboard');
        return view('login');
    }
    /**
     * Auth
     * 
     * @return Panel view or Error
     */
    public function auth (Request $request) 
    { 
        $user = new User;
        $user->email = $request->input('email');
        $user->password = sha1(md5($request->input('password')));
        if(User::auth($user))
        {
            return redirect('/dashboard');
        }
        else
            return redirect('/')->with('error', 'No se pudo iniciar sesión.');
    }
    /**
     * LogOut
     * 
     * $return view Login
     * 
     */
    public function logout (Request $request) 
    {
        $request->session()->forget('user');
        return redirect('/'); 
    }
    /**
     * Dashboard
     * 
     * @return view Dashboard
     */
    public function dashboard (Request $request) 
    {
        if(session('user') === null)
            return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['user'] = session('user');
        $data['sources']                                = SourcesLocal::orderBy('database', 'ASC')->get();
        $data['count']['envios']                        = Sqlsrv\Envio::all()->count();
        $data['count']['vehiculo_dia']                  = Sqlsrv\VehiculoDia::all()->count();
        $data['count']['envio_vehiculo_dia']            = Sqlsrv\EnvioVehiculoDia::all()->count();
        $data['count']['carga_gas']                     = Sqlsrv\CargaGas::all()->count();
        $data['count']['ordenes']                       = Sqlsrv\Ordenes::all()->count();
        $data['count']['devoluciones']                  = Sqlsrv\Devoluciones::all()->count();
        $data['count']['conductores']                   = Sqlsrv\Empleado::all()->count();
        $data['count']['error_envios']                  = Envio::all()->count();//Error::where('table', 'envios')->count();
        $data['count']['error_vehiculo_dia']            = VehiculoDia::all()->count();//Error::where('table', 'vehiculo_dias')->count();
        $data['count']['error_envio_vehiculo_dia']      = EnvioVehiculoDia::all()->count();//Error::where('table', 'envio_vehiculo_dias')->count();
        $data['count']['error_carga_gas']               = CargaGas::all()->count();//Error::where('table', 'carga_gas')->count();
        $data['count']['error_ordenes']                 = Ordenes::all()->count();//Error::where('table', 'ordenes')->count();
        $data['count']['error_devoluciones']            = Devoluciones::all()->count();//Error::where('table', 'devoluciones')->count();
        $data['count']['error_conductores']             = Empleado::all()->count();//Error::where('table', 'conductores')->count();
        return view('panel.dashboard', $data);
    }
    /**
     * ETL
     * 
     * @return view ETL
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function new()
    {
        return view('register');
    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if($request->password != $request->password_confirm)
            return redirect('/register');
        $user = new User;
        $user->name     = $request->input('name');
        $user->email    = $request->input('email');
        $user->password = sha1(md5($request->input('password')));
        if($user->save()){
            User::auth($user);
            return redirect('/dashboard');
        }else{
            return redirect('/register')->with('error', 'No se pudo crear el usuario.');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
