@extends('panel.layout')
@section('dashboard-content')
<pre class="prettyprint">{{ json_encode(Session::get('errors'), JSON_PRETTY_PRINT) }}</ class="prettyprint">
@endsection