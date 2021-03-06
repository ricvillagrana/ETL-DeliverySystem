<?php


Route::get('/debug', function(){
    \App\User::hasSection(session('user')->id, 'Carga gas');
});

Route::get('/generateXLS/{file_name}', 'UsersController@generateXLS');
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Using Models
use App\SourcesLocal;
use App\CargaGas;
use App\Envio;
use App\VehiculoDia;
use App\EnvioVehiculoDia;
use App\Devoluciones;
use App\Ordenes;
use App\Empleado;
use App\Etl;
use App\Error;
use App\Misc;
Use App\Sqlsrv;
use Illuminate\Http\Request;

// Navigation
Route::get('/', 'UsersController@login');
Route::get('/dashboard', 'UsersController@dashboard');

// Users
Route::get('/users', 'UsersController@manage');
Route::get('/user/{username}', 'UsersController@show');
Route::get('/register', 'UsersController@new');
Route::post('/auth', 'UsersController@auth');
Route::post('/create', 'UsersController@create');
Route::get('/logout', 'UsersController@logout');
Route::post('/user/delete/', function (Request $request){
    return \App\User::destroy($request->input('id'));
});

// User Roles
Route::get('/roles', 'RolesController@manage');
Route::post('/role/add', function(Request $request){
    return \App\Role::create(['name' => $request->input('name')]);
});
Route::post('/role/remove', function(Request $request){
    return \App\Role::destroy($request->input('id'));
});
Route::post('/role/change', function(Request $request){
    $user = \App\User::find($request->input('id_user'));
    $user->id_role = $request->input('id_role');
    $user->save();
});

// Roles Sections
Route::post('/role/section/add', function(Request $request){
    \DB::table('role_sections')->insert([
        'id_role'       => $request->input('id_role'),
        'id_section'    => $request->input('id_section')
    ]);
});
Route::post('/role/section/remove', function(Request $request){
    \DB::table('role_sections')->where([
        ['id_role',     $request->input('id_role')],
        ['id_section',  $request->input('id_section')]
    ])->delete();
});

// Users Privileges
Route::post('/user/privilege/add', function(Request $request){
    \DB::table('users_privileges')->insert([
        'id_user'       => $request->input('id_user'),
        'id_privilege'  => $request->input('id_privilege')
    ]);
});
Route::post('/user/privilege/remove', function(Request $request){
    \DB::table('users_privileges')->where([
        ['id_user',      $request->input('id_user')],
        ['id_privilege', $request->input('id_privilege')]
    ])->delete();
});

// ETL
Route::get('/etl', 'EtlController@etl');
Route::get('/etl/errors', 'EtlController@errors');
Route::get('/etl/begin', 'EtlController@begin');
Route::get('/etl/do/auto-fix', 'EtlController@autoFix');
Route::get('/etl/check', 'EtlController@check');
Route::post('/etl/check/change', function(Request $request){
    $table  = $request->input('table');
    $field  = $request->input('field');
    $data   = $request->input('data');
    $id     = $request->input('id');
    if(strpos($field, 'fecha') !== false || strpos($field, 'creado') !== false ){
        $date = new \DateTime($data);
        $data = $date->format('Y-m-d H:i:s');
    }else if(strpos($field, 'hora') !== false){
        $date = new \DateTime($data);
        $data = $date->format('H:i:s');
    }
    Error::where('id_error', $id)
            ->where('table', $table)
            ->update(['solved' => true]);
    //DB::select('UPDATE errors SET solved = 1 WHERE id_error = '.$id.' AND `table` = '.$table);
    return DB::select("UPDATE $table SET $field = '$data' WHERE id = $id");
});
Route::post('/etl/check/change_all', function(Request $request){
    $table  = $request->input('table');
    $field  = $request->input('field');
    $data   = $request->input('data');
    if(strpos($field, 'fecha') !== false || strpos($field, 'creado') !== false ){
        $date = new \DateTime($data);
        $data = $date->format('Y-m-d H:i:s');
    }else if(strpos($field, 'hora') !== false){
        $date = new \DateTime($data);
        $data = $date->format('H:i:s');
    }
    $res = null;
    foreach(Error::where('solved', '=', true)->get() as $error){
        $res = DB::select("UPDATE $table SET $field = '$data' WHERE id = $error->id_error");
        Error::where([
            ['table', $table],
            ['id_error', $error->id_error]
            ])->update(['solved' => true]);
    }
    return $res;
});
Route::post('/etl/check/delete', function(Request $request){
    $table  = $request->input('table');
    $id     = $request->input('id');
    $res = null;
    DB::select("UPDATE errors SET deleted = true WHERE id_error = $id AND `table` = '$table'");
    return DB::select("UPDATE $table SET deleted = true WHERE id = $id");
});
Route::post('/etl/check/send', function(Request $request){
    $table  = $request->input('table');
    $id     = $request->input('id');

    if($table == 'carga_gas'){
        $row = CargaGas::find($id)->toArray();
        Sqlsrv\CargaGas::create($row);
        CargaGas::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'vehiculo_dias'){
        $row = VehiculoDia::find($id)->toArray();
        Sqlsrv\VehiculoDia::create($row);
        VehiculoDia::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'envio_vehiculo_dias'){
        $row = EnvioVehiculoDia::find($id)->toArray();
        Sqlsrv\EnvioVehiculoDia::create($row);
        EnvioVehiculoDia::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'envios'){
        $row = Envio::find($id)->toArray();
        Sqlsrv\Envio::create($row);
        Envio::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'devoluciones'){
        $row = Devoluciones::find($id)->toArray();
        Sqlsrv\Devoluciones::create($row);
        Devoluciones::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'ordenes'){
        $row = Ordenes::find($id)->toArray();
        Sqlsrv\Ordenes::create($row);
        Ordenes::where('id', $row['id'])->update(['deleted' => true]);
    }
    if($table == 'empleados'){
        $row = Empleado::find($id)->toArray();
        Sqlsrv\Empleado::create($row);
        Empleado::where('id', $row['id'])->update(['deleted' => true]);
    }

    DB::select("UPDATE errors SET deleted = true WHERE id_error = $id AND `table` = '$table'");
    
});
Route::get('/etl/check/send-all', function(){
    
    $carga_gas = CargaGas::solvedClean();
    foreach($carga_gas as $row){
        if(Sqlsrv\CargaGas::find($row->id) === null)
            Sqlsrv\CargaGas::create((array)$row);
        CargaGas::where('id', $row->id)->update(['deleted' => true]);
    }
    $vehiculo_dias = VehiculoDia::solvedClean();
    foreach($vehiculo_dias as $row){
        if(Sqlsrv\VehiculoDia::find($row->id) === null)
            Sqlsrv\VehiculoDia::create((array)$row);
        VehiculoDia::where('id', $row->id)->update(['deleted' => true]);
    }
    $envio_vehiculo_dias = EnvioVehiculoDia::solvedClean();
    foreach($envio_vehiculo_dias as $row){
        if(Sqlsrv\EnvioVehiculoDia::find($row->id) === null)
            Sqlsrv\EnvioVehiculoDia::create((array)$row);
        EnvioVehiculoDia::where('id', $row->id)->update(['deleted' => true]);
    }
    $envios = Envio::solvedClean();
    foreach($envios as $row){
        if(Sqlsrv\Envio::find($row->id) === null)
            Sqlsrv\Envio::create((array)$row);
        Envio::where('id', $row->id)->update(['deleted' => true]);
    }
    $ordenes = Ordenes::solvedClean();
    foreach($ordenes as $row){
        if(Sqlsrv\Ordenes::find($row->id) === null)
            Sqlsrv\Ordenes::create((array)$row);
        Ordenes::where('id', $row->id)->update(['deleted' => true]);
    }
    $empleados = Empleado::solvedClean();
    foreach($empleados as $row){
        if(Sqlsrv\Empleado::find($row->id) === null)
            Sqlsrv\Empleado::create((array)$row);
        Empleado::where('id', $row->id)->update(['deleted' => true]);
    }
    $devoluciones = Devoluciones::solvedClean();
    foreach($devoluciones as $row){
        if(Sqlsrv\Devoluciones::find($row->id) === null)
            Sqlsrv\Devoluciones::create((array)$row);
        Devoluciones::where('id', $row->id)->update(['deleted' => true]);
    }
    Error::where('solved', true)->update(['deleted' => true]);
    
});
// APIs
Route::get('/dwh/envios', 'EnviosController@index');
Route::get('/dwh/vehiculo-dia', 'VehiculoDiaController@index');
Route::get('/dwh/envio-vehiculo-dia', 'EnvioVehiculoDiaController@index');
Route::get('/dwh/carga-gas', 'CargaGasController@index');
Route::get('/dwh/devoluciones', 'DevolucionesController@index');
Route::get('/dwh/ordenes', 'OrdenesController@index');
Route::get('/dwh/conductores', 'ConductoresController@index');


/**
* Cleaning tables
*/
Route::get('/etl/clean', function () {
    if(session('user') != null):
        App\CargaGas::truncate();
        App\Envio::truncate();
        App\VehiculoDia::truncate();
        App\EnvioVehiculoDia::truncate();
        App\Devoluciones::truncate();
        App\Ordenes::truncate();
        App\Empleado::truncate();
        App\Sqlsrv\CargaGas::truncate();
        App\Sqlsrv\Envio::truncate();
        App\Sqlsrv\VehiculoDia::truncate();
        App\Sqlsrv\EnvioVehiculoDia::truncate();
        App\Sqlsrv\Devoluciones::truncate();
        App\Sqlsrv\Ordenes::truncate();
        App\Sqlsrv\Empleado::truncate();
        App\Etl::truncate();
        App\Error::truncate();
        Session::forget('errors');
        return 'done';
    else:
        return 'failed';
    endif;
});

/**
* Begin ETL process
*/

Route::get('/etl/do/carga_gas', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Carga gas')) return "Access denied";

    $error = false;
    $carga_gas = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'cargagas')->first()->url));
    foreach($carga_gas as $carga):
        $error = false;
        if(Misc::contains_number($carga->nombre_trabajador)):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'nombre_trabajador',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $carga->nombre_trabajador,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($carga->nombre_trabajador)
            ]);
        endif;
        if((float)$carga->cantidad < 0):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'cantidad',
                'comment'   => 'La cantidad no puede ser menor a 0.',
                'original'  => $carga->cantidad,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(Misc::cast_float($carga->precio_litro) < 0):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'precio_litro',
                'comment'   => 'El precio no puede ser menor a 0.',
                'original'  => Misc::cast_float($carga->precio_litro),
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(Misc::cast_float($carga->total) != Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad))):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'total',
                'comment'   => 'El total debe ser (Precio_litro * cantidad), se sugiere que sea '.Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad)),
                'original'  => Misc::cast_float($carga->total),
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad))
            ]);
        endif;
        if(strtotime($carga->fecha_carga) > time()):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'fecha',
                'comment'   => 'No se puede tener una fecha futura.',
                'original'  => $carga->fech,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if($carga->folio_factura == null || $carga->folio_factura == '""'):
            $error = true;
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'folio_factura',
                'comment'   => 'No existe un folio de la factura.',
                'original'  => $carga->folio_factura,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
            $c                      = new CargaGas;
            $c->id                  = $carga->id_carga;
            $c->nombre_trabajador   = $carga->nombre_trabajador;
            $c->nombre_estacion     = $carga->nombre_estacion;
            $c->cantidad            = $carga->cantidad;
            $c->precio_litro        = Misc::cast_float($carga->precio_litro);
            $c->total               = Misc::cast_float($carga->total);
            $c->folio_factura       = $carga->folio_factura;
            $c->fecha               = date_format(date_create($carga->fecha_carga), "Y/m/d H:i:s");
            if($error):
                $c->etl = session('id_etl');
                $c->save();
            else:   
                Sqlsrv\CargaGas::create($c->toArray());
            endif;
    endforeach;
});
Route::get('/etl/do/envios', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Envíos')) return "Access denied";

    $error = false;
    $envios = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'envios')->first()->url));
    foreach($envios as $envio):
        $error = false;
        if(Misc::contains_number($envio->firmado_por)):
            $error = true;
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'firmado_por',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $envio->firmado_por,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($envio->firmado_por)
            ]);
        endif;
        if(Misc::contains_number($envio->nombre_cliente)):
            $error = true;
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $envio->nombre_cliente,
                'etl'       => session('id_etl'),
                'auto_fix'  => str_replaceMisc::delete_numbers($envio->nombre_cliente)
            ]);
        endif;
        if($envio->folio_factura == null || $envio->folio_factura == "''"):
            $error = true;
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'folio_factura',
                'comment'   => 'No existe un folio de factura.',
                'original'  => $envio->folio_factura,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(strtotime($envio->creado_en) > time() ):
            $error = true;
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha futura.',
                'original'  => $envio->creado_en,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
            $e                  = new Envio;
            $e->id              = $envio->id_envio;
            $e->id_orden        = $envio->id_orden;
            $e->nombre_cliente  = $envio->nombre_cliente;
            $e->firmado_por     = $envio->firmado_por;
            $e->folio_factura   = $envio->folio_factura;
            $e->fecha           = $envio->creado_en;
            $e->estatus         = $envio->estatus;
            if($error):
                $e->etl             = session('id_etl');
                $e->save();
            else:
                Sqlsrv\Envio::create($e->toArray());
            endif;
    endforeach;
});
Route::get('/etl/do/vehiculo_dias', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Vehículo Día')) return "Access denied";

    $error;
    $vehiculoDias = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'vehiculodia')->first()->url));
    foreach($vehiculoDias as $vehiculoDia):
        $error = false;
        if($vehiculoDia->gas_consumida < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_consumida',
                'comment'   => 'La gasolina que se consumió no puede ser negativa.',
                'original'  => $vehiculoDia->gas_consumida,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
        if($vehiculoDia->km_recorridos < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_recorridos',
                'comment'   => 'La kilómetros que se recorrieron no pueden ser negativos (a menos que hayas ido de reversa).',
                'original'  => $vehiculoDia->km_recorridos,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
        if($vehiculoDia->gas_inicial < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_inicial',
                'comment'   => 'La gasolina no puede ser negativa.',
                'original'  => $vehiculoDia->gas_inicial,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
        if($vehiculoDia->gas_final < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_final',
                'comment'   => 'La gasolina no puede ser negativa.',
                'original'  => $vehiculoDia->gas_final,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
        if($vehiculoDia->km_inicial > $vehiculoDia->km_final):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_final',
                'comment'   => 'No puedes terminar con un kilometraje menor al inicial.',
                'original'  => $vehiculoDia->km_final,
                'etl'       => session('id_etl'),
                'auto_fix'  => $vehiculoDia->km_final
            ]);
        endif;
        if($vehiculoDia->km_inicial < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_inicial',
                'comment'   => 'No puedes tener kilometraje negativo',
                'original'  => $vehiculoDia->km_inicial,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
        if($vehiculoDia->km_final < 0):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_final',
                'comment'   => 'No puedes tener kilometraje negativo',
                'original'  => $vehiculoDia->km_final,
                'etl'       => session('id_etl'),
                'auto_fix'  => $vehiculoDia->km_final
            ]);
        endif;
        if(strtotime($vehiculoDia->fecha_dia) > time() ):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'fecha',
                'comment'   => 'No se puede tener una fecha futura.',
                'original'  => $vehiculoDia->fecha,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if($vehiculoDia->hora_inicio == null):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'hora_inicio',
                'comment'   => 'La hora de inicio no puede estar vacía.',
                'original'  => $vehiculoDia->hora_inicio,
                'etl'       => session('id_etl'),
                'auto_fix'  => '09:00am'
            ]);
        endif;
        if($vehiculoDia->hora_fin == null):
            $error = true;
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'hora_fin',
                'comment'   => 'La hora de finalización no puede estar vacía.',
                'original'  => $vehiculoDia->hora_fin,
                'etl'       => session('id_etl'),
                'auto_fix'  => '05:00pm'
            ]);
        endif;
            $vd                     = new VehiculoDia;
            $vd->id                 = $vehiculoDia->id_vehiculo_dia;
            $vd->nombre_trabajador  = $vehiculoDia->nombre_trabajador;
            $vd->fecha              = $vehiculoDia->fecha_dia;
            $vd->gas_inicial        = $vehiculoDia->gas_inicial;
            $vd->gas_final          = $vehiculoDia->gas_final;
            $vd->km_inicial         = $vehiculoDia->km_inicial;
            $vd->km_final           = $vehiculoDia->km_final;
            $vd->hora_inicio        = $vehiculoDia->hora_inicio;
            $vd->hora_fin           = $vehiculoDia->hora_fin;
            $vd->gas_consumida      = $vehiculoDia->gas_consumida;
            $vd->km_recorridos      = $vehiculoDia->km_recorridos;
            if($error):
                $vd->etl                 = session('id_etl');
                $vd->save();
            else:
                Sqlsrv\VehiculoDia::create($vd->toArray());
            endif;
    endforeach;
});
Route::get('/etl/do/envio_vehiculo_dias', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Vehículo Día')) return "Access denied";

    $envioVehiculoDias  = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'enviovehiculodia'  )->first()->url));
    if(sizeof($envioVehiculoDias) == (Envio::all()->count() + Sqlsrv\Envio::all()->count())){return "update_failed";}
    foreach($envioVehiculoDias as $envioVehiculoDia):
        // Bruh, could not find errors here, they are foreign keys!
            $evd                    = new EnvioVehiculoDia;
            $evd->id_envio          = $envioVehiculoDia->id_envio;
            $evd->id_vehiculo_dia   = $envioVehiculoDia->id_vehiculo_dia;
            Sqlsrv\EnvioVehiculoDia::create($evd->toArray());
    endforeach;
});
Route::get('/etl/do/devoluciones', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Devoluciones')) return "Access denied";

    $error;
    $devoluciones = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'devoluciones'      )->first()->url));
    foreach($devoluciones as $devolucion):
        $error = false;
        if(Misc::contains_number($devolucion->nombre_cliente)):
            $error = true;
            Error::create([
                'table'     => 'devoluciones',
                'id_error'  => $devolucion->id_devolucion,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $devolucion->nombre_cliente,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($devolucion->nombre_cliente)
            ]);
        endif;
        if($devolucion->cantidad < 0):
            $error = true;
            Error::create([
                'table'     => 'devoluciones',
                'id_error'  => $devolucion->id_devolucion,
                'field'     => 'cantidad',
                'comment'   => 'No puede ser una cantidad negativa.',
                'original'  => $devolucion->cantidad,
                'etl'       => session('id_etl'),
                'auto_fix'  => '0'
            ]);
        endif;
            $d                  = new Devoluciones;
            $d->id              = $devolucion->id_devolucion;
            $d->id_orden         = $devolucion->id_orden;
            $d->id_prenda       = $devolucion->id_producto;
            $d->nombre_cliente  = $devolucion->nombre_cliente;
            $d->razon           = $devolucion->razon;
            $d->cantidad        = $devolucion->cantidad;
            if($error):
                $d->etl = session('id_etl');
                $d->save();
            else:
                Sqlsrv\Devoluciones::create($d->toArray());
            endif;
    endforeach;
});
Route::get('/etl/do/ordenes', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Órdenes')) return "Access denied";

    $error;
    $ordenes = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'ordenes')->first()->url));
    foreach($ordenes as $orden):
        $error = false;
        if(Misc::contains_number($orden->nombre_cliente)):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $orden->nombre_cliente,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($orden->nombre_cliente)
            ]);
        endif;
        if(strtotime($orden->creado_en) > time() ):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha futura.',
                'original'  => $orden->creado_en,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(Misc::cast_float($orden->iva) != Misc::cast_float(Misc::cast_float($orden->subtotal) * 0.16)):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'iva',
                'comment'   => 'El IVA no corresponde al 16% del subtotal.',
                'original'  => $orden->iva,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::cast_float(Misc::cast_float($orden->subtotal) * 0.16)
            ]);
        endif;
        if(Misc::cast_float($orden->total) != Misc::cast_float((Misc::cast_float(Misc::cast_float($orden->subtotal) + Misc::cast_float($orden->iva))))):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'total',
                'comment'   => 'El total no corresponde a la suma del subtotal y el IVA.',
                'original'  => $orden->total,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::cast_float(Misc::cast_float($orden->subtotal) + Misc::cast_float($orden->iva))
            ]);
        endif;
        if(Misc::cast_float($orden->subtotal) < 0):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'subtotal',
                'comment'   => 'El subtotal no puede ser negativo',
                'original'  => $orden->subtotal,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::cast_float($orden->subtotal) * -1
            ]);
        endif;
        if(Misc::cast_float($orden->total) < 0):
            $error = true;
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'total',
                'comment'   => 'El total no puede ser negativo',
                'original'  => $orden->total,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::cast_float($orden->total) * -1
            ]);
        endif;
        if(Misc::cast_float($orden->iva) < 0):
        $error = true;
        Error::create([
            'table'     => 'ordenes',
            'id_error'  => $orden->id_orden,
            'field'     => 'iva',
            'comment'   => 'El IVA no puede ser negativo',
            'original'  => $orden->iva,
            'etl'       => session('id_etl'),
            'auto_fix'  => Misc::cast_float($orden->iva) * -1
        ]);
        endif;
            $date = new DateTime($orden->creado_en);
            $o                  = new Ordenes;
            $o->id              = $orden->id_orden;
            $o->nombre_cliente  = $orden->nombre_cliente;
            $o->fecha           = $date->format('Y-m-d H:i:s');
            $o->subtotal        = Misc::cast_float($orden->subtotal);
            $o->iva             = Misc::cast_float($orden->iva);
            $o->total           = Misc::cast_float($orden->total);
            $o->tipo_pago       = $orden->tipo_pago;
            if($error):
                $o->etl = session('id_etl');
                $o->save();
            else:
                Sqlsrv\Ordenes::create($o->toArray());
            endif;
    endforeach;
});
Route::get('/etl/do/conductores', function(){
    if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
    if(!\App\User::hasSection(session('user')->id, 'Empleados')) return "Access denied";

    $error;
    $conductores = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'conductores')->first()->url));
    foreach($conductores as $conductor):
        $error = false;
        if(Misc::contains_number($conductor->nombre)):
            $error = true;
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'nombre',
                'comment'   => 'Un nombre no puede contener números.',
                'original'  => $conductor->nombre,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($conductor->nombre)
            ]);
        endif;
        if(Misc::contains_number($conductor->apellido)):
            $error = true;
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'apellido',
                'comment'   => 'Un apellido no puede contener números.',
                'original'  => $conductor->apellido,
                'etl'       => session('id_etl'),
                'auto_fix'  => Misc::delete_numbers($conductor->apellido)
            ]);
        endif;
        if(strlen($conductor->rfc) != 13):
            $error = true;
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'rfc',
                'comment'   => 'Un RFC debe contener 13 caracteres, 10 del RFC y 3 de clave única.',
                'original'  => $conductor->rfc,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(strtotime($conductor->creado_en) > time() ):
            $error = true;
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha de inicio de labores futura.',
                'original'  => $conductor->creado_en,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
        if(strtotime($conductor->fecha_nac) > time() ):
            $error = true;
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'fecha_nac',
                'comment'   => 'El empleado debiera haber nacido ya, la fecha hace referencia al futuro.',
                'original'  => $conductor->fecha_nac,
                'etl'       => session('id_etl'),
                'auto_fix'  => ''
            ]);
        endif;
            $e                      = new Empleado;
            $e->id                  = $conductor->id_conductor;
            $e->nombre              = $conductor->nombre;
            $e->apellido_paterno    = $conductor->apellido;
            $e->apellido_materno    = '';
            $e->telefono            = '';
            $e->correo              = '';
            $e->rfc                 = $conductor->rfc;
            $e->domicilio           = '';
            $e->municipio           = '';
            $e->estado              = '';
            $e->fecha_inicio        = $conductor->creado_en;
            $e->fecha_nac           = $conductor->fecha_nac;
            if($error):
                $e->etl = session('id_etl');
                $e->save();
            else:
                Sqlsrv\Empleado::create($e->toArray());
            endif;
    endforeach;
});
