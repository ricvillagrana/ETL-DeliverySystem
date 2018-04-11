@extends('panel.layout')
@section('dashboard-content')
<h3>Dashboard</h3>
<p>Usamos datos de las siguientes APIs:</p>
<div class="row">
    @foreach ($sources as $source)
    <div class="col-md-3 col-sm-6 col-12 card box-shadow">
        <div class="card-body">
            <h6 class="card-title">{{ $source->database }} / {{ $source->name }}</h6>
            <h6 class="card-subtitle mb-2 text-muted">{{ $source->url }}</h6>
            <p class="card-text">{{ $source->description }}</p>
            <a href="{{ $source->url }}" target="_blank" class="btn btn-dark pull-right">Ver datos</a>
        </div>
    </div>
    @endforeach
</div>
@endsection