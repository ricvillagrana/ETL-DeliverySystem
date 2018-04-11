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
        Session::forget('errors');
        return redirect('/etl');
    else:
        return redirect('/');
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
        $errors = [
            'error_quantity'=> 0,
            'carga_gas'     => [],
            'envios'        => [],
            'vehiculo_dia'  => [],
            'devoluciones'  => [],
            'ordenes'       => [],
            'empleados'     => []
        ];
        // Define config variable
        $config = [
            'INSERT_CORRECT_DATA'   => true,
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
            $everythingOk = true;
            $errorIn = [];
            if(preg_match('/[0-9]/', $carga->nombre_trabajador)):
                $everythingOk = false;
                array_push($errorIn, ['nombre_trabajador' => 'Un nombre no puede contener números.']);
            endif;
            if((float)$carga->cantidad < 0):
                $everythingOk = false;
                array_push($errorIn, ['cantidad' => 'La cantidad no puede se menor o igual a 0.']);
            endif;
            if((float)$carga->precio_litro > 0):
                $everythingOk = false;
                array_push($errorIn, ['precio_litro' => 'El precio no puede ser menor o igual a 0.']);
            endif;
            if((float)$carga->total != (float)$carga->precio_litro * (float)$carga->cantidad):
                $everythingOk = false;
                array_push($errorIn, ['total' => 'El total debe ser (Precio_litro * cantidad), se sugiere que sea '.($carga->precio_litro * $carga->cantidad) ]);
            endif;
            if(strtotime($carga->fecha_carga) > time()):
                $everythingOk = false;
                array_push($errorIn, ['fecha' => 'No se puede tener una fecha futura.' ]);
            endif;
            if($carga->folio_factura == "" || $carga->folio_factura == null):
                $everythingOk = false;
                array_push($errorIn, ['folio_factura' => 'No existe un folio de la factura.']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
                $c                      = new CargaGas;
                $c->id                  = $carga->id_carga;
                $c->nombre_trabajador   = $carga->nombre_trabajador;
                $c->nombre_estacion     = $carga->nombre_estacion;
                $c->cantidad            = $carga->cantidad;
                $c->precio_litro        = (double)$carga->precio_litro;
                $c->total               = (double)$carga->total;
                $c->fecha               = $carga->fecha_carga;
                $c->etl                 = $etl->id;
                $c->save();
            else:
                $errors['error_quantity']++;
                array_push($errors['carga_gas'], [$carga->id_carga => $errorIn]);
            endif;
        endforeach;
        /**
        * Envios
        */
        foreach($envios as $envio):
            $everythingOk = true;
            $errorIn = [];
            if(preg_match('/[0-9]/', $envio->firmado_por)):
                $everythingOk = false;
                array_push($errorIn, ['firmado_por' => 'Un nombre no puede contener números.']);
            endif;
            if(preg_match('/[0-9]/', $envio->nombre_cliente)):
                $everythingOk = false;
                array_push($errorIn, ['nombre_cliente' => 'Un nombre no puede contener números.']);
            endif;
            if($envio->folio_factura == null || $envio->folio_factura == ""):
                $everythingOk = false;
                array_push($errorIn, ['folio_factura' => 'No existe un folio de factura.']);
            endif;
            if(strtotime($envio->creado_en) > time() ):
                $everythingOk = false;
                array_push($errorIn, ['creado_en' => 'No se puede tener una fecha futura.']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
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
            else:
                $errors['error_quantity']++;
                array_push($errors['envios'], [$envio->id_envio => $errorIn]);
            endif;
        endforeach;
        /**
        * Vehículo Día
        */
        foreach($vehiculoDias as $vehiculoDia):
            $everythingOk = true;
            $errorIn = [];
            if($vehiculoDia->gas_consumida < 0):
                $everythingOk = false;
                array_push($errorIn, ['gas_consumida' => 'La gasolina que se consumió no puede ser negativa.']);
            endif;
            if($vehiculoDia->km_recorridos < 0):
                $everythingOk = false;
                array_push($errorIn, ['km_recorridos' => 'La kilómetros que se recorrieron no pueden ser negativos (a menos que hayas ido de reversa).']);
            endif;
            if($vehiculoDia->gas_inicial < 0):
                $everythingOk = false;
                array_push($errorIn, ['gas_inicial' => 'La gasolina que no puede ser negativa.']);
            endif;
            if($vehiculoDia->gas_final < 0):
                $everythingOk = false;
                array_push($errorIn, ['gas_final' => 'La gasolina no puede ser negativa.']);
            endif;
            if($vehiculoDia->km_inicial > $vehiculoDia->km_final):
                $everythingOk = false;
                array_push($errorIn, ['km_final' => 'No puedes terminar con un kilometraje menor al inicial.']);
            endif;
            if(strtotime($vehiculoDia->fecha_dia) > time() ):
                $everythingOk = false;
                array_push($errorIn, ['fecha' => 'No se puede tener una fecha futura.']);
            endif;
            if($vehiculoDia->hora_inicio == null):
                $everythingOk = false;
                array_push($errorIn, ['hora_inicio' => 'La hora de inicio no puede estar vacía.']);
            endif;
            if($vehiculoDia->hora_fin == null):
                $everythingOk = false;
                array_push($errorIn, ['hora_fin' => 'La hora de inicio no puede estar vacía.']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
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
            else:
                $errors['error_quantity']++;
                array_push($errors['vehiculo_dia'], [$vehiculoDia->id_vehiculo_dia => $errorIn]);
            endif;
        endforeach;
        /**
        * Envíos Vehículo Día
        */
        foreach($envioVehiculoDias as $envioVehiculoDia):
            $everythingOk = true;
            // Bruh, could not find errors here, they are foreign keys!
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
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
            $everythingOk = true;
            $errorIn =[];
            if(preg_match('/[0-9]/', $devolucion->nombre_cliente)):
                $everythingOk = false;
                array_push($errorIn, ['nombre_cliente' => 'Un nombre no puede contener números.']);
            endif;
            if($devolucion->cantidad < 0):
                $everythingOk = false;
                array_push($errorIn, ['cantidad' => 'No puede se una cantidad positiva']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
                $d                  = new Devoluciones;
                $d->id              = $devolucion->id_devolucion;
                $d->id_prenda       = $devolucion->id_producto;
                $d->id_orden         = $devolucion->id_orden;
                $d->nombre_cliente  = $devolucion->nombre_cliente;
                $d->razon           = $devolucion->razon;
                $d->cantidad        = $devolucion->cantidad;
                $d->etl             = $etl->id;
                $d->save();
            else:
                $errors['error_quantity']++;
                array_push($errors['devoluciones'], [$devolucion->id_devolucion => $errorIn]);
            endif;
        endforeach;
        /**
        * Órdenes
        */
        foreach($ordenes as $orden):
            $everythingOk = true;
            $errorIn =[];
            if(preg_match('/[0-9]/', $orden->nombre_cliente)):
                $everythingOk = false;
                array_push($errorIn, ['nombre_cliente' => 'Un nombre no puede contener números.']);
            endif;
            if(strtotime($orden->creado_en) > time() ):
                $everythingOk = false;
                array_push($errorIn, ['creado_en' => 'No se puede tener una fecha futura.']);
            endif;
            if((float)$orden->iva != (float)$orden->subtotal * 0.16):
                $everythingOk = false;
                array_push($errorIn, ['iva' => 'El IVA no corresponde al 16% del subtotal.']);
            endif;
            if((float)$orden->total != (float)$orden->subtotal + (float)$orden->iva):
                $everythingOk = false;
                array_push($errorIn, ['total' => 'El total no corresponde a la suma del subtotal y el IVA.']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
                $o                  = new Ordenes;
                $o->id              = $orden->id_orden;
                $o->nombre_cliente  = $orden->nombre_cliente;
                $o->fecha           = $orden->creado_en;
                $o->subtotal        = (double)$orden->subtotal;
                $o->iva             = (double)$orden->iva;
                $o->total           = (double)$orden->total;
                $o->tipo_pago       = $orden->tipo_pago;
                $o->etl             = $etl->id;
                $o->save();
            else:
                $errors['error_quantity']++;
                array_push($errors['ordenes'], [$orden->id_orden => $errorIn]);
            endif;
        endforeach;
        /**
        * Conductores
        */
        foreach($conductores as $conductor):
            $everythingOk = true;
            $errorIn = [];
            if(preg_match('/[0-9]/', $conductor->nombre)):
                $everythingOk = false;
                array_push($errorIn, ['nombre' => 'Un nombre no puede contener números.']);
            endif;
            if(preg_match('/[0-9]/', $conductor->apellido)):
                $everythingOk = false;
                array_push($errorIn, ['apellido' => 'Un apellido no puede contener números.']);
            endif;
            if(strlen($conductor->rfc) != 13):
                $everythingOk = false;
                array_push($errorIn, ['rfc' => 'Un RFC debe contener 13 caracteres, 10 del RFC y 3 de clave única']);
            endif;
            if(strtotime($conductor->creado_en) > time() ):
                $everythingOk = false;
                array_push($errorIn, ['creado_en' => 'No se puede tener una fecha de inicio de labores   futura.']);
            endif;
            if(strtotime($conductor->fecha_nac) > time() ):
                $everythingOk = false;
                array_push($errorIn, ['fecha_nac' => 'El cliente debiera haber nacido ya, la fecha hace referencia al futuro.']);
            endif;
            if($everythingOk && $config['INSERT_CORRECT_DATA']):
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
            else:
                $errors['error_quantity']++;
                array_push($errors['empleados'], [$orden->id_orden => $errorIn]);
            endif;
        endforeach;
        //echo json_encode($errors);
        // Save in cache errors
        session(['errors' => $errors]);
        return redirect('etl/corrections');
    else:
        return redirect('/');
    endif;
});