@extends('panel.layout')
@section('additional-css')
<style>
    .cursor-pointer{cursor: pointer;}
</style>
@endsection
@section('dashboard-content')

<div class="ui card mx-auto w-50 text-center">
    <div class="content">
        <div class="header">{{ $user->name }}</div>
        <div class="meta">{{ '@'.$user->username }}</div>
        <div class="description">
            Email: {{ $user->email }} <br />
            Se unió el {{ \App\Misc::fancy_date($user->created_at) }}
            <div class="row">
                <div class="col">
                    <div class="ui statistic">
                        <div class="value">
                            {{ \App\Etl::where('id_user', $user->id)->get()->count() }} 
                        </div>
                        <div class="label">
                            ETLs realizados
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="ui statistic">
                        <div class="value">
                            {{ (\DB::select('SELECT count(*) num FROM errors e join etls t on e.etl = t.id join users u on t.id_user = u.id where u.id = '.$user->id.' AND e.deleted = true'))[0]->num }} 
                        </div>
                        <div class="label">
                            Correcciones
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="extra content">
            <table class="ui unstackable table w-50 mx-auto my-4">
                <thead>
                    <th>Privilegios</th>
                </thead>
                <tbody>
                    @foreach (\App\Privilege::of($user->id) as $privilege)
                    <tr>
                        <td>{{ $privilege->name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <h2 class="title">Historial de acciones</h2>
            <table class="ui compact table">
                <thead>
                    <th>Tabla</th>
                    <th>Número</th>
                    <th>Campo</th>
                    <th>Valor anterior</th>
                    <th>Nuevo valor</th>
                    <th>Fecha de acción</th>
                </thead>
                <tbody>
                    @foreach(\App\Error::from($user->id) as $error)
                    <tr>
                        <td>{{ $error->table }}</td>
                        <td>{{ $error->id_error }}</td>
                        <td>{{ $error->field }}</td>
                        <td>{{ $error->original }}</td>
                        <td>{{ (\DB::select('SELECT '.$error->field.' as data FROM '.$error->table.' where id = '.$error->id_error)[0])->data }}</td>
                        <td>{{ \App\Misc::fancy_date($error->updated_at) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
@section('additional-js')

<script>
    $('.popup').popup();
    $('.ui.dropdown').dropdown();
</script>
@endsection