let _Password_Reset;

jQuery(document).ready(() => {
    _Password_Reset.init();
});

(($ => {
    _Password_Reset = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            let urlParams = new URLSearchParams(window.location.search);

            const resetPasswordButton = $('#password-reset-button');

            resetPasswordButton.on('click', function (e) {

                let isFormValid = $('#password-reset-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#password-reset-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Password_Reset.xhr = $.ajax({
                    url: '/password/reset?email=' + encodeURIComponent(urlParams.get('email')) + '&identifier=' + encodeURIComponent(urlParams.get('identifier')),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'password': $('#password').val(),
                        'confirm_password': $('#confirm-password').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Password_Reset.xhr !== false) {
                            _Password_Reset.xhr.abort();
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

                        $("#password-reset-form input[type=text],[type=password],[type=email]").each(function() {

                            $(this).removeClass('is-invalid');
                        });

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace("_", "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace("_", " ")];

                                field.val('');

                                field.attr('placeholder', error);

                                field.attr('title', error);

                                field.addClass('is-invalid');
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the password reset. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
