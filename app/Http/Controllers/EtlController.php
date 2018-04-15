<?php

namespace App\Http\Controllers;

use App\Error;
use Illuminate\Http\Request;

class EtlController extends Controller
{
    public function etl (Request $request) 
    {
        if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
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
        if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['user'] = session('user');
        $data['errors'] = Error::all();
        $data['error_quantity'] = Error::all()->count();
        return view('panel.errors', $data);
    }
    
    public function autoCorrect () 
    {
        if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
        // Get errors
        $errors['vehiculo_dias']    = Error::where('table', 'vehiculo_dias')->get();
        $errors['carga_gas']        = Error::where('table', 'caga_gas')->get();
        $errors['devoluciones']     = Error::where('table', 'devoluciones')->get();
        
        $traitment['vehiculo_dias'] = [];
        foreach($errors['vehiculo_dias'] as $error){
            if(!isset($traitment['vehiculo_dias'][$error->id_error]))
                $traitment['vehiculo_dias'][$error->id_error] = \App\VehiculoDia::find($error->id_error);
            if(is_array($traitment['vehiculo_dias'][$error->id_error]['field'])){
                $traitment['vehiculo_dias'][$error->id_error]['field']   = array_merge($traitment['vehiculo_dias'][$error->id_error]['field'], [$error->field]);
                $traitment['vehiculo_dias'][$error->id_error]['comment'] = array_merge($traitment['vehiculo_dias'][$error->id_error]['comment'], [$error->comment]);
            }else{
                $traitment['vehiculo_dias'][$error->id_error]['field']      = [$error->field];
                $traitment['vehiculo_dias'][$error->id_error]['comment']    = [$error->comment];
            }
        }
        return json_encode($traitment);
    }
}
