<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SourcesLocal;

class OrdenesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['user'] = session('user');
        $data['tableName'] = "Órdenes";
        $data['columns'] = [
            'Número de orden',
            'Número de cliente', 
            'Prioridad (1-5)',
            'Cancelada',
            'Subtotal',
            'IVA',
            'Total',
            'Tipo de pago',
            'Fecha'
        ];
        $data['params'] = [
            'id_orden',
            'id_cliente',
            'prioridad',
            'cancelada',
            'subtotal',
            'iva',
            'total',
            'tipo_pago',
            'creado_en'
        ];
        $data['rows'] = json_decode(file_get_contents(SourcesLocal::where('name', 'like', 'ordenes')->first()->url), true);
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
