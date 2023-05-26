let _Unlink_Social;

jQuery(document).ready(() => {
    _Unlink_Social.init();
});

(($ => {
    
    _Unlink_Social = {

        xhr: false,

        init() {
            
            const socialLoginButtons = $('.btn-github, .btn-facebook');

            socialLoginButtons.each(function() {

                let socialButton = $(this);

                socialButton.on('click', function (e) {

                    e.preventDefault();
    
                    let buttonType = $(this).data('type');
                    
                    _Unlink_Social.xhr = $.ajax({
                        url: '/social/unlink/' + buttonType.toLowerCase(),
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'current-username': $('#current-username').val(),
                            'unlink-social-media': $(this).parent().find('#unlink-social-media').val(),
                            'csrf_name': $(this).parent().find('#ajax_csrf_name').data('value'),
                            'csrf_value': $(this).parent().find('#ajax_csrf_value').data('value')
                        },
                        beforeSend() {
                            if (_Unlink_Social.xhr !== false) {
                                _Unlink_Social.xhr.abort();
                            }
                        }
                    }).done(response => {

                        if (response !== false && response !== undefined && response !== '') {
    
                            if (response.fragments.notify) {

                                _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                            }

                            if (response.fragments.unlinked) {

                                let socialMediaName = response.fragments.unlinked;
                                
                                $('.btn-' + socialMediaName.toLowerCase()).children().eq(1).text('No ' + socialMediaName);
                            }
                        }
                    }).fail(response => {
                        _Base.displayMessage("error", "Unable to process the " + buttonType + " unlink. Please reload the page. If the error persists contact support.");
                    });
                });
            });
        },
    };
}))(jQuery);
