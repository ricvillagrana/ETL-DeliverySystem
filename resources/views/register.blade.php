@extends('layouts/bootstrap')
@section('title')
Login
@endsection
@section('css')
<link rel="stylesheet" href="/css/app.css">
@yield('additional-css')
<style>
    html,
    body {
        height: 100%;
    }
    
    body {
        display: -ms-flexbox;
        display: -webkit-box;
        display: flex;
        -ms-flex-align: center;
        -ms-flex-pack: center;
        -webkit-box-align: center;
        align-items: center;
        -webkit-box-pack: center;
        justify-content: center;
        padding-top: 40px;
        padding-bottom: 40px;
        background-color: #f5f5f5;
    }
    
    .form-signin {
        width: 100%;
        max-width: 330px;
        padding: 15px;
        margin: 0 auto;
    }
    .form-signin .checkbox {
        font-weight: 400;
    }
    .form-signin .form-control {
        position: relative;
        box-sizing: border-box;
        height: auto;
        padding: 10px;
        font-size: 16px;
    }
    .form-signin .form-control:focus {
        z-index: 2;
    }
    .form-signin input[name="name"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
    }
    .form-signin input[name="email"] {
        margin-top: -1px;
        margin-bottom: -1px;
        border-radius: 0;
        border-radius: 0;
    }
    .form-signin input[name="password"] {
        margin-top: -1px;
        margin-bottom: -1px;
        border-radius: 0;
        border-radius: 0;
    }
    .form-signin input[name="password_confirm"] {
        margin-bottom: 10px;
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }{{--    --}}
</style>
@endsection
@section('content')
<div id="register-form" class="text-center">
    @if(session('error'))
    <div class="ui negative message w-50 mx-auto">
        <i class="close icon"></i>
        <div class="header">
            Error
        </div>
        <p>{{ session('error') }}</p>
    </div>
    @endif
    <form class="ui form w-50 mx-auto" method="POST" action="/create">
        <h4 class="ui dividing header">Formulario de registro</h4>
        <div class="field">
            <label>Nombre</label>
            <div class="one field">
                <div class="field">
                    <input type="text" name="name" placeholder="Nombre completo" required="required">
                </div>
            </div>
            <label>Usuario</label>
            <div class="field">
                <div class="field">
                    <input type="text" name="username" placeholder="Nombre de usuario" required="required">
                </div>
            </div>
        </div>
        <div class="field">
            <div class="fields">
                <div class="ten wide field">
                    <label>Correo electrónico</label>
                </div>
                <div class="six wide field">
                    <label>Rol de usuario</label>
                </div>
            </div>
            <div class="fields">
                <div class="ten wide field">
                    <input type="email" pattern="[^@]+@[^@]+\.[a-zA-Z]{2,6}" name="email" placeholder="Correo electrónico" required="required">
                    <input type="hidden" id="id_role" name="id_role" v-model:value="role_id">
                </div>
                <div class="six wide field">
                    <div class="ui fluid selection dropdown">
                        <div class="text">Selecciona un rol</div>
                        <i class="dropdown icon"></i>
                        <div class="menu">
                            @foreach (\App\Role::all() as $role)
                            <div @click="set_role" id="role-{{ $role->id }}" role_id="{{ $role->id }}" class="item">{{ $role->name }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="field">
            <div class="fields">
                <div class="eight wide field">
                    <label>Contraseña</label>
                </div>
                <div class="eight wide field">
                    <label>Repite Contraseña</label>
                </div>
            </div>
            <div class="fields">
                <div class="eight wide field">
                    <input type="password" name="password" placeholder="Contraseña" required="required">
                </div>
                <div class="eight wide field">
                    <input type="password" name="password_confirm" placeholder="Repite contraseña" required="required">
                </div>
            </div>
        </div>
        {{ csrf_field() }}
        <button class="ui button" type="submit">Registrar</button>
    </form>
    <p class="mt-5 mb-3 text-muted"><a href="/">Entrar</a></p>
    <p class="mt-5 mb-3 text-muted">Sistema ETL de Costurita</p>
    
</div>
@endsection
@section('js')
<script src="/js/app.js"></script>

<script>
    let register = new Vue({
        el: '#register-form',
        data: {
            role_id: 0
        },
        methods: {
            set_role: event => {
                register.role_id = event.target.getAttribute('role_id')
                document.getElementById('id_role').value = register.role_id
            }
        }
    });
</script>

<script>
    $('.popup').popup();
    $('.ui.dropdown').dropdown();
</script>
@endsection
