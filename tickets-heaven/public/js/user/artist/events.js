let _Artist_Events;

jQuery(document).ready(() => {
    _Artist_Events.init();
});

(($ => {
    
    _Artist_Events = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.event-card');

            let previousValues = {};

            $('.approve-event-button, .reject-event-button').each(function () {

                const btn = $(this);

                btn.on('click', function (e) {

                    e.preventDefault();

                    Swal.fire({
                        title: 'Are you sure?',
                        text: "You can still change the event status later on!",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, ' + (btn.hasClass('approve-event-button') ? 'approve' : 'reject') + ' event!',
                        allowOutsideClick: false,
                    }).then((result) => {
    
                        if (result.isConfirmed) {
    
                            _Artist_Events.xhr = $.ajax({
                                url: '/artist/events/pending',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    'id': btn.data('id'),
                                    'action': btn.hasClass('approve-event-button') ? 'approve' : 'reject',
                                    'csrf_name': $('#ajax_csrf_name').data('value'),
                                    'csrf_value': $('#ajax_csrf_value').data('value')
                                },
                                beforeSend() {
                                    if (_Artist_Events.xhr !== false) {
                                        _Artist_Events.xhr.abort();
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

                                    let card = $('#card-' + response.fragments.updated_event_id);

                                    card.remove();

                                    if ($('.card').length == 0) {

                                        $('.row').append('<p class="text-center">There are no ' + $('#type').val() + ' events</p>');
                                    }
                                }
                            }).fail(response => {
                                _Base.displayMessage("error", "Unable to process the event approval / rejectal. Please reload the page. If the error persists contact support.");
                            });
                        }
                    });
                });
            });

            _Base.handleEventStatisticsForMultipleCards(previousValues);
        },
    };
}))(jQuery);
