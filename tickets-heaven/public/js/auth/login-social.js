let _Login_Social;

jQuery(document).ready(() => {
    _Login_Social.init();
});

(($ => {
    _Login_Social = {

        xhr: false,

        init() {
            
            const socialLoginButtons = $('.btn-github, .btn-facebook');

            socialLoginButtons.on('click', function (e) {

                e.preventDefault();

                let buttonType = $(this).data('type');
                
                _Login_Social.xhr = $.ajax({
                    url: '/social/auth/' + buttonType.toLowerCase(),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        '_http_referrer': $('#_http_referrer').data('value'),
                        'is-post-login-page': $('#is-post-login-page').val() || '',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Login_Social.xhr !== false) {
                            _Login_Social.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {
                        
                        if (response.fragments.redirectUrl) {

                            let base_url = '';

                            if (response.fragments.includeDomain) {

                                base_url = window.location.origin;
                            }

                            location.href = base_url + response.fragments.redirectUrl;
                            
                            return false;
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the " + buttonType + " login. Please reload the page. If the error persists contact support.");
                });
            });
        },
    };
}))(jQuery);
