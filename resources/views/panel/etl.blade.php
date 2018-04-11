@extends('panel.layout')
@section('dashboard-content')

<div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
    <h1 class="display-4">ETL</h1>
    <p class="lead">El significado de ETL es <em>Extraction</em>, <em>Transform</em> and <em>Load</em>, 
        que traducido es Extracción, Transformación y Carga. <br>
        A continuación harás el proceso de ETL.
    </p>
</div>
<div class="offset-md-3 col-md-6 offset-sm-2 col-sm-8 col-12">
    <div class="card mb-4 box-shadow">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">Comenzar ETL</h4>
        </div>
        <div class="card-body">
            <ul class="">
                <li>Importará todas las tablas al <b>DataWareHouse</b>.</li>
                <li>Los campos o registros erroneos serán mostrados para su modificación.</li>
                <li>El proceso quedará registrado a nombre de <b>{{ $user->name }}</b></.li>
                </ul>
                <a href="/etl/begin" ><button type="button" class="btn btn-lg btn-block btn-outline-primary">Comenzar el proceso</button></a>
            </div>
        </div>
        <a href="/etl/clean">Reiniciar ETL</a>
    </div>
    @endsection