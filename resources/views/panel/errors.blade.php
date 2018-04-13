@extends('panel.layout')
@section('additional-css')
<style>
    
</style>
@endsection
@section('dashboard-content')
<h3> Errores: {{ $error_quantity }} </h3>
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