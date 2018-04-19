@extends('panel.layout')
@section('dashboard-content')
<h3>Dashboard</h3>
<p>Puedes ver la sanidad de los datos:</p>

<div class="row">
    <div class="col-md-10 offset-1">
        <canvas id="percentage-chart" width="400" height="120"></canvas>
    </div>
    <div class="col-md-10 offset-1">
        <canvas id="tables-chart" width="400" height="120"></canvas>
    </div>  
</div>

@endsection
@section('additional-js')
<script>
    var doughnut = document.getElementById("percentage-chart");
    var myDoughnutChart = new Chart(doughnut, {
        type: 'radar',
        data: {
            labels: [
            "Carga gas",
            "Conductores",
            "Devoluciones",
            "Envíos", 
            "Envíos vehículo día",
            "Órdenes",
            "Vehículo Día",
            ],
            datasets: [{
                label: 'Registros sanos',
                data: [
                {{ $count['carga_gas'] }},
                {{ $count['conductores'] }},
                {{ $count['devoluciones'] }},
                {{ $count['envios'] }},
                {{ $count['envio_vehiculo_dia'] }},
                {{ $count['ordenes'] }},
                {{ $count['vehiculo_dia'] }},
                ],
                borderColor: [
                '#4594f2',
                ],
                borderWidth: 3
            },
            {
                label: 'Errores (En BD Dummie)',
                data: [
                {{ $count['error_carga_gas'] }},
                {{ $count['error_conductores'] }},
                {{ $count['error_devoluciones'] }},
                {{ $count['error_envios'] }},
                {{ $count['error_envio_vehiculo_dia'] }},
                {{ $count['error_ordenes'] }},
                {{ $count['error_vehiculo_dia'] }},
                ],
                borderColor: [
                '#ff4570',
                ],
                borderWidth: 3
            },
            ]
        },
        options: {
        },
        animation: {
            duration: 2000
        }
    });
    var ctx = document.getElementById("tables-chart");
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [
            "Carga gas",
            "Conductores",
            "Devoluciones",
            "Envíos", 
            "Envíos vehículo día",
            "Órdenes",
            "Vehículo Día",
            ],
            datasets: [{
                label: 'Registros sanos',
                data: [
                {{ $count['carga_gas'] }},
                {{ $count['conductores'] }},
                {{ $count['devoluciones'] }},
                {{ $count['envios'] }},
                {{ $count['envio_vehiculo_dia'] }},
                {{ $count['ordenes'] }},
                {{ $count['vehiculo_dia'] }},
                ],
                borderColor: [
                '#4594f2',
                ],
                borderWidth: 3
            },
            {
                label: 'Errores (En BD Dummie)',
                data: [
                {{ $count['error_carga_gas'] }},
                {{ $count['error_conductores'] }},
                {{ $count['error_devoluciones'] }},
                {{ $count['error_envios'] }},
                {{ $count['error_envio_vehiculo_dia'] }},
                {{ $count['error_ordenes'] }},
                {{ $count['error_vehiculo_dia'] }},
                ],
                borderColor: [
                '#ff4570',
                ],
                borderWidth: 3
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