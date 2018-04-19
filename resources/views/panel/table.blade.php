@extends('panel.layout')
@section('additional-css')
<style>
    
</style>
@endsection
@section('dashboard-content')
<h3> {{ $tableName }} </h3>
<table id="table-head-freeze" class="table table-bordered table-hover table-light">
    <thead class="thead-light">
        @foreach ($columns as $column)
        <th scope="col"> {{ $column }} </th>
        @endforeach
    </thead>
    <tbody>
        @foreach ($rows as $row)
        <tr>
            @foreach ($params as $param)
            <td class="p-1"> {{ $row[$param] }} </td>
            @endforeach
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