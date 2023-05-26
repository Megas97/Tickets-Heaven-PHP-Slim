let _View_Promo_Code;

jQuery(document).ready(() => {
    _View_Promo_Code.init();
});

(($ => {
    
    _View_Promo_Code = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();
            
            $('#event').selectpicker();

            let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

            let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
            
            $('#deadline').attr('min', localISOTime);
            
            const generatePromoCodeButton = $('#generate-promo-code');

            generatePromoCodeButton.on('click', function (e) {

                e.preventDefault();

                const promoCode = _Base.generatePromoCode(6);

                $('#code').val(promoCode);
            });

            const updatePromoCodeButton = $('#update-promo-code-button');

            let previousValues = {};

            $("#view-promo-code-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            $("#view-promo-code-form input[type=datetime-local]").each(function() {
                
                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).val();

                    previousValues[key] = value;
                }
            });

            updatePromoCodeButton.on('click', function (e) {

                $('#deadline').removeAttr('min');

                let isFormValid = $('#view-promo-code-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#view-promo-code-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let form = new FormData();
                
                form.append("code", $('#code').val());
                form.append("event", $('#event').val());
                form.append("percent", $('#percent').val());
                form.append("deadline", $('#deadline').val());
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _View_Promo_Code.xhr = $.ajax({
                    url: '/admin/promotions/' + $('#current-id').val(),
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_View_Promo_Code.xhr !== false) {
                            _View_Promo_Code.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                        let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
                        
                        $('#deadline').attr('min', localISOTime);

                        $("#view-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#view-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                            if ($(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                $(this).attr('placeholder', previousValues[key]);
                            }

                            $(this).removeClass('is-invalid');
                        });

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

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                
                                if (key === 'event' || key === 'deadline') {

                                    _Base.displayMessage("error", error);

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#view-promo-code-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'event') {

                                            $('#' + key.replace('_', '-')).selectpicker('destroy');

                                            $('#' + key.replace('_', '-')).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'event' ) {

                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });

                                $("#view-promo-code-form input[type=datetime-local]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).val(previousValues[key]);
                                    }
                                });
                            });
                        }

                        if (response.fragments.updated_fields) {

                            $("#view-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val() !== '') {

                                    if ($(this).attr('id') != 'discounted-ticket-price') {
                                        
                                        $(this).val('');
                                    }
                                }
                            });

                            $("#view-promo-code-form input[type=datetime-local]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val() !== '') {

                                    if ($(this).hasClass('is-invalid')) {

                                        $(this).removeClass('is-invalid');
                                    }
                                }
                            });

                            $.each(response.fragments.updated_fields, function (key, value) {
                                
                                if (key === 'deadline') {

                                    $("#view-promo-code-form input[type=datetime-local]").each(function() {
                            
                                        if (!$(this).hasClass('is-invalid')) {

                                            let key = $(this).attr('name');

                                            previousValues[key] = value;
                                        }
                                    });
                                    
                                    let field = $('#' + key.replace("_", "-"));
                                    
                                    field.val(value.slice(0, 16));

                                } else if (key === 'percent' || key === 'event_id') {

                                    let field = $('#' + key.replace("_", "-"));

                                    field.val('');
                                    
                                    field.attr('placeholder', parseFloat(value).toFixed(2));

                                    field.attr('title', '');

                                    let ticketPrice = response.fragments.updated_fields.ticket_price;
                                    
                                    let currency = response.fragments.updated_fields.currency;

                                    let percent = response.fragments.updated_fields.percent;
                                    
                                    let discountedTicketPrice = parseFloat(ticketPrice - ticketPrice * (percent / 100)).toFixed(2);
                                    
                                    $('#discounted-ticket-price').val(discountedTicketPrice + ' ' + currency);

                                } else {

                                    let field = $('#' + key.replace("_", "-"));

                                    field.val('');
                                    
                                    field.attr('placeholder', value);

                                    field.attr('title', '');
                                    
                                    let copyButton = field.next().children().eq(0);

                                    copyButton.attr('disabled', false);
                                }
                            });

                        } else {

                            if (!response.fragments.errors) {

                                $("#view-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                    if ($(this).attr('id') != 'discounted-ticket-price') {
                                        
                                        $(this).val('');
                                    }
                                });

                                $("#view-promo-code-form input[type=datetime-local]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        $(this).removeClass('is-invalid');
                                    }
                                });
                            }
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the promo code updating process. Please reload the page. If the error persists contact support.");
                });
            });

            const deletePromoCodeButton = $('#delete-promo-code-button');

            deletePromoCodeButton.on('click', function (e) {

                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Once deleted the promo code will be gone forever!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete the promo code!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("delete_promo_code_button", $('#delete-promo-code-button') ? true : false);
                        form.append("current_id", $('#current-id').val());
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_Promo_Code.xhr = $.ajax({
                            url: '/admin/promotions/' + $('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_Promo_Code.xhr !== false) {
                                    _View_Promo_Code.xhr.abort();
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
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the promo code deletion. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });
        },
    };
}))(jQuery);
