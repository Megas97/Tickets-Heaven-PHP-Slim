let _Venues;

jQuery(document).ready(() => {
    _Venues.init();
});

(($ => {

    _Venues = {

        xhr: false,

        init() {

            let currentDateTime = new Date(); 

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

                $(this).next().html(events.length);
            });
        },
    };
}))(jQuery);
