<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SourcesLocal;

class VehiculoDiaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['user'] = session('user');
        $data['tableName'] = "Vehículo Día";
        $data['columns'] = [
            'Número de vehículo', 
            'Nombre del trabajador',
            'Fecha del día',
            'Gas al inicio del día',
            'Gas al final del día',
            'KM al inicio del día',
            'KM al final del día',
            'Hora de inicio',
            'Hora de finalización',
            'Gas consumida en total',
            'KM recorridos al final'
        ];
        $data['params'] = [
            'id',
            'nombre_trabajador',
            'fecha',
            'gas_inicial',
            'gas_final',
            'km_inicial',
            'km_final',
            'hora_inicio',
            'hora_fin',
            'gas_consumida',
            'km_recorridos'
        ];
        $data['rows'] = \App\Sqlsrv\VehiculoDia::all();
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
