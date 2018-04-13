@extends('panel.layout')
@section('additional-css')
<style>
    
</style>
@endsection
@section('dashboard-content')
<div class="card mb-4 box-shadow mx-auto w-50">
    <div class="card-header">
        <h4 class="my-0 font-weight-normal"> {{ $error_quantity }} errores encontrados </h4>
    </div>
    <div class="card-body">
        Haz click para ir a la corrección.
        <button id="corrections-btn" type="button" class="mt-2 btn btn-lg btn-block btn-outline-success" {{ $error_quantity == 0 ? 'disabled' : '' }}>Comenzar el proceso de corrección</button>
    </div>
</div>
<h3> Lista de errores </h3>
<table id="table-head-freeze" class="table table-hover table-light">
    <thead class="thead-light">
        <th scope="col"> Tabla en la que está </th>
        <th scope="col"> Número de registro </th>
        <th scope="col"> Campo </th>
        <th scope="col"> Descripción del error </th>
    </thead>
    <tbody>
        @foreach ($errors as $error)
        <tr>
            <td> {{ $error->table }} </td>
            <td> {{ $error->id_error }} </td>
            <td> {{ $error->field }} </td>
            <td> {{ $error->comment }} </td>
        </tr>
        @endforeach
    </tbody>
    
</table>
@endsection
@section('additional-js')
<script>
        $(document).ready(function () {
            $("#table-head-freeze").freezeHeader({offset : '40px'});
        });
    </script>
@endsection