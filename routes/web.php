<?php

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

// Navigation
Route::get('/', 'UsersController@login');
Route::get('/register', 'UsersController@new');
Route::get('/dashboard', 'UsersController@dashboard');
Route::get('/etl', 'UsersController@etl');
// User
Route::post('/auth', 'UsersController@auth');
Route::post('/create', 'UsersController@create');
Route::get('/logout', 'UsersController@logout');
// APIs
Route::get('/dwh/envios', 'EnviosController@index');
Route::get('/dwh/vehiculo-dia', 'VehiculoDiaController@index');
Route::get('/dwh/envio-vehiculo-dia', 'EnvioVehiculoDiaController@index');
Route::get('/dwh/carga-gas', 'CargaGasController@index');
Route::get('/dwh/devoluciones', 'DevolucionesController@index');
Route::get('/dwh/ordenes', 'OrdenesController@index');
Route::get('/dwh/conductores', 'ConductoresController@index');
// ETL Process
Route::get('etl/begin', function () {
    // Defines variables from APIs Interface
    $carga_gas = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'cargagas')->first()->url));
    $envios = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'envios')->first()->url));
    $vehiculoDias = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'vehiculodia')->first()->url));
    
    
    // Begins process
    /**
    * Carga ETL
    */
    foreach($carga_gas as $carga):
        $everythingOk = true;
        $errorIn = [];
        if($carga->cantidad > 0):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['cantidad' => 'La cantidad no puede se menor o igual a 0.']);
        endif;
        if($carga->precio_litro > 0):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['precio_litro' => 'El precio no puede ser menor o igual a 0.']);
        endif;
        if($carga->total != $carga->precio_litro * $carga->cantidad):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['total' => 'El total debe ser (Precio_litro * cantidad), se sugiere que sea '.($carga->precio_litro * $carga->cantidad) ]);
        endif;
        if(strtotime($carga->fecha) > strtotime(time()) ):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['fecha' => 'No se puede tener una fecha futura.' ]);
        endif;
        if($everythingOk):
            $c                  = new CargaGas;
            $c->id              = $carga->id_carga;
            $c->cantidad        = $carga->cantidad;
            $c->precio_litro    = $carga->precio_litro;
            $c->total           = $carga->total;
            $c->fecha           = $carga->fecha;
        else:
            $errors['carga_gas'] = [];
            $errors['carga_gas'] = array_merge($errors['carga_gas'], [$carga->id_carga => $errorIn]);
        endif;
    endforeach;
    /**
     * Envios
     */
    foreach($envios as $envio):
        $everythingOk = true;
        $errorIn = [];
        if($envio->folio_factura == null || $envio->folio_factura == ""):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['folio_factura' => 'No existe un folio de factura.']);
        endif;
        if(strtotime($envio->creado_en) > strtotime(time()) ):
            $everythingOk = false;
            $errorIn = array_merge($errorIn, ['fecha' => 'No se puede tener una fecha futura.']);
        endif;
        if($everythingOk):
            $e                  = new Envio;
            $e->id              = $envio->id_envio;
            $e->id_orden        = $envio->id_orden;
            $e->entregado       = $envio->entregado == 1 ? true : false;
            $e->folio_factura   = $envio->folio_factura;
            $e->fecha           = $envio->creado_en;
        else:
            $errors['envios'] = [];
            $errors['envios'] = array_merge($errors['envios'], [$envio->id_envio => $errorIn]);
        endif;
    endforeach;
    /**
     * Vehículo Día
     */
    foreach($viculoDias as $vehiculoDia):
        $everythingOk = true;
        $errorIn = [];
        
        if($everythingOk):
            $vd                 = new VhiculoDia;
            $vd->id             = $vehiculoDia->id_vehiculo_dia;
            $vd->id_trabajador  = $vehiculoDia->id_conductor;
            $vd->fecha          = $vehiculoDia->fecha_dia;
            $vd->gas_inical     = $vehiculoDia->gas_inical;
            $vd->gas_final      = $vehiculoDia->gas_final;
            $vd->km_inicial     = $vehiculoDia->km_inicial;
            $vd->km_final       = $vehiculoDia->km_final;
            $vd->hora_inicio    = $vehiculoDia->hora_inicio;
            $vd->hora_fin       = $vehiculoDia->hora_fin;
        else:
            $errors['envios'] = [];
            $errors['envios'] = array_merge($errors['envios'], [$vehiculoDia->id_vehiculo_dia => $errorIn]);
        endif;
    endforeach;
});