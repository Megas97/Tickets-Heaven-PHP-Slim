let _Login;

jQuery(document).ready(() => {
    _Login.init();
});

(($ => {
    _Login = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const loginButton = $('#login-button');

            loginButton.on('click', function (e) {

                let isFormValid = $('#login-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#login-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Login.xhr = $.ajax({
                    url: '/login',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'username_or_email': $('#username-or-email').val(),
                        'password': $('#password').val(),
                        'remember_me': $('#remember-me').is(':checked') ? $('#remember-me').val() : '',
                        '_http_referrer': $('#_http_referrer').data('value'),
                        'is-post-login-page': $('#is-post-login-page').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Login.xhr !== false) {
                            _Login.xhr.abort();
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

                        $("#login-form input[type=text],[type=password],[type=email]").each(function() {

                            $(this).removeClass('is-invalid');
                        });

                        if (response.fragments.notify) {

                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

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
                    _Base.displayMessage("error", "Unable to process the login. Please reload the page. If the error persists contact support.");
                });
            });
        },
    };
}))(jQuery);
