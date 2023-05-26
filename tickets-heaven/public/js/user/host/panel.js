let _Host;

jQuery(document).ready(() => {
    _Host.init();
});

(($ => {
    
    _Host = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handlePanelButtonBrightnessAnimation();

            const addEventButton = $('#add-event-button');

            addEventButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/host/events/add';

                return false;
            });

            const activeEventsButton = $('#active-events-button');

            activeEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/host/events';

                return false;
            });

            const inActiveEventsButton = $('#inactive-events-button');

            inActiveEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/host/events/inactive';

                return false;
            });

            const promotionsButton = $('#promotions-button');

            promotionsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/host/promotions';

                return false;
            });
        },
    };
}))(jQuery);
