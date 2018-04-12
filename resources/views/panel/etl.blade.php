@extends('panel.layout')
@section('additional-css')
<style>
    #fg-wall{
        position: fixed;
        width: 100%;
        height: 100%;
        background: black;
        opacity: 0.8;
        margin: 0;
        top: 100%;
        left: 0;
        z-index: 9998;
        transition-duration: 0s;
    }
    #content-etl-process{
        position: fixed;
        width: 100%;
        height: 100%;
        margin: 0;
        top: 100%;
        left: 0;
        opacity: 0;
        z-index: 9999;
        transition-duration: .4s;
    }
    .loader {
        border: 16px solid #f3f3f3; /* Light grey */
        border-top: 16px solid #007bff; /* Blue */
        border-bottom: 16px solid #007bff; /* Blue */
        border-radius: 50%;
        width: 100px;
        height: 100px;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection
@section('dashboard-content')
<div id="app-etl">
    <div class="pricing-header px-3 py-3 pt-md-5 pb-md-4 mx-auto text-center">
        <h1 class="display-4">ETL</h1>
        <p class="lead">El significado de ETL es <em>Extraction</em>, <em>Transform</em> and <em>Load</em>, 
            que traducido es Extracción, Transformación y Carga. <br>
            A continuación harás el proceso de ETL.
        </p>
    </div>
    <div class="offset-md-3 col-md-6 offset-sm-2 col-sm-8 col-12">
        <div class="card mb-4 box-shadow">
            <div class="card-header">
                <h4 class="my-0 font-weight-normal">Comenzar ETL</h4>
            </div>
            <div class="card-body">
                <ul>
                    <li>Importará todas las tablas al <b>DataWareHouse</b>.</li>
                    <li>Los campos o registros erroneos serán mostrados para su modificación.</li>
                    <li>El proceso quedará registrado a nombre de <b>{{ $user->name }}</b>.</li>
                </ul>
                <button @click="etl_begin()" id="etl-begin" type="button" class="btn btn-lg btn-block btn-outline-primary">Comenzar el proceso</button>
            </div>
        </div>
        <button @click="etl_reset()" class="btn btn-outline-danger">Reiniciar ETL</button>
    </div>
    <div id="fg-wall" class="text-center"></div>
    <div id="content-etl-process">
        <div class="card text-center mx-auto mt-5" style="width: 40rem; opacity: 1;">
            <div class="card-body">
                <h2 class="card-title">Proceso ETL</h2>
                <p class="card-text"> 
                    <div id="progress-wheel" style="display:block;"><p class="loader mx-auto"></p></div>
                    <div id="progress-done" style="display:none;">
                        <img width="100px" height="100px" src="{{URL::asset('img/checked.png')}}">
                    </div>
                    <p> @{{ percentage }} </p>
                    @{{ message }} 
                </p>
                <div class="progress mb-4">
                    <div id="etl-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                </div>
                <a href="#" id="next-etl-btn" style="display:none;" class="btn btn-success w-50 mx-auto">Corregir errores</a>
                <button id="close-etl-btn" @click="etl_finish()" style="display:none;" class="mt-3 btn btn-outline-secondary w-25 mx-auto"> Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('additional-js')
<script>
    let etl = new Vue({
        el: '#app-etl',
        
        data: {
            message: 'Comenzando el proceso...',
            percentage: '0%',
            errors: 0,
        },
        
        methods: {
            etl_reset: function(){
                // Enable etl-begin button
                $("#etl-begin").prop('disabled', false) ;
                // show buttons
                $('#next-etl-btn').css({display: "none"});
                $('#close-etl-btn').css({display: "none"});
                // Show done image
                $('#progress-wheel').css({display: "block"});
                $('#progress-done').css({display: "none"});
                // Set progressbar to 0%
                etl.percentage = '0%'
                $('#etl-progress-bar').css('width', etl.percentage)
                // Set progressbar primary
                $('#etl-progress-bar').removeClass('bg-success')
                // Cleaning tables
                $.ajaxSetup({async: false});
                $.ajax({url: '{{ URL::to('/etl/clean') }}', success: function(result){
                    swal(
                    'ETL reiniciado',
                    'Todos tus movimientos que no estaban fríamente calculados fueron deshechos',
                    'success'
                    )
                }});
            },
            etl_begin: function(){
                $('#fg-wall').css('top', '0');
                $('#content-etl-process').css('top', '0');
                $('#content-etl-process').css('opacity', "1");
                etl.message = 'Preparando Fuentes de Datos para ETL...'
                setTimeout(function(){
                    etl.message = 'Proceso de ETL en marcha...'
                    etl.percentage = '22%'
                    $('#etl-progress-bar').css('width', etl.percentage)
                    setTimeout(function(){
                        $.ajaxSetup({async: false});
                        $.ajax({url: '{{ URL::to('/etl/begin') }}', success: function(result){
                            etl.percentage = '40%'
                            $('#etl-progress-bar').css('width', etl.percentage)
                            etl.message = 'Proceso de ETL terminado con éxito. Resultado: ' + result + ' errores.'
                            etl.errors = result
                        }});
                        etl.percentage = '50%'
                        $('#etl-progress-bar').css('width', etl.percentage)
                        setTimeout(function(){
                            etl.message = 'Recabando errores...'
                            etl.percentage = '68%'
                            $('#etl-progress-bar').css('width', etl.percentage)
                            setTimeout(function(){
                                etl.message = 'Almacenando errores...'
                                etl.percentage = '86%'
                                $('#etl-progress-bar').css('width', etl.percentage)
                                setTimeout(function(){
                                    etl.message = 'Proceso finalizado. ' + etl.errors + ' errores encontrados.'
                                    // show buttons
                                    $('#next-etl-btn').css({display: "block"});
                                    $('#close-etl-btn').css({display: "block"});
                                    // Show done image
                                    $('#progress-wheel').css({display: "none"});
                                    $('#progress-done').css({display: "block"});
                                    // Set progressbar to 100%
                                    etl.percentage = '100%'
                                    $('#etl-progress-bar').css('width', etl.percentage)
                                    // Set progressbar Green
                                    $('#etl-progress-bar').addClass('bg-success')
                                    // Disable etl-begin button
                                    $("#etl-begin").prop('disabled', true) ;
                                },2000);
                            },1000);
                        },2500);
                    },500);
                }, 1000)
            },
            etl_finish: function(){
                $('#fg-wall').css('top', "100%");
                $('#content-etl-process').css('top', "100%");
                $('#content-etl-process').css('opacity', "0");
            }
        }
    })
</script>
@endsection