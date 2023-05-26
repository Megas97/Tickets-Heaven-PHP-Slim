let _Admin;

jQuery(document).ready(() => {
    _Admin.init();
});

(($ => {
    
    _Admin = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handlePanelButtonBrightnessAnimation();

            const venuesButton = $('#venues-button');

            venuesButton.on('click', function (e) {

                base_url = window.location.origin;

                location.href = base_url + '/admin/venues';

                return false;
            });

            const eventsButton = $('#events-button');

            eventsButton.on('click', function (e) {

                base_url = window.location.origin;

                location.href = base_url + '/admin/events';

                return false;
            });

            const usersButton = $('#users-button');

            usersButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/admin/users';

                return false;
            });

            const supportTicketsButton = $('#support-tickets-button');

            supportTicketsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/admin/support';

                return false;
            });

            const promotionsButton = $('#promotions-button');

            promotionsButton.on('click', function (e) {
                
                base_url = window.location.origin;

                location.href = base_url + '/admin/promotions';

                return false;
            });
        },
    };
}))(jQuery);
