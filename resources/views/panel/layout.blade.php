@extends('layouts/bootstrap')
@section('title')
Dashboard
@endsection
@section('css')
<style type="text/css">/* Chart.js */
    a:hover {text-decoration: none;}
    body {
        font-size: .875rem;
    }
    
    .feather {
        width: 16px;
        height: 16px;
        vertical-align: text-bottom;
    }
    
    /*
    * Sidebar
    */
    
    .sidebar {
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 100; /* Behind the navbar */
        padding: 0;
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
    }
    
    .sidebar-sticky {
        position: -webkit-sticky;
        position: sticky;
        top: 48px; /* Height of navbar */
        height: calc(100vh - 48px);
        padding-top: .5rem;
        overflow-x: hidden;
        overflow-y: auto; /* Scrollable contents if viewport is shorter than content. */
    }
    
    .sidebar .nav-link {
        font-weight: 500;
        color: #333;
    }
    
    .sidebar .nav-link .feather {
        margin-right: 4px;
        color: #999;
    }
    
    .sidebar .nav-link.active {
        color: #007bff;
    }
    
    .sidebar .nav-link:hover .feather,
    .sidebar .nav-link.active .feather {
        color: inherit;
    }
    
    .sidebar-heading {
        font-size: .75rem;
        text-transform: uppercase;
    }
    
    /*
    * Navbar
    */
    
    .navbar-brand {
        padding-top: .75rem;
        padding-bottom: .75rem;
        font-size: 1rem;
        background-color: rgba(0, 0, 0, .25);
        box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25);
    }
    
    .navbar .form-control {
        padding: .75rem 1rem;
        border-width: 0;
        border-radius: 0;
    }
    
    .form-control-dark {
        color: #fff;
        background-color: rgba(255, 255, 255, .1);
        border-color: rgba(255, 255, 255, .1);
    }
    
    .form-control-dark:focus {
        border-color: transparent;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
    }
    
    /*
    * Utilities
    */
    
    .border-top { border-top: 1px solid #e5e5e5; }
    .border-bottom { border-bottom: 1px solid #e5e5e5; }
    .box-shadow { box-shadow: 0 .25rem .75rem rgba(0, 0, 0, .05); }
    .margining { margin: 5px;}
</style>
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

<nav class="col-md-2 d-none d-md-block bg-light sidebar">
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
                <span><i class="fa fa-user-o"></i> Data Ware House</span>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/envios"><i class="fa fa-truck"></i> Envíos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/vehiculo-dia"><i class="fa fa-cube"></i> Vehiculos día</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/envio-vehiculo-dia"><i class="fa fa-cubes"></i> Envíos vehiculo día</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/carga-gas"><i class="fa fa-filter"></i> Carga gas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/devoluciones"><i class="fa fa-undo"></i> Devoluciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/ordenes"><i class="fa fa-list-ul"></i> Órdenes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/dwh/conductores"><i class="fa fa-users"></i> Conductores</a>
                </li>
            </ul>
        </h6>
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                <span><i class="fa fa-user-o"></i> Fuentes de datos (APIs)</span>
            </h6>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/envios"><i class="fa fa-truck"></i> Envíos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/vehiculo-dia"><i class="fa fa-cube"></i> Vehiculos día</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/envio-vehiculo-dia"><i class="fa fa-cubes"></i> Envíos vehiculo día</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/carga-gas"><i class="fa fa-filter"></i> Carga gas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/devoluciones"><i class="fa fa-undo"></i> Devoluciones</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/ordenes"><i class="fa fa-list-ul"></i> Órdenes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/fuentes-datos/conductores"><i class="fa fa-users"></i> Conductores</a>
                </li>
            </ul>
        </h6>
    </div>
</nav>
<div class="offset-md-2 col-md-10 col-12">
    <br>
    @yield('dashboard-content')
</div>
@endsection
@section('js')
<script src="/js/app.js"></script>
<script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
<script src="https://unpkg.com/sweetalert2@7.18.0/dist/sweetalert2.all.js"></script>
@yield('additional-js')
@endsection
@section('extra-content')
@yield('extra')
@endsection
