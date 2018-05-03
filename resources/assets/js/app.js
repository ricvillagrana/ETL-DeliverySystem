
/**
* First we will load all of this project's JavaScript dependencies which
* includes Vue and other libraries. It is a great starting point when
* building robust, powerful web applications using Vue and Laravel.
*/

// ETL reset
etl_reset = () => {
    swal({
        title: 'Ejecutando...',
        onOpen: () => {
            swal.showLoading()
        }
    })
    $.ajax({
        url: '/etl/clean', 
        success: result => {
            swal(
                'ETL reiniciado',
                'Todos tus movimientos que no estaban fríamente calculados fueron deshechos',
                'success'
            )            
            window.location.href = '/etl';
        }
    });
}

require('./bootstrap');
$ = require('jquery');
window.Vue = require('vue');
window.swal = require('sweetalert2');


/**
* Next, we will create a fresh Vue application instance and attach it to
* the page. Then, you may begin adding components to this application
* or customize the JavaScript scaffolding to fit your unique needs.
*/

Vue.component('example-component', require('./components/ExampleComponent.vue'));
