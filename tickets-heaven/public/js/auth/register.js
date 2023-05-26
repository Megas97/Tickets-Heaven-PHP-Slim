let _Register;

jQuery(document).ready(() => {
    _Register.init();
});

(($ => {
    _Register = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $('#phone-code-id').selectpicker();

            $('#default-currency-id').selectpicker();

            $('#credit-card-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));
            });

            const registerButton = $('#register-button');

            let previousValues = {};

            $("#register-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            registerButton.on('click', function (e) {

                let isFormValid = $('#register-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#register-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Register.xhr = $.ajax({
                    url: '/register',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'username': $('#username').val(),
                        'email': $('#email').val(),
                        'first_name': $('#first-name').val(),
                        'last_name': $('#last-name').val(),
                        'phone_code_id': $('#phone-code-id').val(),
                        'phone_number': $('#phone-number').val(),
                        'credit_card_number': $('#credit-card-number').val(),
                        'default_currency_id': $('#default-currency-id').val(),
                        'address': $('#address').val(),
                        'description': $('#description').val(),
                        'password': $('#password').val(),
                        'confirm_password': $('#confirm-password').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Register.xhr !== false) {
                            _Register.xhr.abort();
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

                        $("#register-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#register-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                            if ($(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                $(this).attr('placeholder', previousValues[key]);
                            }

                            $(this).removeClass('is-invalid');
                        });

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];

                                if (key === 'phone_code_id') {

                                    _Base.displayMessage("error", error.replace(" id", ""));

                                    field.val('+XXX').change();

                                } else if (key === 'default_currency_id') {

                                    _Base.displayMessage("error", error.replace(" id", ""));

                                    field.val('Default currency').change();

                                } else if (key === 'phone') {

                                    _Base.displayMessage("error", error);

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#register-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'phone_code_id' || key === 'default_currency_id') {

                                            $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                            $('#' + key.replace(/_/g, "-")).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'phone_code_id' || key === 'default_currency_id') {
                                        
                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the registration. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
