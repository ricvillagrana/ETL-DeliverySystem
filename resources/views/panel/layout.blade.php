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
    <a class="navbar-brand col-sm-3 col-md-2 mr-0" href="#">Costurita ETL</a>
    <ul class="navbar-nav px-3">
        <li class="nav-item text-nowrap">
            <a class="nav-link" href="/logout"><i class="fa fa-sign-out"></i> Cerrar sesión</a>
        </li>
    </ul>
</nav>

<nav class="col-md-2 d-none d-md-block bg-light sidebar px-0">
    <div class="sidebar-sticky">
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span><i class="fa fa-user-o"></i> {{ $user->name }}</span>
        </h6>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="/dashboard"><i class="fa fa-home"></i> Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/etl"><i class="fa fa-download"></i> ETL</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/etl/errors"><i class="fa fa-exclamation-triangle"></i> Errores</a>
            </li>
        </ul>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span><i class="fa fa-user-o"></i> DataWareHouse</span>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/carga-gas"><i class="fa fa-filter"></i> Carga gas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/conductores"><i class="fa fa-users"></i> Conductores</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/devoluciones"><i class="fa fa-undo"></i> Devoluciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/envios"><i class="fa fa-truck"></i> Envíos</a>
                </li>
                {{-- <li class="nav-item">
                    <a class="nav-link" href="/dwh/envio-vehiculo-dia"><i class="fa fa-cubes"></i> Envíos vehiculo día</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/ordenes"><i class="fa fa-list-ul"></i> Órdenes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/vehiculo-dia"><i class="fa fa-cube"></i> Vehiculos día</a>
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
@yield('additional-js')
@endsection
@section('extra-content')
@yield('extra')
@endsection
