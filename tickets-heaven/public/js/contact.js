let _Contact;

jQuery(document).ready(() => {
    _Contact.init();
});

(($ => {
    
    _Contact = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $("#contact-form").submit(function (e) {

                let isFormValid = $('#contact-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#contact-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Contact.xhr = $.ajax({
                    url: '/contact',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'guest_email': $('#guest-email') ? $('#guest-email').val() : '',
                        'guest_first_name': $('#guest-first-name') ? $('#guest-first-name').val() : '',
                        'guest_last_name': $('#guest-last-name') ? $('#guest-last-name').val() : '',
                        'subject': $('#subject').val(),
                        'message': $('#message').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Contact.xhr !== false) {
                            _Contact.xhr.abort();
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

                            let errorFields = [];

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];

                                field.val('');

                                field.attr('placeholder', error);

                                field.addClass('is-invalid');

                                if (!errorFields.includes(key)) {
                                        
                                    errorFields.push(key);
                                }
                            });

                            $("#contact-form input, textarea").each(function() {

                                let otherKey = $(this).attr('name');

                                if (!errorFields.includes(otherKey)) {

                                    $(this).removeClass('is-invalid');

                                    $(this).attr('placeholder', _Base.capitalizeFirstLetter(otherKey.replace(/_/g, ' ').replace('guest ', '')));
                                }
                            });

                        } else {

                            let email = $('#guest-email');

                            let firstName = $('#guest-first-name');

                            let lastName = $('#guest-last-name');

                            if (!email.attr('disabled')) {
                                
                                email.val('');
                            }

                            if (!firstName.attr('disabled')) {
                                
                                firstName.val('');
                            }

                            if (!lastName.attr('disabled')) {
                                
                                lastName.val('');
                            }

                            $('#subject').val('');

                            $('#message').val('');

                            $("#contact-form input, textarea").each(function() {

                                let key = $(this).attr('name');

                                $(this).removeClass('is-invalid');

                                $(this).attr('placeholder', _Base.capitalizeFirstLetter(key.replace(/_/g, ' ').replace('guest ', '')));
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the support ticket sending. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
