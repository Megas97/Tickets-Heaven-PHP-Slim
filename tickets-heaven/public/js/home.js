let _Home;

jQuery(document).ready(() => {
    _Home.init();
});

(($ => {
    
    _Home = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.multi-events-card');

            if ($('#multiEventsCarousel').length > 0) {

                let eventsCarousel = new bootstrap.Carousel($('#multiEventsCarousel'));
            }

            if ($('#multiArtistsCarousel').length > 0) {
                
                let artistsCarousel = new bootstrap.Carousel($('#multiArtistsCarousel'));
            }

            if ($('#multiVenuesCarousel').length > 0) {

                let venuesCarousel = new bootstrap.Carousel($('#multiVenuesCarousel'));
            }

            _Base.handleMultipleCarouselBehavior($('#multiEventsCarousel'));

            _Base.handleMultipleCarouselBehavior($('#multiArtistsCarousel'));

            _Base.handleMultipleCarouselBehavior($('#multiVenuesCarousel'));

            _Base.handleCarouselCardTextTruncation($('.carousel-card-description'), 'home');

            let currentDateTime = new Date(); 

            let cards = $('.multi-events-card');
            
            cards.each(function (e) {
                
                let card = $(this);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);

                if (currentDateTime >= endDatetime) {

                    card.parent().remove();
                }
            });
        }
    };
}))(jQuery);
