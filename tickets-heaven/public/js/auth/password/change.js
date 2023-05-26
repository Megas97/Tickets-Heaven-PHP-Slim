let _Password_Change;

jQuery(document).ready(() => {
    _Password_Change.init();
});

(($ => {
    _Password_Change = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const changePasswordButton = $('#password-change-button');

            changePasswordButton.on('click', function (e) {

                let isFormValid = $('#password-change-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#password-change-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Password_Change.xhr = $.ajax({
                    url: '/password/change',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'password_old': $('#password-old').val(),
                        'password': $('#password').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Password_Change.xhr !== false) {
                            _Password_Change.xhr.abort();
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

                        $("#password-change-form input[type=text],[type=password],[type=email]").each(function() {

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
                    _Base.displayMessage("error", "Unable to process the password change. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
