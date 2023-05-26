let _Link_Social;

jQuery(document).ready(() => {
    _Link_Social.init();
});

(($ => {
    
    _Link_Social = {

        xhr: false,

        init() {
            
            const socialLoginButtons = $('.btn-github, .btn-facebook');

            socialLoginButtons.each(function() {

                let socialButton = $(this);

                socialButton.on('click', function (e) {

                    e.preventDefault();
    
                    let buttonType = $(this).data('type');
                    
                    _Link_Social.xhr = $.ajax({
                        url: '/social/auth/' + buttonType.toLowerCase(),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            '_http_referrer': $(this).parent().find('#_http_referrer').data('value'),
                            'link-social-media': $(this).parent().find('#link-social-media').val(),
                            'unlink-social-media': $(this).parent().find('#unlink-social-media').val(),
                            'csrf_name': $(this).parent().find('#ajax_csrf_name').data('value'),
                            'csrf_value': $(this).parent().find('#ajax_csrf_value').data('value')
                        },
                        beforeSend() {
                            if (_Link_Social.xhr !== false) {
                                _Link_Social.xhr.abort();
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
    
                            if (response.fragments.notify) {

                                _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                            }

                            if (response.fragments.unlinked) {

                                let socialMediaName = response.fragments.unlinked;

                                $('.btn-' + socialMediaName.toLowerCase()).children().eq(1).text('Link ' + socialMediaName);

                                $('.btn-' + socialMediaName.toLowerCase()).prev().attr('id', 'link-social-media');
                                
                                $('.btn-' + socialMediaName.toLowerCase()).prev().attr('name', 'link-social-media');
                            }
                        }
                    }).fail(response => {
                        _Base.displayMessage("error", "Unable to process the " + buttonType + " (un)link. Please reload the page. If the error persists contact support.");
                    });
                });
            });
        },
    };
}))(jQuery);
