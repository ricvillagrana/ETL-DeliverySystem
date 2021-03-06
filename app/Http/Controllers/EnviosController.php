<?php

namespace App\Http\Controllers;

use App\Sqlsrv\Envio;
use App\SourcesLocal;
use Illuminate\Http\Request;
use Illuminate\Http\Schema;

class EnviosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['user'] = session('user');
        $data['tableName'] = "Envios";
        $data['columns'] = [
            'Número de envío', 
            'Número de orden',
            'Nombre del cliente',
            'Estatus de envío',
            'Quién firmó',
            'Folio de la factura',
            'Fecha de creación'
        ];
        $data['params'] = [
            'id',
            'id_orden',
            'nombre_cliente',
            'estatus',
            'firmado_por',
            'folio_factura',
            'fecha'
        ];
        $data['rows'] = \App\Sqlsrv\Envio::all();
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
