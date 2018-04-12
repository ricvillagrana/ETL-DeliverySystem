@extends('panel.layout')
@section('dashboard-content')
<h3>Dashboard</h3>
<p>Usamos datos de las siguientes APIs:</p>
<div class="row">
    <div class="col-md-12">
        <canvas id="tables-chart" width="400" height="120"></canvas>
    </div>
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
@section('additional-js')
<script>
    var ctx = document.getElementById("tables-chart");
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
                "Envíos", 
                "Vehículo Día",
                "Envíos vehículo día",
                "Carga gas",
                "Devoluciones",
                "Órdenes",
                "Conductores",
            ],
            datasets: [{
                label: '# de registros',
                data: [
                    {{ $count['envios'] }},
                    {{ $count['vehiculo_dia'] }},
                    {{ $count['envio_vehiculo_dia'] }},
                    {{ $count['carga_gas'] }},
                    {{ $count['devoluciones'] }},
                    {{ $count['ordenes'] }},
                    {{ $count['conductores'] }},
                ],
                borderColor: [
                '#007bff',
                ],
                borderWidth: 1
            },
            {
                label: '# de campos con error',
                data: [
                    {{ $count['error_envios'] }},
                    {{ $count['error_vehiculo_dia'] }},
                    {{ $count['error_envio_vehiculo_dia'] }},
                    {{ $count['error_carga_gas'] }},
                    {{ $count['error_devoluciones'] }},
                    {{ $count['error_ordenes'] }},
                    {{ $count['error_conductores'] }},
                ],
                borderColor: [
                '#dc3545',
                ],
                borderWidth: 1
            },
            ]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        },
        animation: {
            duration: 2000
        }
    });
</script>
@endsection