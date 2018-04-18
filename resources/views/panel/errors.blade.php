@extends('panel.layout')
@section('additional-css')
<style>
    
</style>
@endsection
@section('dashboard-content')
<div class="card mb-4 box-shadow mx-auto w-50">
    <div class="card-header">
        <h4 class="my-0 font-weight-normal"> {{ $error_quantity }}/{{ $error_quantity_total }} errores </h4>
    </div>
    <div class="card-body">
        Haz click para ir a la corrección.
        @if($error_quantity == $error_quantity_total)
            <button @click="{{ $auto_fix ? 'auto_correct()' : 'correct()' }}" id="corrections-btn" type="button" class="mt-2 btn btn-lg btn-block btn-outline-success" {{ $error_quantity == 0 ? 'disabled="disabled"' : '' }}>Comenzar el proceso de corrección</button>
        @else
            <button @click="correct()" type="button" class="mt-2 btn btn-lg btn-block btn-outline-success">Correcciones</button>
        @endif
    </div>
</div>
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
@endsection
@section('additional-js')
<script>
    $("#table-head-freeze").freezeHeader({offset : '40px'});
    let card = new Vue({
        el: ".card",
        data: {
            
        },
        methods: {
            correct: function(){
                window.location = '{{ URL::to("/etl/corrections") }}';
            },
            auto_correct: function(){
                swal({
                    title: 'Hay errores que se pueden corregir de manera automática.',
                    text: "¿Deseas corregirlos ahora?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, corregir',
                    cancelButtonText: 'No, no corregir',
                    confirmButtonClass: 'btn btn-success',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false,
                    reverseButtons: true
                }).then((result) => {
                    if (result.value) {
                        // Corregidos
                        $.ajaxSetup({async:true})
                        this.loading()
                        $.ajax({
                            url: '{{ URL::to("/etl/do/auto-fix") }}',
                            success: (result) => {
                                swal({
                                    title: 'Listo',
                                    text: 'Ahora sólo debes aceptar los cambions.',
                                    type: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'Revisar',
                                    confirmButtonClass: 'btn btn-success',
                                    buttonsStyling: false,
                                    showCancelButton: false,
                                }).then((result) => {
                                    if (result.value) {
                                        window.location = '{{ URL::to("/etl/check") }}';
                                    }
                                })
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