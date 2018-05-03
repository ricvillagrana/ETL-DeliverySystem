@extends('panel.layout')
@section('additional-css')
<style>
    .cursor-pointer{cursor: pointer;}
</style>
@endsection
@section('dashboard-content')
<h3>Administración de usuarios</h3>
<p>Aquí puedes modificar los permisos e incuso eliminar usuarios.</p>

<table id="manage_users" class="ui celled table w-75 mx-auto">
    <thead>
        <tr>
            <th>Usuario</th>
            <th>Nombre</th>
            <th>Correo electrónico</th>
            <th>Fecha de creación</th>
            @foreach(\App\Privilege::all() as $privilege)
            <th>{{ $privilege->name }}</th>
            @endforeach
            <th>Rol</th>
            <th>Más</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr id="row-{{ $user->id }}">
            <td>{{ $user->username }} {{ $user->id == (session('user'))->id ? '(Tú)' : '' }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ \App\Misc::fancy_date($user->created_at) }}</td>
            @foreach(\App\Privilege::all() as $privilege)
            <th @click="swap" 
            {{-- data-content="Concede o revoca {{ $privilege->name }} a {{ $user->name }}" --}} 
            id_user="{{ $user->id }}" 
            id_privilege="{{ $privilege->id }}"
            privilege="{{ \App\Privilege::check($user->id, $privilege->id) ? 'set' : 'unset' }}"
            class="popup center aligned cursor-pointer"><i id="{{ $user->id.'-'.$privilege->id }}" id_user="{{ $user->id }}" id_privilege="{{ $privilege->id }}" class="large {{ \App\Privilege::check($user->id, $privilege->id) ? 'green check' : 'red minus' }} icon"></i></th>
            @endforeach
            <td>
                <div class="ui fluid selection dropdown">
                    <div class="text">{{ \App\User::getRole($user->id) }}</div>
                    <i class="dropdown icon"></i>
                    <div class="menu">
                        @foreach (\App\Role::all() as $role)
                        <div @click="set_role" id="role-{{ $role->id }}" role_id="{{ $role->id }}" user_id="{{ $user->id }}" class="item">{{ $role->name }}</div>
                        @endforeach
                    </div>
                </div>
            </td>
            <td>
                <div class="ui fluid selection dropdown">
                    <div>Opciones</div>
                    <i class="dropdown icon"></i>
                    <div class="menu">
                        <a  href="/user/{{ $user->username }}" class="item text-primary"><i class="fa fa-user"></i> Perfil</a>
                        @if($user->id != (session('user'))->id)
                        <div @click="user_delete" username="{{ $user->username }}" user_id="{{ $user->id }}" class="item text-danger"><i class="fa fa-trash"></i> Eliminar</div>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>



@endsection
@section('additional-js')
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    let manage_users = new Vue({
        el: '#manage_users',
        data: {
            username: ''
        },
        methods: {
            swap: (e) => {
                var uri = (e.target.getAttribute('privilege') == 'unset') ? 'add' : 'remove'
                $.ajax({
                    url: '/user/privilege/' + uri,
                    type: 'POST',
                    data: {
                        'id_user': e.target.getAttribute('id_user'),
                        'id_privilege': e.target.getAttribute('id_privilege')
                    },
                    success: result => {
                        let id = e.target.getAttribute('id_user') + '-' + e.target.getAttribute('id_privilege')
                        if(e.target.getAttribute('privilege') == 'set'){
                            e.target.setAttribute('privilege', 'unset')
                            document.getElementById(id).classList.remove('green')
                            document.getElementById(id).classList.remove('check')
                            document.getElementById(id).classList.add('red')
                            document.getElementById(id).classList.add('minus')
                        }else{
                            e.target.setAttribute('privilege', 'set')
                            document.getElementById(id).classList.remove('red')
                            document.getElementById(id).classList.remove('minus')
                            document.getElementById(id).classList.add('green')
                            document.getElementById(id).classList.add('check')
                        }
                    },
                    error: error => {
                        console.log(error)
                    }
                })
            },
            user_delete: (event) => {
                this.username = e.target.getAttribute('username')
                swal({
                    title: 'Eliminarás al usuario ' + this.username,
                    text: "No podrás recuperarlo, ¿deseas continuar?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.value) {
                        manage_users.loading()
                        $.post({
                            url: '/user/delete/', 
                            type: 'POST',
                            data: {
                                'id': event.target.getAttribute('user_id'),
                            },
                            success: (result) => {
                                document.getElementById('row-' + event.target.getAttribute('user_id')).style.display = 'none'
                                swal({
                                    title: 'Se eliminó',
                                    text: 'EL usuario ya no existe.',
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
                    }
                })
            },
            set_role: event => {
                $.post({
                    url: '/role/change/', 
                    type: 'POST',
                    data: {
                        'id_user': event.target.getAttribute('user_id'),
                        'id_role': event.target.getAttribute('role_id')
                    },
                    success: (result) => {
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
    $('.popup').popup();
    $('.ui.dropdown').dropdown();
</script>
@endsection