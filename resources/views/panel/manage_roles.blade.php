@extends('panel.layout')
@section('additional-css')
<style>
    .cursor-pointer{cursor: pointer;}
</style>
@endsection
@section('dashboard-content')
<h3>Administración de usuarios</h3>
<p>Aquí puedes modificar los permisos e incuso eliminar usuarios.</p>
<div id="manage_roles">
    <div class="row mb-3">
        <div class="w-50 mx-auto">
            <div class="ui action input pull-right">
                <input id="new_role_name" type="text" placeholder="Nombre del rol" v-model:value="new_role_name">
                <button @click="add_role" class="ui button blue"><i class="fa fa-plus"></i></button>
            </div>
        </div>
    </div>
    <div class="row">
        <table class="ui celled table w-50 mx-auto">
            <thead>
                <tr>
                    <th>Rol</th>
                    @foreach(\DB::table('sections')->get() as $section)
                    <th>{{ $section->name }}</th>
                    @endforeach
                    <th>Más</th>
                </tr>
            </thead>
            <tbody id="roles">
                @foreach($roles as $role)
                <tr id="row-{{ $role->id }}">
                    <td>{{ $role->name }}</td>
                    @foreach(\DB::table('sections')->get() as $section)
                    <td @click="swap" 
                    id_role="{{ $role->id }}" 
                    id_section="{{ $section->id }}"
                    access="{{ \App\Section::check($role->id, $section->id) ? 'set' : 'unset' }}"
                    class="popup center aligned cursor-pointer"><i id="{{ $role->id.'-'.$section->id }}" id_user="{{ $role->id }}" id_privilege="{{ $section->id }}" class="large {{ \App\Section::check($role->id, $section->id) ? 'green check' : 'red minus' }} icon"></i></td>
                    @endforeach
                    <td>
                        <button @click="remove_role" role_name="{{ $role->name }}" role_id="{{ $role->id }}" class="item btn btn-outline-danger"><i class="fa fa-trash"></i> Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@section('additional-js')
<script>
    let manage_roles = new Vue({
        el: '#manage_roles',
        data: {
            role_name: '',
            new_role_name: ''
        },
        methods: {
            swap: (e) => {
                var uri = (e.target.getAttribute('access') == 'unset') ? 'add' : 'remove'
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: '/role/section/' + uri,
                    type: 'POST',
                    data: {
                        'id_role': e.target.getAttribute('id_role'),
                        'id_section': e.target.getAttribute('id_section')
                    },
                    success: result => {
                        let id = e.target.getAttribute('id_role') + '-' + e.target.getAttribute('id_section')
                        if(e.target.getAttribute('access') == 'set'){
                            e.target.setAttribute('access', 'unset')
                            document.getElementById(id).classList.remove('green')
                            document.getElementById(id).classList.remove('check')
                            document.getElementById(id).classList.add('red')
                            document.getElementById(id).classList.add('minus')
                        }else{
                            e.target.setAttribute('access', 'set')
                            document.getElementById(id).classList.remove('red')
                            document.getElementById(id).classList.remove('minus')
                            document.getElementById(id).classList.add('green')
                            document.getElementById(id).classList.add('check')
                        }
                    },
                    error: error => {
                        console.log(error)
                        swal({
                            title: 'Algo salió mal',
                            text: 'Error: '+jQuery.parseJSON(error.responseText).message,
                            type: 'error'
                        })
                    }
                })
            },
            add_role: () => {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                }); 
                if(manage_roles.new_role_name != ""){
                    manage_roles.loading()
                    $.post({
                        url: '/role/add/', 
                        type: 'POST',
                        data: {
                            'name': manage_roles.new_role_name,
                        },
                        success: (result) => {
                            swal({
                                title: 'Completado',
                                text: 'El rol ' + manage_roles.new_role_name + ' se añadió.',
                                type: 'success'
                            })
                            document.location.reload();
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
            },
            remove_role: event => {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                this.role_name = event.target.getAttribute('role_name')
                swal({
                    title: 'Eliminarás el rol de ' + this.role_name,
                    text: "No podrás recuperarlo, ¿deseas continuar?",
                    type: 'warning',
                    showCancelButton: true,
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.value) {
                        //manage_roles.loading()
                        $.post({
                            url: '/role/remove/', 
                            type: 'POST',
                            data: {
                                'id': event.target.getAttribute('role_id'),
                            },
                            success: (result) => {
                                document.getElementById('row-' + event.target.getAttribute('role_id')).style.display = 'none'
                                /*
                                swal({
                                    title: 'Se eliminó',
                                    text: 'El rol ya no existe.',
                                    type: 'success'
                                })
                                */
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