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
<h3> Lista de correcciones </h3>
<div class="alert alert-info" role="alert">
    <h4>Atención</h4>
    <ul>
        <li>Los campos con fondo amarillo son los modificados de manera automática.</li>
        <li>Puedes acceder a la información de cambios de los campos haciendo click en ellos.</li>
        <li>Al hacer click en las "cabeceras" podrás editar todos los campos (Sólo los editables).</li>
    </ul>
</div>
<div id="check">
    @foreach ($tables as $key => $table)
    <h3>{{ $table['table_name'] }}</h3>
    <table id="freeze-{{ $key }}" class="table table-hover table-light">
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
                    <button  @click="send_dwh" id="set-{{ $key.'-'.$id }}" class="btn btn-success"><i class="fa fa-download"></i> Enviar a DWH</button>
                    <button  @click="delete_dwh" id="drop-{{ $key.'-'.$id }}" class="btn btn-danger"><i class="fa fa-times"></i> Eliminar</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
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
                        Valor actual: <input class="form-control w-50 mx-auto" :type="input_type" v-model:value="current"><br />
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
                        Nuevo valor: <input class="form-control w-50 mx-auto" :type="general_input_type" v-model:value="general_current"><br />
                    </p>
                    <button id="close-etl-btn" @click="close_card()" class="mt-3 btn btn-outline-danger"><i class="fa fa-times"></i> Cancelar</button>
                    <button id="close-etl-btn" @click="send_all()" class="mt-3 btn btn-outline-primary"><i class="fa fa-save"></i> Guardar</button>
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
            send_all: function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajaxSetup({async: false});
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
                            title: 'Bien hecho',
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
            show_actions: function (event) {
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
            save: function () {
                this.close_card()
                // Allows sending AJAX queries to Laravel
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajaxSetup({async: false});
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
                            title: 'Bien hecho',
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
            delete_dwh: function(event) {
                id = '#' + event.target.getAttribute('id').replace('drop-','')
                $(id).addClass('hidden')
                $.post({
                    url: '{{ URL::to('/etl/check/delete/') }}', 
                    type: 'POST',
                    data: {
                        'table': this.table,
                        'field': this.field,
                        'data': this.current,
                        'id': this.row_id,
                    },
                    success: (result) => {
                        swal({
                            title: 'Se eliminó',
                            text: 'EL campo no se envió al DataWareHouse',
                            type: 'success'
                        })
                    },
                    error: (error) => {
                        swal({
                            title: 'Algo salió mal',
                            text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                            type: 'error'
                        })
                    }
                });
            },
            send_dwh: () => {
                
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