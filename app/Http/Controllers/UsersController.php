<?php

namespace App\Http\Controllers;

use App\User;
use App\Sources;
use App\SourcesLocal;
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
        $data['sources'] = SourcesLocal::orderBy('database', 'ASC')->get();
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
