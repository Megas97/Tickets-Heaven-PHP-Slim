let _Order_Details;

jQuery(document).ready(() => {
    _Order_Details.init();
});

(($ => {
    
    _Order_Details = {

        xhr: false,

        init() {

            _Base.handleEventHappeningLabel('.big-event-card');

            let carousel = new bootstrap.Carousel($('#ordersCarousel'));

            let currentDateTime = new Date(); 

            let cards = $('.big-event-card');

            cards.each(function (e) {

                let card = $(this);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);

                if (currentDateTime >= endDatetime) {

                    let button = card.find('#view-details-button');

                    let disabledButton = $('<button type="button" class="btn btn-secondary" disabled>Event Deleted Or Has Passed</button>');

                    disabledButton.insertAfter(button);

                    button.remove();
                }
            });
        }
    };
}))(jQuery);
