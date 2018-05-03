@extends('layouts/bootstrap')
@section('title')
Login
@endsection
@section('css')
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
<div class="text-center">
    <form class="form-signin" method="POST" action="/create">
        <h1 class="h3 mb-3 font-weight-normal">Registrar</h1>
        @if(session('error'))
            <p class="alert alert-danger">{{ session('error') }} </p>
        @endif
        <input name="name" type="text" id="inputName" class="form-control" placeholder="Nombre" required="" autofocus="" autofocus>
        <input name="username" type="text" id="inputUsername" class="form-control" placeholder="Usuario" required="">
        <input name="email" type="email" id="inputEmail" class="form-control" placeholder="Correo Electrónico" required="">
        <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Contraseña" required="">
        <input name="password_confirm" type="password" id="inputPasswordConfirm" class="form-control" placeholder="Repite Contraseña" required="">
        {{ csrf_field() }}
        <button class="btn btn-lg btn-primary btn-block" type="submit">Registrar <i class="fa fa-sign-in"></i></button>
        <p class="mt-5 mb-3 text-muted"><a href="/">Entrar</a></p>
        <p class="mt-5 mb-3 text-muted">Sistema ETL de Costurita</p>
    </form>
</div>
@endsection