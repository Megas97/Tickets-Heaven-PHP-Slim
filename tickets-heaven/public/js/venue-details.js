let _Venue_Details;

jQuery(document).ready(() => {
    _Venue_Details.init();
});

(($ => {
    
    _Venue_Details = {

        init() {
            
            _Base.handleEventHappeningLabel('.carousel-caption');

            _Base.handleCarouselCardTextTruncation($('.carousel-card-description'));
			
			if ($('#eventsCarousel').length > 0) {

				let carousel = new bootstrap.Carousel($('#eventsCarousel'));
			}

            let currentDateTime = new Date(); 

            let cards = $('.carousel-item');
            
            cards.each(function (e) {

                let card = $(this);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);
                
                if (currentDateTime >= endDatetime) {

                    card.remove();

                    let remainingCards = $('.carousel-item');

                    if (remainingCards.length > 0) {

                        $(remainingCards[0]).addClass('active');

                    } else {

                        $('#eventsCarousel').prev().remove();
                        
                        $('#eventsCarousel').remove();
                    }
                }
            });
        }
    };
}))(jQuery);
