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
// ETL
Route::get('/etl', 'EtlController@etl');
Route::get('/etl/errors', 'EtlController@errors');
Route::get('/etl/begin', 'EtlController@begin');
// User
Route::post('/auth', 'UsersController@auth');
Route::post('/create', 'UsersController@create');
Route::get('/logout', 'UsersController@logout');
// APIs
Route::get('/fuentes-datos/envios', 'EnviosController@index');
Route::get('/fuentes-datos/vehiculo-dia', 'VehiculoDiaController@index');
Route::get('/fuentes-datos/envio-vehiculo-dia', 'EnvioVehiculoDiaController@index');
Route::get('/fuentes-datos/carga-gas', 'CargaGasController@index');
Route::get('/fuentes-datos/devoluciones', 'DevolucionesController@index');
Route::get('/fuentes-datos/ordenes', 'OrdenesController@index');
Route::get('/fuentes-datos/conductores', 'ConductoresController@index');


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

Route::get('/debug', function(){
    echo session('id_etl');
});

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

Route::get('etl/do/carga_gas', function(){
    $carga_gas = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'cargagas')->first()->url));
    foreach($carga_gas as $carga):
        if(preg_match('/[0-9]/', $carga->nombre_trabajador)):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'nombre_trabajador',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if((float)$carga->cantidad < 0):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'cantidad',
                'comment'   => 'La cantidad no puede ser menor a 0.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if((float)preg_replace('/[^A-Za-z0-9\.]/', '', $carga->precio_litro) < 0):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'precio_litro',
                'comment'   => 'El precio no puede ser menor a 0.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(Misc::cast_float($carga->total) != Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad))):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'total',
                'comment'   => 'El total debe ser (Precio_litro * cantidad), se sugiere que sea '.Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad)),
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($carga->fecha_carga) > time()):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'fecha',
                'comment'   => 'No se puede tener una fecha futura.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($carga->folio_factura == "''" || $carga->folio_factura == null):
            
            Error::create([
                'table'     => 'carga_gas',
                'id_error'  => $carga->id_carga,
                'field'     => 'folio_factura',
                'comment'   => 'No existe un folio de la factura.',
                'etl'       => session('id_etl')
            ]);
        endif;
            $c                      = new CargaGas;
            $c->id                  = $carga->id_carga;
            $c->nombre_trabajador   = $carga->nombre_trabajador;
            $c->nombre_estacion     = $carga->nombre_estacion;
            $c->cantidad            = $carga->cantidad;
            $c->precio_litro        = Misc::cast_float($carga->precio_litro);
            $c->total               = Misc::cast_float($carga->total);
            $c->fecha               = $carga->fecha_carga;
            $c->etl                 = session('id_etl');
            $c->save();
    endforeach;
});
Route::get('etl/do/envios', function(){
    $envios = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'envios')->first()->url));
    foreach($envios as $envio):
        if(preg_match('/[0-9]/', $envio->firmado_por)):
            
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'firmado_por',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(preg_match('/[0-9]/', $envio->nombre_cliente)):
            
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($envio->folio_factura == null || $envio->folio_factura == "''"):
            
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'folio_factura',
                'comment'   => 'No existe un folio de factura.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($envio->creado_en) > time() ):
            
            Error::create([
                'table'     => 'envios',
                'id_error'  => $envio->id_envio,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha futura.',
                'etl'       => session('id_etl')
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
            $e->etl             = session('id_etl');
            $e->save();
    endforeach;
});
Route::get('etl/do/vehiculo_dias', function(){
    $vehiculoDias = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'vehiculodia')->first()->url));
    foreach($vehiculoDias as $vehiculoDia):
        if($vehiculoDia->gas_consumida < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_consumida',
                'comment'   => 'La gasolina que se consumió no puede ser negativa.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->km_recorridos < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_recorridos',
                'comment'   => 'La kilómetros que se recorrieron no pueden ser negativos (a menos que hayas ido de reversa).',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->gas_inicial < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_inicial',
                'comment'   => 'La gasolina no puede ser negativa.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->gas_final < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'gas_final',
                'comment'   => 'La gasolina no puede ser negativa.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->km_inicial > $vehiculoDia->km_final):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_final',
                'comment'   => 'No puedes terminar con un kilometraje menor al inicial.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->km_inicial < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_inicial',
                'comment'   => 'No puedes tener kilometraje negativo',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->km_final < 0):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'km_final',
                'comment'   => 'No puedes tener kilometraje negativo',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($vehiculoDia->fecha_dia) > time() ):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'fecha',
                'comment'   => 'No se puede tener una fecha futura.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->hora_inicio == null):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'hora_inicio',
                'comment'   => 'La hora de inicio no puede estar vacía.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($vehiculoDia->hora_fin == null):
            
            Error::create([
                'table'     => 'vehiculo_dias',
                'id_error'  => $vehiculoDia->id_vehiculo_dia,
                'field'     => 'hora_fin',
                'comment'   => 'La hora de inicio no puede estar vacía.',
                'etl'       => session('id_etl')
            ]);
        endif;
            $vd                     = new VehiculoDia;
            $vd->id                 = $vehiculoDia->id_vehiculo_dia;
            $vd->nombre_trabajador  = $vehiculoDia->nombre_trabajador;
            $vd->fecha              = $vehiculoDia->fecha_dia;
            $vd->gas_inicial         = $vehiculoDia->gas_inicial;
            $vd->gas_final          = $vehiculoDia->gas_final;
            $vd->km_inicial         = $vehiculoDia->km_inicial;
            $vd->km_final           = $vehiculoDia->km_final;
            $vd->hora_inicio        = $vehiculoDia->hora_inicio;
            $vd->hora_fin           = $vehiculoDia->hora_fin;
            $vd->gas_consumida      = $vehiculoDia->gas_consumida;
            $vd->km_recorridos      = $vehiculoDia->km_recorridos;
            $vd->etl                 = session('id_etl');
            $vd->save();
    endforeach;
});
Route::get('etl/do/envio_vehiculo_dias', function(){
    $envioVehiculoDias  = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'enviovehiculodia'  )->first()->url));
    foreach($envioVehiculoDias as $envioVehiculoDia):
        // Bruh, could not find errors here, they are foreign keys!
            $evd                    = new EnvioVehiculoDia;
            $evd->id_envio          = $envioVehiculoDia->id_envio;
            $evd->id_vehiculo_dia   = $envioVehiculoDia->id_vehiculo_dia;
            $evd->etl               = session('id_etl');
            $evd->save();
    endforeach;
});
Route::get('etl/do/devoluciones', function(){
    $devoluciones       = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'devoluciones'      )->first()->url));
    foreach($devoluciones as $devolucion):
        if(preg_match('/[0-9]/', $devolucion->nombre_cliente)):
            
            Error::create([
                'table'     => 'devoluciones',
                'id_error'  => $devolucion->id_devolucion,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if($devolucion->cantidad < 0):
            
            Error::create([
                'table'     => 'devoluciones',
                'id_error'  => $devolucion->id_devolucion,
                'field'     => 'cantidad',
                'comment'   => 'No puede ser una cantidad negativa.',
                'etl'       => session('id_etl')
            ]);
        endif;
            $d                  = new Devoluciones;
            $d->id              = $devolucion->id_devolucion;
            $d->id_prenda       = $devolucion->id_producto;
            $d->id_orden         = $devolucion->id_orden;
            $d->nombre_cliente  = $devolucion->nombre_cliente;
            $d->razon           = $devolucion->razon;
            $d->cantidad        = $devolucion->cantidad;
            $d->etl             = session('id_etl');
            $d->save();
    endforeach;
});
Route::get('etl/do/ordenes', function(){
    $ordenes = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'ordenes')->first()->url));
    foreach($ordenes as $orden):
        if(preg_match('/[0-9]/', $orden->nombre_cliente)):
            
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'nombre_cliente',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($orden->creado_en) > time() ):
            
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha futura.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(Misc::cast_float($orden->iva) != Misc::cast_float(Misc::cast_float($orden->subtotal) * 0.16)):
            
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'iva',
                'comment'   => 'El IVA no corresponde al 16% del subtotal.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(Misc::cast_float($orden->total) != Misc::cast_float(Misc::cast_float($orden->subtotal) + Misc::cast_float($orden->iva))):
            
            Error::create([
                'table'     => 'ordenes',
                'id_error'  => $orden->id_orden,
                'field'     => 'total',
                'comment'   => 'El total no corresponde a la suma del subtotal y el IVA.',
                'etl'       => session('id_etl')
            ]);
        endif;
            $o                  = new Ordenes;
            $o->id              = $orden->id_orden;
            $o->nombre_cliente  = $orden->nombre_cliente;
            $o->fecha           = $orden->creado_en;
            $o->subtotal        = Misc::cast_float($orden->subtotal);
            $o->iva             = Misc::cast_float($orden->iva);
            $o->total           = Misc::cast_float($orden->total);
            $o->tipo_pago       = $orden->tipo_pago;
            $o->etl             = session('id_etl');
            $o->save();
    endforeach;
});
Route::get('etl/do/conductores', function(){
    $conductores = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'conductores')->first()->url));
    foreach($conductores as $conductor):
        if(preg_match('/[0-9]/', $conductor->nombre)):
            
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'nombre',
                'comment'   => 'Un nombre no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(preg_match('/[0-9]/', $conductor->apellido)):
            
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'apellido',
                'comment'   => 'Un apellido no puede contener números.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strlen($conductor->rfc) != 13):
            
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'rfc',
                'comment'   => 'Un RFC debe contener 13 caracteres, 10 del RFC y 3 de clave única.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($conductor->creado_en) > time() ):
            
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'creado_en',
                'comment'   => 'No se puede tener una fecha de inicio de labores futura.',
                'etl'       => session('id_etl')
            ]);
        endif;
        if(strtotime($conductor->fecha_nac) > time() ):
            
            Error::create([
                'table'     => 'empleados',
                'id_error'  => $conductor->id_conductor,
                'field'     => 'fecha_nac',
                'comment'   => 'El empleado debiera haber nacido ya, la fecha hace referencia al futuro.',
                'etl'       => session('id_etl')
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
            $e->etl                 = session('id_etl');
            $e->save();
    endforeach;
});
