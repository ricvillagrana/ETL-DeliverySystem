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
    $carga_gas          = json_decode(file_get_contents(App\SourcesLocal::where('name', 'like', 'cargagas'          )->first()->url));
    foreach($carga_gas as $carga):
        echo Misc::cast_float($carga->total) ."<br>";
    endforeach;
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
Route::get('etl/begin', function () {
    if(session('user') != null):
        // Define variables from APIs Interface
        $carga_gas          = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'cargagas'          )->first()->url));
        $envios             = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'envios'            )->first()->url));
        $vehiculoDias       = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'vehiculodia'       )->first()->url));
        $envioVehiculoDias  = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'enviovehiculodia'  )->first()->url));
        $devoluciones       = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'devoluciones'      )->first()->url));
        $ordenes            = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'ordenes'           )->first()->url));
        $conductores        = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'conductores'       )->first()->url));
        // Define errors variables
        $errors = 0;
        // Define config variable
        $config = [
            'DEBUG_MODE'   => false,
        ];
        
        /**
        * Register new ETL with current User
        * Using ${Object}->etl = $etl->id; for registering the transaction to current ETL
        */
        $etl = new Etl;
        $etl->id_user = session('user')->id;
        $etl->save();
        
        
        // Begins process
        /**
        * Carga Gas
        */
        foreach($carga_gas as $carga):
            if(preg_match('/[0-9]/', $carga->nombre_trabajador)):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'nombre_trabajador',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if((float)$carga->cantidad < 0):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'cantidad',
                    'comment'   => 'La cantidad no puede ser menor a 0.',
                    'etl'       => $etl->id
                ]);
            endif;
            if((float)preg_replace('/[^A-Za-z0-9\.]/', '', $carga->precio_litro) < 0):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'precio_litro',
                    'comment'   => 'El precio no puede ser menor a 0.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(Misc::cast_float($carga->total) != Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad))):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'total',
                    'comment'   => 'El total debe ser (Precio_litro * cantidad), se sugiere que sea '.Misc::cast_float(Misc::cast_float($carga->precio_litro) * Misc::cast_float($carga->cantidad)),
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($carga->fecha_carga) > time()):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'fecha',
                    'comment'   => 'No se puede tener una fecha futura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($carga->folio_factura == "''" || $carga->folio_factura == null):
                $errors++;
                Error::create([
                    'table'     => 'carga_gas',
                    'id_error'  => $carga->id_carga,
                    'field'     => 'folio_factura',
                    'comment'   => 'No existe un folio de la factura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
                $c                      = new CargaGas;
                $c->id                  = $carga->id_carga;
                $c->nombre_trabajador   = $carga->nombre_trabajador;
                $c->nombre_estacion     = $carga->nombre_estacion;
                $c->cantidad            = $carga->cantidad;
                $c->precio_litro        = Misc::cast_float($carga->precio_litro);
                $c->total               = Misc::cast_float($carga->total);
                $c->fecha               = $carga->fecha_carga;
                $c->etl                 = $etl->id;
                $c->save();
            endif;
        endforeach;
        /**
        * Envios
        */
        foreach($envios as $envio):
            if(preg_match('/[0-9]/', $envio->firmado_por)):
                $errors++;
                Error::create([
                    'table'     => 'envios',
                    'id_error'  => $envio->id_envio,
                    'field'     => 'firmado_por',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(preg_match('/[0-9]/', $envio->nombre_cliente)):
                $errors++;
                Error::create([
                    'table'     => 'envios',
                    'id_error'  => $envio->id_envio,
                    'field'     => 'nombre_cliente',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($envio->folio_factura == null || $envio->folio_factura == "''"):
                $errors++;
                Error::create([
                    'table'     => 'envios',
                    'id_error'  => $envio->id_envio,
                    'field'     => 'folio_factura',
                    'comment'   => 'No existe un folio de factura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($envio->creado_en) > time() ):
                $errors++;
                Error::create([
                    'table'     => 'envios',
                    'id_error'  => $envio->id_envio,
                    'field'     => 'creado_en',
                    'comment'   => 'No se puede tener una fecha futura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
                $e                  = new Envio;
                $e->id              = $envio->id_envio;
                $e->id_orden        = $envio->id_orden;
                $e->nombre_cliente  = $envio->nombre_cliente;
                $e->firmado_por     = $envio->firmado_por;
                $e->folio_factura   = $envio->folio_factura;
                $e->fecha           = $envio->creado_en;
                $e->estatus         = $envio->estatus;
                $e->etl             = $etl->id;
                $e->save();
            endif;
        endforeach;
        /**
        * Vehículo Día
        */
        foreach($vehiculoDias as $vehiculoDia):
            if($vehiculoDia->gas_consumida < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'gas_consumida',
                    'comment'   => 'La gasolina que se consumió no puede ser negativa.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->km_recorridos < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'km_recorridos',
                    'comment'   => 'La kilómetros que se recorrieron no pueden ser negativos (a menos que hayas ido de reversa).',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->gas_inicial < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'gas_inicial',
                    'comment'   => 'La gasolina no puede ser negativa.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->gas_final < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'gas_final',
                    'comment'   => 'La gasolina no puede ser negativa.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->km_inicial > $vehiculoDia->km_final):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'km_final',
                    'comment'   => 'No puedes terminar con un kilometraje menor al inicial.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->km_inicial < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'km_inicial',
                    'comment'   => 'No puedes tener kilometraje negativo',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->km_final < 0):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'km_final',
                    'comment'   => 'No puedes tener kilometraje negativo',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($vehiculoDia->fecha_dia) > time() ):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'fecha',
                    'comment'   => 'No se puede tener una fecha futura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->hora_inicio == null):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'hora_inicio',
                    'comment'   => 'La hora de inicio no puede estar vacía.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($vehiculoDia->hora_fin == null):
                $errors++;
                Error::create([
                    'table'     => 'vehiculo_dias',
                    'id_error'  => $vehiculoDia->id_vehiculo_dia,
                    'field'     => 'hora_fin',
                    'comment'   => 'La hora de inicio no puede estar vacía.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
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
                $vd->etl                 = $etl->id;
                $vd->save();
            endif;
        endforeach;
        /**
        * Envíos Vehículo Día
        */
        foreach($envioVehiculoDias as $envioVehiculoDia):
            
            // Bruh, could not find errors here, they are foreign keys!
            if(!$config['DEBUG_MODE']):
                $evd                    = new EnvioVehiculoDia;
                $evd->id_envio          = $envioVehiculoDia->id_envio;
                $evd->id_vehiculo_dia   = $envioVehiculoDia->id_vehiculo_dia;
                $evd->etl               = $etl->id;
                $evd->save();
            endif;
        endforeach;
        /**
        * Devoluciones
        */
        foreach($devoluciones as $devolucion):
            if(preg_match('/[0-9]/', $devolucion->nombre_cliente)):
                $errors++;
                Error::create([
                    'table'     => 'devoluciones',
                    'id_error'  => $devolucion->id_devolucion,
                    'field'     => 'nombre_cliente',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if($devolucion->cantidad < 0):
                $errors++;
                Error::create([
                    'table'     => 'devoluciones',
                    'id_error'  => $devolucion->id_devolucion,
                    'field'     => 'cantidad',
                    'comment'   => 'No puede ser una cantidad negativa.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
                $d                  = new Devoluciones;
                $d->id              = $devolucion->id_devolucion;
                $d->id_prenda       = $devolucion->id_producto;
                $d->id_orden         = $devolucion->id_orden;
                $d->nombre_cliente  = $devolucion->nombre_cliente;
                $d->razon           = $devolucion->razon;
                $d->cantidad        = $devolucion->cantidad;
                $d->etl             = $etl->id;
                $d->save();
            endif;
        endforeach;
        /**
        * Órdenes
        */
        foreach($ordenes as $orden):
            if(preg_match('/[0-9]/', $orden->nombre_cliente)):
                $errors++;
                Error::create([
                    'table'     => 'ordenes',
                    'id_error'  => $orden->id_orden,
                    'field'     => 'nombre_cliente',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($orden->creado_en) > time() ):
                $errors++;
                Error::create([
                    'table'     => 'ordenes',
                    'id_error'  => $orden->id_orden,
                    'field'     => 'creado_en',
                    'comment'   => 'No se puede tener una fecha futura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(Misc::cast_float($orden->iva) != Misc::cast_float(Misc::cast_float($orden->subtotal) * 0.16)):
                $errors++;
                Error::create([
                    'table'     => 'ordenes',
                    'id_error'  => $orden->id_orden,
                    'field'     => 'iva',
                    'comment'   => 'El IVA no corresponde al 16% del subtotal.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(Misc::cast_float($orden->total) != Misc::cast_float(Misc::cast_float($orden->subtotal) + Misc::cast_float($orden->iva))):
                $errors++;
                Error::create([
                    'table'     => 'ordenes',
                    'id_error'  => $orden->id_orden,
                    'field'     => 'total',
                    'comment'   => 'El total no corresponde a la suma del subtotal y el IVA.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
                $o                  = new Ordenes;
                $o->id              = $orden->id_orden;
                $o->nombre_cliente  = $orden->nombre_cliente;
                $o->fecha           = $orden->creado_en;
                $o->subtotal        = Misc::cast_float($orden->subtotal);
                $o->iva             = Misc::cast_float($orden->iva);
                $o->total           = Misc::cast_float($orden->total);
                $o->tipo_pago       = $orden->tipo_pago;
                $o->etl             = $etl->id;
                $o->save();
            endif;
        endforeach;
        /**
        * Conductores
        */
        foreach($conductores as $conductor):
            if(preg_match('/[0-9]/', $conductor->nombre)):
                $errors++;
                Error::create([
                    'table'     => 'empleados',
                    'id_error'  => $conductor->id_conductor,
                    'field'     => 'nombre',
                    'comment'   => 'Un nombre no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(preg_match('/[0-9]/', $conductor->apellido)):
                $errors++;
                Error::create([
                    'table'     => 'empleados',
                    'id_error'  => $conductor->id_conductor,
                    'field'     => 'apellido',
                    'comment'   => 'Un apellido no puede contener números.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strlen($conductor->rfc) != 13):
                $errors++;
                Error::create([
                    'table'     => 'empleados',
                    'id_error'  => $conductor->id_conductor,
                    'field'     => 'rfc',
                    'comment'   => 'Un RFC debe contener 13 caracteres, 10 del RFC y 3 de clave única.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($conductor->creado_en) > time() ):
                $errors++;
                Error::create([
                    'table'     => 'empleados',
                    'id_error'  => $conductor->id_conductor,
                    'field'     => 'creado_en',
                    'comment'   => 'No se puede tener una fecha de inicio de labores futura.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(strtotime($conductor->fecha_nac) > time() ):
                $errors++;
                Error::create([
                    'table'     => 'empleados',
                    'id_error'  => $conductor->id_conductor,
                    'field'     => 'fecha_nac',
                    'comment'   => 'El empleado debiera haber nacido ya, la fecha hace referencia al futuro.',
                    'etl'       => $etl->id
                ]);
            endif;
            if(!$config['DEBUG_MODE']):
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
                $e->etl                 = $etl->id;
                $e->save();
            endif;
        endforeach;
        return $errors;
    else:
        return redirect('/');
    endif;
});