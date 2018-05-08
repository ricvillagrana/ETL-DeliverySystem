@extends('panel.layout')
@section('additional-css')
<style>
    
</style>
@endsection
@section('dashboard-content')
<div id="errors">
    <div class="card mb-4 box-shadow mx-auto w-50">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal"> Errores </h4>
        </div>
        <div class="card-body mx-auto">
            <div class="ui statistic">
                <div class="value">
                    {{ $error_quantity }}/{{ $error_quantity_total }} 
                </div>
                <div class="label">
                    Errores restantes
                </div>
            </div><br />
            @if($error_quantity == $error_quantity_total)
            <button @click="{{ $auto_fix ? 'auto_correct()' : 'correct()' }}" id="corrections-btn" type="button" class="mt-2 btn btn-lg btn-block btn-outline-success" {{ $error_quantity == 0 ? 'disabled="disabled"' : '' }}>Comenzar el proceso de corrección</button>
            @else
            <button @click="correct()" type="button" class="mt-2 btn btn-lg btn-block btn-outline-success">Correcciones</button>
            @endif
        </div>
    </div>
    <button class="btn btn-info" @click="switch_errors()">@{{ btn_hide_text }}</button>
    <div v-if="errors_visible">
        <h3> Lista de errores </h3>
        <table id="table-head-freeze" class="table table-bordered table-hover table-light">
            <thead class="thead-light">
                <th scope="col"> Tabla en la que está </th>
                <th scope="col"> Número de registro </th>
                <th scope="col"> Campo </th>
                <th scope="col"> Descripción del error </th>
                <th scope="col"> Valor sugerido </th>
            </thead>
            <tbody>
                @foreach ($errors as $error)
                <tr>
                    <td> {{ $error->table }} </td>
                    <td> {{ $error->id_error }} </td>
                    <td> {{ $error->field }} </td>
                    <td> {{ $error->comment }} </td>
                    <td> {{ $error->auto_fix }} </td>
                </tr>
                @endforeach
            </tbody>
            
        </table>
    </div>
</div>
@endsection
@section('additional-js')
<script>
    $("#table-head-freeze").freezeHeader({offset : '40px'});
    let errors = new Vue({
        el: "#errors",
        data: {
            errors_visible: false,
            btn_hide_text: 'Mostrar errores'
        },
        methods: {
            switch_errors: () => {
                console.log('switched to: ' + !errors.errors_visible)
                errors.errors_visible = !errors.errors_visible 
                errors.btn_hide_text = errors.errors_visible ? 'Esconder errores' : 'Mostrar errores'
            },
            correct: function(){
                window.location = '{{ URL::to("/etl/check") }}';
            },
            auto_correct: function(){
                $.ajax({
                    url: '/etl/do/auto-fix',
                    success: (result) => {
                        console.log(result)
                    },
                    error: (error) => {
                        swal({
                            type: 'error',
                            title: 'Algo salió mal...',
                            text: 'Hubo un error mientras se hacían las correcciones, es posible que sea un fallo con el servidor.',
                            footer: "Inténtalo de nuevo después, repórtalo con el desarrollador si es necesario",
                        })
                    }
                });
                swal({
                    title: 'Hay {{ \App\Error::where("auto_fix", "<>", "")->get()->count() }} errores que se corrigieron de manera automática.',
                    text: "¿Deseas enviarlos al DataWareHouse ahora?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'No, no enviar',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        // Corregidos
                        this.loading()
                        $.ajax({
                            url: '/etl/check/send-all',
                            type: 'GET',
                            success: (result) => {
                                this.correct()
                            }
                        })                        
                        console.log('corregido')
                    } else if ( result.dismiss === swal.DismissReason.cancel) {
                        // No corregidos
                        this.correct()
                    }
                })
            },
            loading: () => {
                swal({
                    title: 'Ejecutando...',
                    onOpen: () => {
                        swal.showLoading()
                    }
                })
            }
        }
    });
</script>
@endsection