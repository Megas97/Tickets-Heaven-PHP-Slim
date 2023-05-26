let _View_SupportTicket;

jQuery(document).ready(() => {
    _View_SupportTicket.init();
});

(($ => {
    
    _View_SupportTicket = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();

            const deleteSupportTicketButton = $('#support-ticket-delete-button');

            deleteSupportTicketButton.on('click', function (e) {

                e.preventDefault();
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Once deleted the support ticket will be gone forever!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete the support ticket!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_SupportTicket.xhr = $.ajax({
                            url: '/admin/support/' + $('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_SupportTicket.xhr !== false) {
                                    _View_SupportTicket.xhr.abort();
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
                            _Base.displayMessage("error", "Unable to process the support ticket deletion. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });

            const replyToSupportTicketButton = $('#support-ticket-reply-button');

            replyToSupportTicketButton.on('click', function (e) {

                e.preventDefault();

                let senderEmail = $('#sender-email:disabled');
                
                location.href = 'mailto:' + senderEmail.val();
            });
        }
    };
}))(jQuery);
