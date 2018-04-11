<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SourcesLocal;

class CargaGasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['user'] = session('user');
        $data['tableName'] = "Carga Gas";
        $data['columns'] = [
            'Número de carga',
            'Número de empleado', 
            'Número de vehiculo',
            'Número de estación de gas',
            'Cantidad (Litros)',
            'Precio / Litro',
            'Total',
            'Fecha de carga',
            'Folio de factura'
        ];
        $data['params'] = [
            'id_carga',
            'id_conductor',
            'id_vehiculo',
            'id_estacion_gas',
            'cantidad',
            'precio_litro',
            'total',
            'fecha_carga',
            'folio_factura'
        ];
        $data['rows'] = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'cargagas')->first()->url), true);
        return view('panel.table', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
