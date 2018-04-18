@extends('panel.layout')
@section('additional-css')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
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
    #card-changes, #card-general{
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
    
    .text-custom{
        font-size: 18px;
    }
    .hidden{
        display: none;
    }
</style>
@endsection
@section('dashboard-content')
<div id="check">
    <h3> Lista de correcciones </h3>
    <div class="alert alert-info" role="alert">
        <h4>Atención</h4>
        <ul>
            <li>Los campos con fondo amarillo son los modificados de manera automática.</li>
            <li>Puedes acceder a la información de cambios de los campos haciendo click en ellos.</li>
            <li>Al hacer click en las "cabeceras" podrás editar todos los campos (Sólo los editables).</li>
            <li>Los botones de la columna Acción son cambios reflejados directamente en el DataWareHouse, ten mucho cuidado.</li>
            <ul>
                <li>El ícono <i class="fa fa-save"></i>, es un guardado directo.</li>
                <li>El ícono <i class="fa fa-times"></i>, es un eliminado definitivo, no se enviará a la base de datos.</li>
            </ul>
            <li>FAQ</li>
            <ul>
                <li>Puedes retirarte y cerrar sesión cuando quieras, el ETL seguirá en el punto que lo hayas dejado, no necesitas hacer click en ningún botón, siempre te llevamos el paso.</li>
            </ul>
        </ul>
    </div>
    <div class="card mb-4 box-shadow mx-auto w-50">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">Guarda los cambios.</h4>
        </div>
        <div class="card-body">
            <div class="my-2">Cuando termines pasarás a la última fase, la corrección de errores que un algoritmo no puede calcular o "adivinar", es decir, datos externos al sistema propio.</div>
            <button @click="finish" class="btn btn-success w-100 py-4"><h5><i class="fa fa-database"></i> Enviar cambios al DataWareHouse</h5></button>
        </div>
    </div>
    <div>
        @foreach ($tables as $key => $table)
        @if($table['data'] != null)
        <h3>{{ $table['table_name'] }}</h3>
        <table id="freeze-{{ $key }}" class="table table-bordered table-hover table-light">
            <thead class="thead-light">
                {{-- <th>Guardar</th> --}}
                @foreach($table['headers'] as $index => $header)
                <th class="cursor-pointer" scope="col" @click="edit_all" table="{{ $key }}" field="{{ $table['indexes'][$index] }}"> {{ $header }} </th>
                @endforeach
                <th>Acciones</th>
            </thead>
            <tbody>
                @foreach ($table['data'] as $id => $row)
                <tr id="{{ $key.'-'.$id }}">
                    {{-- <td width="10px">
                        <input class="form-check" type="checkbox" name="carga_gas_{{ $row['id'] }}" id="carga_gas_{{ $row['id'] }}" checked="checked" />
                    </td> --}}
                    @foreach ($table['indexes'] as $index)
                    <td 
                    @click="{{ in_array($index, $row['field']) ? 'show_actions' : '' }}" 
                    table="{{ $key }}"
                    field="{{ $index }}"
                    row_id="{{ $id }}"
                    original="{{ in_array($index, $row['field']) ? $row['original'][array_search($index, $row['field'])] : '' }}" 
                    current="{{ $row[$index] }}" 
                    suggest="{{ $row[$index] }}"
                    {{ in_array($index, $row['field']) ? 'general_id='.$key.'-'.$index : '' }}
                    class="{{ in_array($index, $row['field']) ? 'bg-warning cursor-pointer' : '' }}"
                    > {{ $row[$index] }} </td>
                    @endforeach
                    <td width="10px">
                        <button  @click="send_dwh" row="{{ $key.'-'.$id }}" class="btn btn-success px-4"><i row="{{ $key.'-'.$id }}" class="fa fa-download"></i></button>
                        <button  @click="delete_dwh" row="{{ $key.'-'.$id }}" class="btn btn-danger px-4"><i row="{{ $key.'-'.$id }}" class="fa fa-times"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        @endforeach
        <div id="fg-wall" class="text-center"></div>
        <div id="card-changes">
            <div class="row">
                <div class="card text-center mx-auto mt-5 col-md-6 col-sm-5" style="opacity: 1;">
                    <div class="card-body">
                        <h2 class="card-title">Cambiar valor</h2>
                        <p class="card-text text-custom"> 
                            Valor que contenía: @{{ original }} <br />
                            Valor sugerido: @{{ suggest }} <br />
                            Valor actual: <input id="new_value" class="form-control w-50 mx-auto" :type="input_type" v-model:value="current"><br />
                        </p>
                        <button id="close-etl-btn" @click="close_card()" class="mt-3 btn btn-outline-danger"><i class="fa fa-times"></i> Cancelar</button>
                        <button id="close-etl-btn" @click="save()" class="mt-3 btn btn-outline-primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="card-general">
            <div class="row">
                <div class="card text-center mx-auto mt-5 col-md-6 col-sm-5" style="opacity: 1;">
                    <div class="card-body">
                        <h2 class="card-title">Cambiar valor</h2>
                        <p class="card-text text-custom">
                            Nuevo valor: <input id="general_new_value" class="form-control w-50 mx-auto" :type="general_input_type" v-model:value="general_current"><br />
                        </p>
                        <button id="close-etl-btn" @click="close_card()" class="mt-3 btn btn-outline-danger"><i class="fa fa-times"></i> Cancelar</button>
                        <button id="close-etl-btn" @click="save_all()" class="mt-3 btn btn-outline-primary"><i class="fa fa-save"></i> Guardar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('additional-js')
<script>
    let app = new Vue({
        el: '#check',
        data: {
            original: '',
            current: '',
            suggest: '',
            table: '',
            field: '',
            row_id: '',
            id: '',
            input_type: 'text',
            current_field: null,
            
            // Genearl
            general_table_name: '',
            general_current: '',
            general_field: '',
            general_input_type: '',
        },
        methods: {
            edit_all: function (event) {
                document.getElementById('general_new_value').focus()
                this.general_current = ''
                this.general_table_name = event.target.getAttribute('table')
                this.general_field = event.target.getAttribute('field')
                if(this.general_field.includes('fecha') || this.general_field.includes('creado')){
                    this.general_input_type = 'date'
                }else if(this.general_field.includes('hora')){
                    this.general_input_type = 'time'
                }else {
                    this.general_input_type = 'text'
                }
                
                this.show_bg()
                this.show_general()
            },
            save_all: function () {
                this.loading()
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post({
                    url: '{{ URL::to("/etl/check/change_all") }}', 
                    type: 'POST',
                    data: {
                        'table': app.general_table_name,
                        'field': app.general_field,
                        'data': app.general_current
                    },
                    success: (result) => {
                        $("td[general_id='" + this.general_table_name + "-" + this.general_field +"']").html(this.general_current)
                        swal({
                            title: 'Éxito...',
                            text: 'EL campo fue modificado y ahora todos contienen: ' + this.general_current,
                            type: 'success'
                        })
                    },
                    error: (error) => {
                        swal({
                            title: 'Algo salió mal',
                            text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                            type: 'error',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        })
                    }
                });
                this.close_card()
            },
            save: function () {
                this.loading()
                this.close_card()
                // Allows sending AJAX queries to Laravel
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.post({
                    url: '{{ URL::to("/etl/check/change") }}', 
                    type: 'POST',
                    data: {
                        'table': this.table,
                        'field': this.field,
                        'data': this.current,
                        'id': this.row_id,
                    },
                    success: (result) => {
                        this.current_field.setAttribute('current', this.current)
                        this.current_field.innerHTML = this.current
                        swal({
                            title: 'Éxito...',
                            text: 'EL campo fue modificado',
                            type: 'success'
                        })
                    },
                    error: (error) => {
                        console.log(error)
                        swal({
                            title: 'Algo salió mal',
                            text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                            type: 'error'
                        })
                    }
                });
            },
            show_actions: function (event) {
                document.getElementById('new_value').focus()
                this.current_field = event.target;
                this.original = this.current_field.getAttribute('original')
                if(this.original == '' || this.original == null) this.original = 'NULL'
                this.current = this.current_field.getAttribute('current')
                this.suggest = this.current_field.getAttribute('suggest')
                this.table = this.current_field.getAttribute('table')
                this.field = this.current_field.getAttribute('field')
                this.row_id = this.current_field.getAttribute('row_id')
                if(this.field.includes('fecha') || this.field.includes('creado')){
                    this.input_type = 'date'
                }else if(this.field.includes('hora')){
                    this.input_type = 'time'
                }else {
                    this.input_type = 'text'
                }
                this.show_bg()
                this.show_card()
            },
            close_card: function () {
                this.hide_bg()
                this.hide_card()
                this.hide_general()
            },
            delete_dwh: function(event) {
                id          = '#' + event.target.getAttribute('row')
                data        = event.target.getAttribute('row').split('-')
                this.table  = data[0]
                this.row_id = data[1]
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                swal({
                    title: 'Eliminarás un registro',
                    text: "No podrás recuperarlo, ¿deseas continuar?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.value) {
                        this.loading()
                        $.post({
                            url: '{{ URL::to('/etl/check/delete') }}', 
                            type: 'POST',
                            data: {
                                'table': this.table,
                                'id': this.row_id,
                            },
                            success: (result) => {
                                $(id).addClass('animated zoomOutLeft')
                                setTimeout(() => {
                                    $(id).addClass('hidden')
                                },800)
                                setTimeout(() => {
                                    swal({
                                        title: 'Se eliminó',
                                        text: 'EL campo no se enviará al DataWareHouse, ni aparecerá en la lista de errores.',
                                        type: 'success'
                                    })
                                },800)
                            },
                            error: (error) => {
                                swal({
                                    title: 'Algo salió mal',
                                    text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                                    type: 'error'
                                })
                            }
                        });
                    }
                })
                
            },
            send_dwh: function (event) {
                id          = '#' + event.target.getAttribute('row')
                data        = event.target.getAttribute('row').split('-')
                this.table  = data[0]
                this.row_id = data[1]
                
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                swal({
                    title: 'Enviarás un registro al DataWareHosue',
                    text: "Ten cuidado con lo que haces, ¿deseas continuar?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar'
                }).then((result) => {
                    if (result.value) {
                        this.loading()
                        $.post({
                            url: '{{ URL::to('/etl/check/send') }}', 
                            type: 'POST',
                            data: {
                                'table': this.table,
                                'id': this.row_id,
                            },
                            success: (result) => {
                                $(id).addClass('animated zoomOutRight')
                                setTimeout(() => {
                                    $(id).addClass('hidden')
                                },800)
                                setTimeout(() => {
                                    swal({
                                        title: 'Se envió',
                                        text: 'EL campo se envió al DataWareHouse, podrás encontrarlo ahí.',
                                        type: 'success'
                                    })
                                },800)
                            },
                            error: (error) => {
                                swal({
                                    title: 'Algo salió mal',
                                    text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                                    type: 'error'
                                })
                            }
                        });
                    }
                })
            },
            finish: () => {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                swal({
                    title: 'Terminar',
                    text: "Siempre es recomendable una segunda revisión, ya que una vez enviados, no hay vuelta atrás, ¿deseas continuar?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#1e7e34',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, enviar'
                }).then((result) => {
                    if (result.value) {
                        app.loading()
                        $.post({
                            url: '{{ URL::to('/etl/check/send-all') }}', 
                            type: 'POST',
                            data: {
                                'table': this.table,
                                'id': this.row_id,
                            },
                            success: (result) => {
                                setTimeout(() => {
                                    swal({
                                        title: 'Se enviaron los registros',
                                        text: 'Los registros se enviaron al DataWareHouse, podrás encontrarlos ahí.',
                                        type: 'success'
                                    }).then((result) => {
                                        if(result.value){
                                            window.location = '{{ URL::to("/etl/corrections") }}'
                                        }
                                    })
                                },800)
                            },
                            error: (error) => {
                                swal({
                                    title: 'Algo salió mal',
                                    html: 'Error: <code>'+jQuery.parseJSON(error.responseText).message+'</code>',
                                    type: 'error'
                                })
                            }
                        });
                    }
                })
            },
            
            // Misc methods
            show_bg: () => {
                $('#fg-wall').css('top', '0%');
            },
            hide_bg: () => {
                $('#fg-wall').css('top', '100%');
            },
            show_card: () => {
                this.current = ''
                $('#card-changes').html()
                $('#card-changes').css('top', '0');
                $('#card-changes').css('opacity', "1");
            },
            hide_card: () => {
                $('#card-changes').css('top', '100%');
                $('#card-changes').css('opacity', "0");
            },
            show_general: () => {
                $('#card-general').css('top', '0');
                $('#card-general').css('opacity', "1");
            },
            hide_general: () => {
                $('#card-general').css('top', '100%');
                $('#card-general').css('opacity', "0");
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
<script>
    
    @foreach($tables as $key => $table)
    $("#freeze-{{ $key }}").freezeHeader({offset : '40px'});
    @endforeach
</script>
@endsection