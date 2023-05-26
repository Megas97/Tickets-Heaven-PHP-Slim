let _Owner;

jQuery(document).ready(() => {
    _Owner.init();
});

(($ => {
    
    _Owner = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handlePanelButtonBrightnessAnimation();

            const pendingEventsButton = $('#pending-events-button');

            pendingEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/owner/events';

                return false;
            });

            const approvedEventsButton = $('#approved-events-button');

            approvedEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/owner/events/approved';

                return false;
            });

            const rejectedEventsButton = $('#rejected-events-button');

            rejectedEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/owner/events/rejected';

                return false;
            });

            const venuesButton = $('#venues-button');

            venuesButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/owner/venues';

                return false;
            });
        },
    };
}))(jQuery);
