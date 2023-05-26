let _Password_Recover;

jQuery(document).ready(() => {
    _Password_Recover.init();
});

(($ => {
    _Password_Recover = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const recoverPasswordButton = $('#password-recover-button');

            recoverPasswordButton.on('click', function (e) {

                let isFormValid = $('#password-recover-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#password-recover-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Password_Recover.xhr = $.ajax({
                    url: '/password/recover',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'email': $('#email').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Password_Recover.xhr !== false) {
                            _Password_Recover.xhr.abort();
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

                        $("#password-recover-form input[type=text],[type=password],[type=email]").each(function() {

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
                    _Base.displayMessage("error", "Unable to process the password recovery. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
