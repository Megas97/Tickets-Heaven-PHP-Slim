let _Events;

jQuery(document).ready(() => {
    _Events.init();
});

(($ => {

    _Events = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();
            
            _Base.handleEventHappeningLabel('.event-card');

            let currentDateTime = new Date(); 

            let cards = $('.event-card');

            cards.each(function (e) {

                let card = $(this);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);

                if (currentDateTime >= endDatetime) {

                    card.remove();
                }
            });

            let venues = $('.hosted-events');

            venues.each(function (e) {

                let events = JSON.parse($(this).val());

                for (let event of events) {

                    let endDateArray = event.end_date.split('.');

                    let endTimeArray = event.end_time.split(':');

                    let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);

                    if (currentDateTime >= endDatetime) {

                        let index = events.indexOf(event);

                        if (index > -1) {

                            events.splice(index, 1);
                        }
                    }
                }

                $(this).next().next().html(events.length);
            });

            $.each($('.buy-ticket-form'), function (e) {

                $(this).submit(function (e) {

                    let isFormValid = $(this)[0].checkValidity();
    
                    if (!isFormValid) {
    
                        $(this)[0].reportValidity();
    
                        return false;
    
                    } else {
    
                        e.preventDefault();
                    }
    
                    _Events.xhr = $.ajax({
                        url: '/events',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'event_id': $(this).parent().find('.event-id').val(),
                            'choose_ticket_quantity': $(this).find('.choose-ticket-quantity').val(),
                            'csrf_name': $('#ajax_csrf_name').data('value'),
                            'csrf_value': $('#ajax_csrf_value').data('value')
                        },
                        beforeSend() {
                            if (_Events.xhr !== false) {
                                _Events.xhr.abort();
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
    
                            $('#cart-items-quantity').html(response.fragments.cart_items_count > 0 ? response.fragments.cart_items_count : '');
                        }
                    }).fail(response => {
                        _Base.displayMessage("error", "Unable to process the ticket buying. Please reload the page. If the error persists contact support.");
                    });
                });
            });
        },
    };
}))(jQuery);
