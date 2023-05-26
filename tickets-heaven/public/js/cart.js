let _Cart;

jQuery(document).ready(() => {
    _Cart.init();
});

(($ => {
    
    _Cart = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $('#guest-credit-card-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));
            });

            let table = $('#cartEventsTable').DataTable({

                initComplete: function() {

                    this.api().columns().every(function() {

                        const column = this;

                        if ($(column.header()).hasClass('dropdown-filter')) {

                            const select = $('<select class="mx-1 mb-2 mb-0 form-control-sm"><option value="">-- ' + $(column.header()).html() + ' --</option></select>')
                                .prependTo($('.dataTables_filter'))
                                .on('change', function() {
                                    const val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    const regExSearch = '^' + val + '';

                                    column.search(val ? "^" + regExSearch : '', true, false, false).draw();
                                });

                            const sortVals = [];

                            column.data().unique().sort().each(function(d, j) {

                                const split = d.toString().split(', ');

                                split.forEach(function(val) {

                                    val = _Base.removeTags(val).trim();

                                    if ($.inArray(val, sortVals) === -1) {

                                        sortVals[j] = d;

                                        if (val != '') {

                                            select.append('<option value="' + val + '">' + val + '</option>');
                                        }
                                    }
                                });
                            });
                        }
                    });
                },
                dom: "<'row'<'col-sm-24 col-md-12'><'col-sm-24 col-md-12 d-flex justify-content-end mb-2'B>>" +
                        "<'row'<'col-sm-24 col-md-12'l><'col-sm-24 col-md-12'f>>" +
                        "<'row'<'col-sm-24'tr>>" +
                        "<'row'<'col-sm-24 col-md-10'i><'col-sm-24 col-md-14'p>>",
                deferRender: true,
                'pagingType': 'simple',
                'columnDefs': [
                    { visible: false, targets: [0] }, // id
                    { orderable: false, targets: [1, 6] }, // image, actions
                    { searchable: false, targets: [6] }, // actions
                ],
                'order': [
                    [0, 'asc']
                ],
                'lengthMenu': [
                    [20, 50, -1],
                    [20, 50, 'All']
                ],
                responsive: true,
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search Cart'
                },
                ajax: {
                    'url': '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'Cart',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'event.id', className: 'text-center align-middle' },
                    { data: 'event.image', className: 'text-center align-middle' },
                    { data: 'event.name', className: 'text-center align-middle' },
                    { data: 'event.ticket_quantity', className: 'text-center align-middle' },
                    { data: 'event.ticket_single_price', className: 'text-center align-middle' },
                    { data: 'event.ticket_total_price', className: 'text-center align-middle' },
                    { data: 'event.actions', className: 'text-center align-middle' },
                ],
            });

            table.on('draw responsive-display', function() {

                let updateButtons = $('.datatable-add-button, .datatable-subtract-button');

                let deleteButtons = $('.datatable-delete-button');

                $.each(updateButtons, function() {

                    let updateButton = $(this);

                    updateButton.on('click', function (e) {

                        _Cart.xhr = $.ajax({
                            url: '/datatable/update',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'modelData': 'Cart',
                                'modelId': updateButton.data('id'),
                                'action': updateButton.data('action'),
                                'csrf_name': $('#ajax_csrf_name').data('value'),
                                'csrf_value': $('#ajax_csrf_value').data('value')
                            },
                            beforeSend() {
                                if (_Cart.xhr !== false) {
                                    _Cart.xhr.abort();
                                }
                            }
                        }).done(response => {

                            if (response !== false && response !== undefined && response !== '') {

                                if (response.fragments.redirectUrl) {

                                    let base_url = '';
        
                                    let fragmentsString = '';
        
                                    if (response.fragments.includeDomain) {
        
                                        base_url = window.location.origin;
                                    }
                                    
                                    if (response.fragments && !response.fragments.clean_url) {
        
                                        fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                                    }
        
                                    location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                                    
                                    return false;
                                }

                                $.each(response.fragments.updated_fields, function (key, value) {

                                    let field = $('#' + key.replace(/_/g, '-'));

                                    if (key === 'total_due' && response.fragments.updated_fields['total_promo_due']) {

                                        field.html('<s>' + value + '</s>');

                                    } else if (key === 'total_promo_due' || (key === 'total_due' && !response.fragments.updated_fields['total_promo_due'])) {

                                        field.html(value);
                                    }
                                });
                                
                                table.ajax.reload();
                            }
                        }).fail(response => {
                            // This is commented as it spams errors when spamming the + or - buttons as requests get cancelled but everything still works as expected
                            // _Base.displayMessage("error", "Unable to process the cart item update. Please reload the page. If the error persists contact support.");
                        });
                    });
                });
                
                $.each(deleteButtons, function() {
                    
                    let deleteButton = $(this);

                    deleteButton.on('click', function (e) {
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You can add the event to your cart again from the event details page!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete the cart item!',
                            allowOutsideClick: false,
                        }).then((result) => {

                            if (result.isConfirmed) {
                                
                                _Cart.xhr = $.ajax({
                                    url: '/datatable/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'modelData': 'Cart',
                                        'modelId': deleteButton.data('id'),
                                        'csrf_name': $('#ajax_csrf_name').data('value'),
                                        'csrf_value': $('#ajax_csrf_value').data('value')
                                    },
                                    beforeSend() {
                                        if (_Cart.xhr !== false) {
                                            _Cart.xhr.abort();
                                        }
                                    }
                                }).done(response => {

                                    if (response !== false && response !== undefined && response !== '') {

                                        if (response.fragments.redirectUrl) {

                                            let base_url = '';
                
                                            let fragmentsString = '';
                
                                            if (response.fragments.includeDomain) {
                
                                                base_url = window.location.origin;
                                            }
                                            
                                            if (response.fragments && !response.fragments.clean_url) {
                
                                                fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                                            }
                
                                            location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                                            
                                            return false;
                                        }

                                        if (response.fragments.notify) {
                
                                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                        }

                                        $('#cart-items-quantity').html(response.fragments.cart_items_count > 0 ? response.fragments.cart_items_count : '');

                                        if (response.fragments.cart_items_count == 0) {

                                            $('.card-body').remove();

                                            $('#checkout-button').attr('disabled', true);

                                            let currency = $('#total-due').html().slice(-3);

                                            $('#total-due').html('0.00 ' + currency);
                                        }
                                        
                                        table.ajax.reload();
                                    }
                                }).fail(response => {
                                    _Base.displayMessage("error", "Unable to process the cart item deletion. Please reload the page. If the error persists contact support.");
                                });
                            }
                        })
                    });
                });
            });

            let applyPromoCodeButton = $('#checkout-apply-promo-code-button');

            applyPromoCodeButton.on('click', function (e) {

                e.preventDefault();

                let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);

                _Cart.xhr = $.ajax({
                    url: '/apply-promo',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'checkout_promo_code': $('#checkout-promo-code').val(),
                        'current_datetime': localISOTime,
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Cart.xhr !== false) {
                            _Cart.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        if (response.fragments.redirectUrl) {

                            let base_url = '';

                            let fragmentsString = '';

                            if (response.fragments.includeDomain) {

                                base_url = window.location.origin;
                            }
                            
                            if (response.fragments && !response.fragments.clean_url) {

                                fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                            }

                            location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                            
                            return false;
                        }

                        if (response.fragments.notify) {
                            
                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.errors) {
                            
                            $.each(response.fragments.errors, function (key, value) {
                                
                                let field = $('#' + key.replace(/_/g, '-'));
                                
                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, ' ')];

                                if (key === 'checkout_promo_code') {
                                
                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                    
                                    field.addClass('is-invalid');

                                } else if (key === 'checkout_promo_code_expired') {

                                    _Base.displayMessage('error', error);
                                }
                            });
                        }

                        if (response.fragments.updated_fields) {

                            $.each(response.fragments.updated_fields, function (key, value) {

                                let field = $('#' + key.replace(/_/g, '-'));

                                field.html(value);

                                if (key === 'total_due') {

                                    field.html('<s>' + field.html() + '</s>');

                                } else if (key === 'total_promo_due') {

                                    field.removeClass('d-none');
                                }

                                $('#checkout-promo-code').attr('title', '');
                            });
                        }

                        if (response.fragments.promoEvent) {

                            $('#checkout-promo-code').attr('disabled', true);

                            $('#checkout-promo-code').removeClass('is-invalid');

                            $('#checkout-promo-code').attr('placeholder', 'Promo code');
;                            
                            $('#checkout-apply-promo-code-button').addClass('d-none');

                            $('#checkout-remove-promo-code-button').removeClass('d-none');

                            $('#checkout-remove-promo-code-button').next().children().eq(0).removeClass('clear-button');

                            $('#checkout-remove-promo-code-button').next().children().eq(0).off('click');
                            
                            table.ajax.reload();
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the promo code. Please reload the page. If the error persists contact support.");
                });
            });

            let removePromoCodeButton = $('#checkout-remove-promo-code-button');

            removePromoCodeButton.on('click', function (e) {

                e.preventDefault();

                _Cart.xhr = $.ajax({
                    url: '/remove-promo',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Cart.xhr !== false) {
                            _Cart.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        if (response.fragments.redirectUrl) {

                            let base_url = '';

                            let fragmentsString = '';

                            if (response.fragments.includeDomain) {

                                base_url = window.location.origin;
                            }
                            
                            if (response.fragments && !response.fragments.clean_url) {

                                fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                            }

                            location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                            
                            return false;
                        }

                        if (response.fragments.notify) {
                            
                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.updated_fields) {

                            $.each(response.fragments.updated_fields, function (key, value) {

                                let field = $('#' + key.replace(/_/g, '-'));

                                if (key === 'total_due') {

                                    field.html(field.html().replace('<s>', '').replace('</s>', ''));

                                    field.removeClass('text-decoration-line-through');

                                    $('#total-promo-due').addClass('d-none');
                                }
                            });
                        }

                        if (response.fragments.removed_promo) {

                            $('#checkout-promo-code').val('');

                            $('#checkout-promo-code').attr('disabled', false);

                            $('#checkout-remove-promo-code-button').addClass('d-none');

                            $('#checkout-apply-promo-code-button').removeClass('d-none');

                            $('#checkout-remove-promo-code-button').next().children().eq(0).addClass('clear-button');

                            $('#checkout-remove-promo-code-button').next().children().eq(0).on('click', function (e) {

                                let inputElement = $(this).parent().prev().prev().prev();

                                inputElement.val('');
                            });
                            
                            table.ajax.reload();
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the promo code. Please reload the page. If the error persists contact support.");
                });
            });

            let checkoutButton = $('#checkout-button');

            checkoutButton.on('click', function (e) {

                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You can't undo this action!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, checkout!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {
                        
                        _Cart.xhr = $.ajax({
                            url: '/cart',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'guest_first_name': $('#guest-first-name') ? $('#guest-first-name').val() : null,
                                'guest_last_name': $('#guest-last-name') ? $('#guest-last-name').val() : null,
                                'guest_email': $('#guest-email') ? $('#guest-email').val() : null,
                                'guest_credit_card_number': $('#guest-credit-card-number') ? $('#guest-credit-card-number').val() : null,
                                'csrf_name': $('#ajax_csrf_name').data('value'),
                                'csrf_value': $('#ajax_csrf_value').data('value')
                            },
                            beforeSend() {
                                if (_Cart.xhr !== false) {
                                    _Cart.xhr.abort();
                                }
                            }
                        }).done(response => {

                            if (response !== false && response !== undefined && response !== '') {

                                if (response.fragments.redirectUrl) {

                                    let base_url = '';
        
                                    let fragmentsString = '';
        
                                    if (response.fragments.includeDomain) {
        
                                        base_url = window.location.origin;
                                    }
                                    
                                    if (response.fragments && !response.fragments.clean_url) {
        
                                        fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                                    }
        
                                    location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                                    
                                    return false;
                                }

                                if (response.fragments.errors) {

                                    let errorFields = [];
                                    
                                    $.each(response.fragments.errors, function (key, value) {
                                        
                                        let field = $('#' + key.replace(/_/g, '-'));
                                        
                                        let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, ' ')];
                                        
                                        field.val('');
        
                                        field.attr('placeholder', error);

                                        field.attr('title', error);
                                        
                                        field.addClass('is-invalid');

                                        if (!errorFields.includes(key)) {
                                        
                                            errorFields.push(key);
                                        }
                                    });

                                    $("#guest-checkout-details input").each(function() {

                                        let otherKey = $(this).attr('name');

                                        if (!errorFields.includes(otherKey)) {

                                            $(this).removeClass('is-invalid');

                                            $(this).attr('placeholder', _Base.capitalizeFirstLetter(otherKey.replace(/_/g, ' ').replace('guest ', '')));
                                        }
                                    });
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the checkout. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });
        }
    };
}))(jQuery);
