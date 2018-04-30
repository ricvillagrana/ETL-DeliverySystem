@extends('layouts/bootstrap')
@section('title')
Dashboard
@endsection
@section('css')
<link rel="stylesheet" href="/css/app.css">
@yield('additional-css')
@endsection
@section('content')
<nav class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0">
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="/">BI Costurita</a>
    <span class="px-3">
        <div class="ui pointing dropdown link">
            <span class="item text-light">{{ (session('user'))->name }}<i class="dropdown icon"></i></span>
            <div class="menu">
                <div class="header">General</div>
                <a href="/user/{{ session('user')->username }}" class="item"><i class="fa fa-user"></i> Perfil</a>
                @if(\App\Privilege::checkName(session('user')->id, 'Administrar usuarios'))
                <a href="/users" class="item"><i class="fa fa-users"></i> Administrar usuarios</a>
                <div class="divider"></div>
                <div class="header">Opciones de ETL</div>
                <div onclick="etl_reset();" class="item"><i class="fa fa-refresh"></i> Restaurar</div>
                <a class="item" target="_blank" href="/generateXLS"><i class="fa fa-download"></i> Descargar XLS</a>
                @endif
                <div class="divider"></div>
                <a href="/logout" class="item"><i class="fa fa-sign-out"></i> Cerrar sesión</a>
            </div>
        </div>
    </span>
</nav>

<nav class="col-md-2 d-none d-md-block bg-light sidebar px-0">
    <div class="sidebar-sticky">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span><i class="fa fa-list"></i> Menú</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/etl"><i class="fa fa-linode"></i> ETL</a>
            </li>
            @if(\App\Error::where('solved', '!=', '1')->get()->count() != 0)
            <li class="nav-item">
                <a class="nav-link" href="/etl/errors"><i class="fa fa-exclamation-triangle"></i> Errores <div class="ui tiny label">{{ \App\Error::where('solved', '<>', '1')->get()->count() }}</div></a>
            </li>
            @endif
            @if(\App\Etl::all()->count() != 0 && \App\Error::where('solved', '=', '0')->get()->count() == 0 && \App\Privilege::checkName(session('user')->id, 'Dashboard'))
            <li class="nav-item">
                <a class="nav-link" href="/dashboard"><i class="fa fa-home"></i> Dashboard</a>
            </li>
            @endif
        </ul>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span><i class="fa fa-database"></i> DataWareHouse</span>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/carga-gas"><i class="fa fa-filter"></i> Carga gas <div class="ui tiny label">{{ \App\Sqlsrv\CargaGas::all()->count() }}</div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/conductores"><i class="fa fa-users"></i> Conductores <div class="ui tiny label">{{ \App\Sqlsrv\Empleado::all()->count() }}</div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/devoluciones"><i class="fa fa-undo"></i> Devoluciones <div class="ui tiny label">{{ \App\Sqlsrv\Devoluciones::all()->count() }}</div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/envios"><i class="fa fa-truck"></i> Envíos <div class="ui tiny label">{{ \App\Sqlsrv\Envio::all()->count() }}</div></a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="/dwh/envio-vehiculo-dia"><i class="fa fa-cubes"></i> Envíos vehiculo día <div class="ui tiny label">{{ \App\Sqlsrv\EnvioVehiculoDia::all()->count() }}</div></a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/ordenes"><i class="fa fa-list-ul"></i> Órdenes <div class="ui tiny label">{{ \App\Sqlsrv\Ordenes::all()->count() }}</div></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/vehiculo-dia"><i class="fa fa-cube"></i> Vehiculos día <div class="ui tiny label">{{ \App\Sqlsrv\VehiculoDia::all()->count() }}</div></a>
                </li>
                <li class="nav-item p-3">
                    <a class="btn btn-outline-success mx-auto" target="_blank" href="/generateXLS"><i class="fa fa-download"></i> Descargar XLS</a>
                </li>
            </ul>
        </h6>
    </div>
</nav>
<div class="offset-md-2 col-md-10 col-12 mb-5">
    <br>
    @yield('dashboard-content')
</div>
@endsection
@section('js')
<script src="/js/app.js"></script>
{{-- Semantic UI Loads --}}
<script>
    $('.ui.dropdown').dropdown();
</script>
@yield('additional-js')
@endsection
@section('extra-content')
@yield('extra')
@endsection
