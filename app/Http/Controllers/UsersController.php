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

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


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
        $data['count']['error_envios']                  = Envio::all()->count();
        $data['count']['error_vehiculo_dia']            = VehiculoDia::all()->count();
        $data['count']['error_envio_vehiculo_dia']      = EnvioVehiculoDia::all()->count();
        $data['count']['error_carga_gas']               = CargaGas::all()->count();
        $data['count']['error_ordenes']                 = Ordenes::all()->count();
        $data['count']['error_devoluciones']            = Devoluciones::all()->count();
        $data['count']['error_conductores']             = Empleado::all()->count();
        return view('panel.dashboard', $data);
    }
    
    
    public function generateXLS ()
    {     
        \PhpOffice\PhpSpreadsheet\Shared\File::setUseUploadTempDirectory(true);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
        ->setCreator('Ricardo Villagrana')
        ->setLastModifiedBy('ETL System for Costurita Inc.')
        ->setTitle('Data Ware House')
        ->setSubject('Resultado del ETL')
        ->setDescription('Data Ware House in an Excel document.')
        ->setKeywords('DataWareHouse, ETL, Costurita');
        
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial');
        $spreadsheet->getDefaultStyle()->getFont()->setSize(14);
        
        // Create a new worksheet called "My Data"
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Datos Generales');
        // Attach the "My Data" worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 0);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Datos Generales');
        date_default_timezone_set('America/Mexico_City');
        $sheet->setCellValue('B2', 'DataWareHouse de Costurita');
        $sheet->setCellValue('B3', 'Creado por: Ricardo Villagrana');
        $sheet->setCellValue('B4', 'Exportado por: '.session('user')->name);
        $sheet->setCellValue('B5', 'Fecha de exportación: '.date('d/m/Y h:i:s', time()));
        $sheet->setCellValue('B7', 'Tablas');
        $sheet->setCellValue('C7', 'Cantidad de registros');

        $sheet->setCellValue('B8', 'Carga Gas');
        $sheet->setCellValue('B9', 'Conductores');
        $sheet->setCellValue('B10', 'Devoluciones');
        $sheet->setCellValue('B11', 'Envíos');
        $sheet->setCellValue('B12', 'Órdenes');
        $sheet->setCellValue('B13', 'Vehículo Día');

        $sheet->setCellValue('C8', Sqlsrv\CargaGas::all()->count());
        $sheet->setCellValue('C9', Sqlsrv\Empleado::all()->count());
        $sheet->setCellValue('C10', Sqlsrv\Devoluciones::all()->count());
        $sheet->setCellValue('C11', Sqlsrv\Envio::all()->count());
        $sheet->setCellValue('C12', Sqlsrv\Ordenes::all()->count());
        $sheet->setCellValue('C13', Sqlsrv\VehiculoDia::all()->count());
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Carga Gas');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 1);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Carga Gas');
        $sheet->setCellValue('A1', 'Número de carga');
        $sheet->setCellValue('B1', 'Nombre de empleado');
        $sheet->setCellValue('C1', 'Estación de gas');
        $sheet->setCellValue('D1', 'Cantidad (Litros)');
        $sheet->setCellValue('E1', 'Precio por litro ($)');
        $sheet->setCellValue('F1', 'Total ($)');
        $sheet->setCellValue('G1', 'Fecha de carga');
        $sheet->setCellValue('H1', 'Folio de factura');
        $c = 1;
        foreach(Sqlsrv\CargaGas::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->nombre_trabajador);
            $sheet->setCellValue('C'.$c, $row->nombre_estacion);
            $sheet->setCellValue('D'.$c, $row->cantidad);
            $sheet->setCellValue('E'.$c, $row->precio_litro);
            $sheet->setCellValue('F'.$c, $row->total);
            $sheet->setCellValue('G'.$c, $row->fecha);
            $sheet->setCellValue('H'.$c, $row->folio_factura);
        }
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Conductores');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 2);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Conductores');
        
        $sheet->setCellValue('A1', 'Número de empleado');
        $sheet->setCellValue('B1', 'Nombre');
        $sheet->setCellValue('C1', 'Apellido paterno');
        $sheet->setCellValue('D1', 'Apellido materno');
        $sheet->setCellValue('E1', 'Teléfono');
        $sheet->setCellValue('F1', 'Correo Electrónico');
        $sheet->setCellValue('G1', 'RFC');
        $sheet->setCellValue('H1', 'Domicilio');
        $sheet->setCellValue('I1', 'Municipio');
        $sheet->setCellValue('J1', 'Estado');
        $sheet->setCellValue('K1', 'Fecha de inicio');
        $sheet->setCellValue('L1', 'Fecha de nacimiento');
        $c = 1;
        foreach(Sqlsrv\Empleado::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->nombre);
            $sheet->setCellValue('C'.$c, $row->apellido_paterno);
            $sheet->setCellValue('D'.$c, $row->apellido_materno);
            $sheet->setCellValue('E'.$c, $row->telefono);
            $sheet->setCellValue('F'.$c, $row->correo);
            $sheet->setCellValue('G'.$c, $row->rfc);
            $sheet->setCellValue('H'.$c, $row->domicilio);
            $sheet->setCellValue('I'.$c, $row->municipio);
            $sheet->setCellValue('J'.$c, $row->estado);
            $sheet->setCellValue('K'.$c, $row->fecha_inicio);
            $sheet->setCellValue('L'.$c, $row->fecha_nac);
        }
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Devoluciones');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 3);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Devoluciones');
        
        $sheet->setCellValue('A1', 'Número de devolución');
        $sheet->setCellValue('B1', 'Nombre del cliente');
        $sheet->setCellValue('C1', 'Número de orden');
        $sheet->setCellValue('D1', 'Razón');
        $sheet->setCellValue('E1', 'Cantidad');
        $c = 1;
        foreach(Sqlsrv\Devoluciones::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->nombre_cliente);
            $sheet->setCellValue('C'.$c, $row->id_orden);
            $sheet->setCellValue('D'.$c, $row->razon);
            $sheet->setCellValue('E'.$c, $row->cantidad);
        }
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Envíos');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 4);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Envíos');
        $sheet->setCellValue('A1', 'Número de envío');
        $sheet->setCellValue('B1', 'Número de orden');
        $sheet->setCellValue('C1', 'Nombre de cliente');
        $sheet->setCellValue('D1', 'Estatus envío');
        $sheet->setCellValue('E1', 'Quién firmó');
        $sheet->setCellValue('F1', 'Folio factura');
        $sheet->setCellValue('G1', 'Fecha de creación');
        $c = 1;
        foreach(Sqlsrv\Envio::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->id_orden);
            $sheet->setCellValue('C'.$c, $row->nombre_cliente);
            $sheet->setCellValue('D'.$c, $row->estatus);
            $sheet->setCellValue('E'.$c, $row->firmado_por);
            $sheet->setCellValue('F'.$c, $row->folio_factura);
            $sheet->setCellValue('G'.$c, $row->fecha);
        }
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Órdenes');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 5);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Órdenes');
        
        $sheet->setCellValue('A1', 'Número de orden	');
        $sheet->setCellValue('B1', 'Nombre del cliente');
        $sheet->setCellValue('C1', 'Subtotal');
        $sheet->setCellValue('D1', 'IVA');
        $sheet->setCellValue('E1', 'Total');
        $sheet->setCellValue('F1', 'Tipo de pago');
        $sheet->setCellValue('G1', 'Fecha');
        $c = 1;
        foreach(Sqlsrv\Ordenes::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->nombre_cliente);
            $sheet->setCellValue('C'.$c, $row->subtotal);
            $sheet->setCellValue('D'.$c, $row->iva);
            $sheet->setCellValue('E'.$c, $row->total);
            $sheet->setCellValue('F'.$c, $row->tipo_pago);
            $sheet->setCellValue('G'.$c, $row->fecha);
        }
        
        // Create a new worksheet
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Vehículo Día');
        // Attach the worksheet as the first worksheet in the Spreadsheet object
        $spreadsheet->addSheet($myWorkSheet, 6);
        $sheet = $spreadsheet->setActiveSheetIndexByName('Vehículo Día');
        
        $sheet->setCellValue('A1', 'Número de vehículo');
        $sheet->setCellValue('B1', 'Nombre del trabajador');
        $sheet->setCellValue('C1', 'Fecha del día');
        $sheet->setCellValue('D1', 'Gas al inicio del día');
        $sheet->setCellValue('E1', 'Gas al final del día');
        $sheet->setCellValue('F1', 'KM al inicio del día');
        $sheet->setCellValue('G1', 'KM al final del día');
        $sheet->setCellValue('H1', 'Hora de inicio');
        $sheet->setCellValue('I1', 'Hora de finalización');
        $sheet->setCellValue('J1', 'Gas consumida en total');
        $sheet->setCellValue('K1', 'KM recorridos al final');
        $c = 1;
        foreach(Sqlsrv\VehiculoDia::all() as $row){
            $c++;
            $sheet->setCellValue('A'.$c, $row->id);
            $sheet->setCellValue('B'.$c, $row->nombre_trabajador);
            $sheet->setCellValue('C'.$c, $row->fecha);
            $sheet->setCellValue('D'.$c, $row->gas_inicial);
            $sheet->setCellValue('E'.$c, $row->gas_final);
            $sheet->setCellValue('F'.$c, $row->km_inicial);
            $sheet->setCellValue('G'.$c, $row->km_final);
            $sheet->setCellValue('H'.$c, $row->hora_inicio);
            $sheet->setCellValue('I'.$c, $row->hora_fin);
            $sheet->setCellValue('J'.$c, $row->gas_consumida);
            $sheet->setCellValue('K'.$c, $row->km_recorridos);
        }
        
        $spreadsheet->removeSheetByIndex(7);
        
        $sheet = $spreadsheet->setActiveSheetIndexByName('Datos Generales');
        $writer = new Xlsx($spreadsheet);
        $fileName = './DataWareHouse-'.session('user')->name.'.xlsx';
        $writer->save($fileName);
        return redirect($fileName);
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
