let app = new Vue({
    el: '#check',
    data: {
        original: '',
        current: '',
        suggest: '',
        table: '',
        field: '',
        comment: '',
        row_id: '',
        id: '',
        input_type: 'text',
        current_field: null,
        
        // Genearl
        general_table_name: '',
        general_current: '',
        general_field: '',
        general_input_type: '',
        errors: 0
    },
    methods: {
        chars_handler: event => {
            let input = document.getElementById('new_value')
            let type = input.getAttribute('type')
            if(type == 'number'){
                input.value = input.value.replace(/\D\./g, '/^[0-9]$/')
            }
        },
        auto_fix: () => {
            $.ajax({
                url: '/etl/do/auto-fix',
                success: (result) => {
                    console.log(result)
                    document.location.reload()
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
        },
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
                url: '/etl/check/change_all', 
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
                url: '/etl/check/change', 
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
                    row_id = this.current_field.getAttribute('table') + '-' + this.current_field.getAttribute('row_id')
                    console.log(row_id)
                    document.getElementById('btn-' + row_id).setAttribute('able-to-send', true)
                    document.getElementById('icon-' + row_id).setAttribute('able-to-send', true)
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
            this.comment = this.current_field.getAttribute('comment')
            this.row_id = this.current_field.getAttribute('row_id')
            if(this.field.includes('fecha') || this.field.includes('creado') || this.field.includes('created_at')){
                this.input_type = 'date'
            }else if(this.field.includes('hora')){
                this.input_type = 'time'
            }else if(this.field.includes('folio') 
                    || this.field.includes('numero') 
                    || this.field.includes('gas')
                    || this.field.includes('km')
                    || this.field.includes('total')
                    || this.field.includes('subtotal')
                    || this.field.includes('iva')
                    || this.field.includes('cantidad')
                    || this.field.includes('precio')
                ){
                this.input_type = 'number'
            }else{
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
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.value) {
                    this.loading()
                    $.post({
                        url: '/etl/check/delete', 
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
                                app.errors--
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
            console.log(event.target.getAttribute('able-to-send'))
            if(event.target.getAttribute('able-to-send') != 'true'){
                swal({
                    title: 'Campo no modificado',
                    text: 'El campo aún no ha sido modificado y no puede mandarse al DataWareHouse así.',
                    type: 'error'
                })
            }else{
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
                            url: '/etl/check/send', 
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
                                    app.errors--
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
            }
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
                        url: '/etl/check/send-all', 
                        type: 'GET',
                        success: (result) => {
                            setTimeout(() => {
                                swal({
                                    title: 'Se enviaron los registros',
                                    text: 'Los registros se enviaron al DataWareHouse, podrás encontrarlos ahí.',
                                    type: 'success'
                                }).then((result) => {
                                    if(result.value){
                                        window.location = '/etl/check'
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
        
        // Misc method
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