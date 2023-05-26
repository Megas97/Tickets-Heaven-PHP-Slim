let _Artist;

jQuery(document).ready(() => {
    _Artist.init();
});

(($ => {
    
    _Artist = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handlePanelButtonBrightnessAnimation();

            const pendingEventsButton = $('#pending-events-button');

            pendingEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/artist/events/pending';

                return false;
            });

            const approvedEventsButton = $('#approved-events-button');

            approvedEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/artist/events/approved';

                return false;
            });

            const rejectedEventsButton = $('#rejected-events-button');

            rejectedEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/artist/events/rejected';

                return false;
            });

            const activeEventsButton = $('#active-events-button');

            activeEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/artist/events';

                return false;
            });

            const inactiveEventsButton = $('#inactive-events-button');

            inactiveEventsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/artist/events/inactive';

                return false;
            });
        },
    };
}))(jQuery);
