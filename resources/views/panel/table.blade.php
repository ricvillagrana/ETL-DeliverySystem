@extends('panel.layout')
@section('dashboard-content')
<h3> {{ $tableName }} </h3>
<table class="table table-hover table-light">
    <thead>
        @foreach ($columns as $column)
            <th scope="col"> {{ $column }} </th>
        @endforeach
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                @foreach ($params as $param)
                <td> {{ $row[$param] }} </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
    
</table>
@endsection