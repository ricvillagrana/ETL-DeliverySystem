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
use Illuminate\Support\Facades\DB;

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
        $data['errors'] = Error::where('solved', '<>', '1')->get();
        $data['auto_fix'] = Error::where('auto_fix', '<>', '')->count() > 0 ? true : false;
        $data['error_quantity'] = Error::where('solved', '<>', '1')->count();
        $data['error_quantity_total'] = Error::all()->count();
        return view('panel.errors', $data);
    }
    
    public function autoFix () 
    {
        $errors = Error::all();
        $fixed = [];
        foreach($errors as $error){
            if($error->auto_fix != ''){
                $error->solved = true;
                if(false !== strpos($error->field, 'hora')){
                    $time = new \DateTime($error->auto_fix);
                    $error->auto_fix = $time->format('H:i:s');
                }
                if(false !== strpos($error->field, 'fecha') || false !== strpos($error->field, 'creado')){
                    $date = new \DateTime($error->auto_fix);
                    $error->auto_fix = $date->format('Y-m-d H:i:s');
                }
                $error->original = (array)DB::select("SELECT $error->field FROM $error->table WHERE id = $error->id_error")[0];
                $error->original = $error->original[$error->field];
                $error->save();
                $row = DB::select("update $error->table set $error->field = '$error->auto_fix' where id = $error->id_error");
                array_push($fixed, $row);
            }
        }
    }
    
    public function check ($param = true) 
    {
        $errors = Error::solved();
        if(session('user') === null)return redirect('/')->with('error', 'Debes iniciar sesión.');
        $data['user'] = session('user');
        // Getting data from Envios
        $envios['table_name'] = "Envios";
        $envios['headers'] = ['Nombre del cliente', 'Persona que firma de recibido', 'Fecha', 'Folio de la factura', 'Estatus de la entrega'];
        $envios['indexes'] = ['nombre_cliente', 'firmado_por', 'fecha', 'folio_factura', 'estatus'];
        $rows = json_decode(json_encode(Envio::solved($param)),true);
        $envios['data'] = [];
        foreach($rows as $row){
            if(!isset($envios['data'][$row['id_error']])){
                $envios['data'][$row['id_error']]= $row;
                $envios['data'][$row['id_error']]['field'] = [$row['field']];
                $envios['data'][$row['id_error']]['comment'] = [$row['comment']];
                $envios['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $envios['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $envios['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
            }else{
                array_push($envios['data'][$row['id_error']]['field'], $row['field']);
                array_push($envios['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($envios['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($envios['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
            }
        }
        $data['tables']['envios'] = $envios;
        
        // Getting data from Carga Gas
        $carga_gas['table_name'] = "Carga Gas";
        $carga_gas['headers'] = ['Nombre del trabajador', 'Nombre de la estación', 'Cantidad (Litros)', 'Precio ($)', 'Total ($)', 'Folio factura', 'Fecha'];
        $carga_gas['indexes'] = ['nombre_trabajador', 'nombre_estacion', 'cantidad', 'precio_litro', 'total', 'folio_factura', 'fecha'];
        $rows = json_decode(json_encode(CargaGas::solved($param)),true);
        $carga_gas['data'] = [];
        foreach($rows as $row){
            if(!isset($carga_gas['data'][$row['id_error']])){
                $carga_gas['data'][$row['id_error']]= $row;
                $carga_gas['data'][$row['id_error']]['field'] = [$row['field']];
                $carga_gas['data'][$row['id_error']]['comment'] = [$row['comment']];
                $carga_gas['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $carga_gas['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $carga_gas['data'][$row['id_error']]['original'] = [$row['original']];
            }else{
                array_push($carga_gas['data'][$row['id_error']]['field'], $row['field']);
                array_push($carga_gas['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($carga_gas['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($carga_gas['data'][$row['id_error']]['original'], $row['original']);
            }
        }
        $data['tables']['carga_gas'] = $carga_gas;

        // Getting data from Devoluciones
        $devoluciones['table_name'] = "Devoluciones";
        $devoluciones['headers'] = ['Nombre del cliente', 'Razón de la devolución', 'Cantidad (prendas)', 'Fecha de devolución'];
        $devoluciones['indexes'] = ['nombre_cliente', 'razon', 'cantidad', 'created_at'];
        $rows = json_decode(json_encode(Devoluciones::solved($param)),true);
        $devoluciones['data'] = [];
        foreach($rows as $row){
            if(!isset($devoluciones['data'][$row['id_error']])){
                $devoluciones['data'][$row['id_error']]= $row;
                $devoluciones['data'][$row['id_error']]['field'] = [$row['field']];
                $devoluciones['data'][$row['id_error']]['comment'] = [$row['comment']];
                $devoluciones['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $devoluciones['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $devoluciones['data'][$row['id_error']]['original'] = [$row['original']];
            }else{
                array_push($devoluciones['data'][$row['id_error']]['field'], $row['field']);
                array_push($devoluciones['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($devoluciones['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($devoluciones['data'][$row['id_error']]['original'], $row['original']);
            }
        }
        $data['tables']['devoluciones'] = $devoluciones;

        // Getting data from Vehículo Día
        $vehiculo_dias['table_name'] = "Vehículo Día";
        $vehiculo_dias['headers'] = ['Nombre del trabajador', 'Gasolina inicial', 'Gasolina final', 'Gasolina consumida', 'Km inicial', 'Km final', 'Km recorridos', 'Hora inicial', 'Hora final', 'fecha'];
        $vehiculo_dias['indexes'] = ['nombre_trabajador', 'gas_inicial', 'gas_final', 'gas_consumida', 'km_inicial', 'km_final', 'km_recorridos', 'hora_inicio', 'hora_fin', 'fecha'];
        $rows = json_decode(json_encode(VehiculoDia::solved($param)),true);
        $vehiculo_dias['data'] = [];
        foreach($rows as $row){
            if(!isset($vehiculo_dias['data'][$row['id_error']])){
                $vehiculo_dias['data'][$row['id_error']]= $row;
                $vehiculo_dias['data'][$row['id_error']]['field'] = [$row['field']];
                $vehiculo_dias['data'][$row['id_error']]['comment'] = [$row['comment']];
                $vehiculo_dias['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $vehiculo_dias['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $vehiculo_dias['data'][$row['id_error']]['original'] = [$row['original']];
            }else{
                array_push($vehiculo_dias['data'][$row['id_error']]['field'], $row['field']);
                array_push($vehiculo_dias['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($vehiculo_dias['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($vehiculo_dias['data'][$row['id_error']]['original'], $row['original']);
            }
        }
        $data['tables']['vehiculo_dias'] = $vehiculo_dias;

        // Getting data from Órdenes
        $ordenes['table_name'] = "Órdenes";
        $ordenes['headers'] = ['Nombre del cliente', 'Fecha', 'Subtotal ($)', 'IVA ($)', 'Total ($)', 'Tipo de pago'];
        $ordenes['indexes'] = ['nombre_cliente', 'fecha', 'subtotal', 'iva', 'total', 'tipo_pago'];
        $rows = json_decode(json_encode(Ordenes::solved($param)),true);
        $ordenes['data'] = [];
        foreach($rows as $row){
            if(!isset($ordenes['data'][$row['id_error']])){
                $ordenes['data'][$row['id_error']]= $row;
                $ordenes['data'][$row['id_error']]['field'] = [$row['field']];
                $ordenes['data'][$row['id_error']]['comment'] = [$row['comment']];
                $ordenes['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $ordenes['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $ordenes['data'][$row['id_error']]['original'] = [$row['original']];
            }else{
                array_push($ordenes['data'][$row['id_error']]['field'], $row['field']);
                array_push($ordenes['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($ordenes['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($ordenes['data'][$row['id_error']]['original'], $row['original']);
            }
        }
        $data['tables']['ordenes'] = $ordenes;

        // Getting data from Conductores
        $empleados['table_name'] = "Empleados";
        $empleados['headers'] = ['Nombre del cliente', 'Apellido Paterno', 'Apellido Materno', 'Teléfono', 'Correo electrónico', 'RFC', 'Domicilio', 'Municipio', 'Estado', 'Fecha de inicio', 'Fecha de Nacimiento'];
        $empleados['indexes'] = ['nombre', 'apellido_paterno', 'apellido_materno', 'telefono', 'correo', 'rfc', 'domiciolio', 'municipio', 'estado', 'fecha_inicio', 'fecha_nac'];
        $rows = json_decode(json_encode(Ordenes::solved($param)),true);
        $empleados['data'] = [];
        foreach($rows as $row){
            if(!isset($empleados['data'][$row['id_error']])){
                $empleados['data'][$row['id_error']]= $row;
                $empleados['data'][$row['id_error']]['field'] = [$row['field']];
                $empleados['data'][$row['id_error']]['comment'] = [$row['comment']];
                $empleados['data'][$row['id_error']]['id_error'] = [$row['id_error']];
                $empleados['data'][$row['id_error']]['auto_fix'] = [$row['auto_fix']];
                $empleados['data'][$row['id_error']]['original'] = [$row['original']];
            }else{
                array_push($empleados['data'][$row['id_error']]['field'], $row['field']);
                array_push($empleados['data'][$row['id_error']]['comment'], $row['comment']);
                array_push($empleados['data'][$row['id_error']]['auto_fix'], $row['auto_fix']);
                array_push($empleados['data'][$row['id_error']]['original'], $row['original']);
            }
        }
        $data['tables']['empleados'] = $empleados;

        // Debug
        // return json_encode($data);
        
        $data['total_errors'] = Error::all()->count();

        return view('panel.editable', $data);
    }
    
}
