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
    @if($total_errors == 0)
    <div class="card mb-4 box-shadow mx-auto w-50">
        <div class="card-header">
            <h4 class="my-0 font-weight-normal">Proceso Finalizado</h4>
        </div>
        <div class="card-body">
            <div class="my-2">Felicitaciones, terminaste el proceso ETL, ahora puedes ir al Dashboard.</div>
            <a href="/"><button class="btn btn-success w-100 py-2"><h5>Dashboard</h5></button></a>
        </div>
    </div>
    @else
    <h3> Lista de correcciones </h3>
    <div class="alert alert-info" role="alert">
        <h4>Atención</h4>
        <ul>
            <li>Los campos con fondo distinto son los que puedes modificar.</li>
            <ul>
                <li>Algunos de ellos fueron (o pueden ser) modificados de manera automática y se marcaron en azul.</li>
                <li>Algunos no pudieron ser calculados para auto-corrección, así que fueron marcados en amarillo.</li>
                <li>Los colores no cambiarán para que tengas claro cuáles son (o eran en caso de haberse modificado) los errores y cuáles las auto-correcciones.</li>
            </ul>
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
                    @if(in_array($index, $row['field']))
                    original="{{ $row['original'][array_search($index, $row['field'])] }}" 
                    current="{{ $row[$index] }}" 
                    suggest="{{ $row['auto_fix'][array_search($index, $row['field'])] }}"
                    comment="{{ $row['comment'][array_search($index, $row['field'])] }}"
                    {{ in_array($index, $row['field']) ? 'general_id='.$key.'-'.$index : '' }}
                    class="bg-info cursor-pointer {{ ($row['auto_fix'][array_search($row['field'], array_keys($row['auto_fix']))] == "" && in_array($index, $row['field'])) ? 'bg-warning' : '' }}"
                    class="{{ $row['solved'] ? 'bg-warning cursor-pointer' : '' }}"
                    @endif
                    > {{ $row[$index] }} </td>
                    @endforeach
                    <td width="10px">
                        <button @click="send_dwh" able-to-send="{{ $row['solved'] != 0 ? 'true' : 'false' }}" id="{{ 'btn-'.$key.'-'.$id }}" row="{{ $key.'-'.$id }}" class="btn btn-success px-4"><i row="{{ $key.'-'.$id }}" id="{{ 'icon-'.$key.'-'.$id }}" able-to-send="{{ $row['solved'] != 0 ? 'true' : 'false' }}" class="fa fa-download"></i></button>
                        <button @click="delete_dwh" row="{{ $key.'-'.$id }}" class="btn btn-danger px-4"><i row="{{ $key.'-'.$id }}" class="fa fa-times"></i></button>
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
                            Comentario: @{{ comment }} <br />
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
    @endif
</div>
@endsection
@section('additional-js')
<script>
    @foreach($tables as $key => $table)
    $("#freeze-{{ $key }}").freezeHeader({offset : '40px'});
    @endforeach
</script>
@endsection