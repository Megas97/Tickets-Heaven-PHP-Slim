let _Edit_Venue;

jQuery(document).ready(() => {
    _Edit_Venue.init();
});

(($ => {
    
    _Edit_Venue = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.carousel-caption');

            _Base.handleCarouselCardTextTruncation($('.carousel-card-description'));

            let previousValues = {};

            if ($('[id*="eventsCarousel"]').length > 0) {
                
                let eventsCarousel = new bootstrap.Carousel($('[id*="eventsCarousel"]'));
            }

            if ($('[id*="inactiveEventsCarousel"]').length > 0) {

                let inactiveEventsCarousel = new bootstrap.Carousel($('[id*="inactiveEventsCarousel"]'));
            }

            $('.edit-modal').each(function () {

                let previousValues = {};

                $(this).on('hide.bs.modal', function (e) {

                    const pressedElement = document.activeElement;
                    
                    if ($(pressedElement).attr('id') !== 'update-venue-button') {
                        
                        const modal = $(this);

                        modal.find("#edit-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                            $(this).val('');
                        });
                        
                        for (let key in previousValues) {

                            let field = modal.find('#' + key.replace(/_/g, "-"));
                            
                            if (key === 'phone_code_id') {

                                field.val(previousValues[key + '_value']).change();

                                field.selectpicker('destroy');

                                field.selectpicker();

                            } else if (key === 'opens' || key === 'closes') {

                                field.val(previousValues[key]);

                            } else if (key === 'events') {

                                field.selectpicker('deselectAll');

                            } else {

                                if (field.hasClass('is-invalid')) {

                                    field.attr('placeholder', previousValues[key]);

                                    field.attr('title', '');

                                    field.removeClass('is-invalid');
                                }
                            }
                        }

                        modal.find('#venue-picture').val('');
                    }

                });

                $(this).on('show.bs.modal', function (e) {

                    const modal = $(this);
                    
                    modal.find('#phone-code-id').selectpicker();

                    modal.find('#remove-venue-picture-' + modal.find('#current-id').val()).click(function() {

                        if ($(this).is(':checked')) {

                            modal.find('#venue-picture').val('');
                        }
                    });
                    
                    modal.find("#venue-picture").on('change', function() {

                        modal.find("#remove-venue-picture-" + modal.find('#current-id').val()).prop("checked", false);
                    });

                    const updateVenueButton = modal.find('#update-venue-button');
                    
                    modal.find("#edit-venue-form select").each(function() {

                        if (!$(this).hasClass('is-invalid')) {

                            let key = $(this).attr('name');

                            let value = $(this).html();

                            previousValues[key] = value;

                            previousValues[key + '_value'] = $(this).val();
                        }
                    });
                    
                    if (!modal.find("#opens").hasClass('is-invalid')) {

                        let key = modal.find("#opens").attr('name');

                        let value = modal.find("#opens").val();

                        previousValues[key] = value;
                    }

                    if (!modal.find("#closes").hasClass('is-invalid')) {

                        let key = modal.find("#closes").attr('name');

                        let value = modal.find("#closes").val();

                        previousValues[key] = value;
                    }

                    updateVenueButton.off('click').on('click', function (e) {

                        let isFormValid = modal.find('#edit-venue-form')[0].checkValidity();

                        if (!isFormValid) {

                            modal.find('#edit-venue-form')[0].reportValidity();

                            return false;

                        } else {

                            e.preventDefault();
                        }

                        let file = modal.find('#venue-picture').prop("files")[0];

                        let form = new FormData();

                        form.append("name", modal.find('#name').val());
                        form.append("description", modal.find('#description').val());
                        form.append("address", modal.find('#address').val());
                        form.append("phone_code_id", modal.find('#phone-code-id').val());
                        form.append("phone_number", modal.find('#phone-number').val());
                        form.append("opens", modal.find('#opens').val());
                        form.append("closes", modal.find('#closes').val());
                        form.append("events", modal.find('#events').val());
                        form.append("remove_venue_picture", modal.find('#remove-venue-picture-' + modal.find('#current-id').val()).is(':checked') ? modal.find('#remove-venue-picture-' + modal.find('#current-id').val()).val() : '');
                        form.append("venue_picture", file);
                        form.append("csrf_name", modal.find('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", modal.find('#ajax_csrf_value').data('value'));

                        _Edit_Venue.xhr = $.ajax({
                            url: '/owner/venues/' + modal.find('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_Edit_Venue.xhr !== false) {
                                    _Edit_Venue.xhr.abort();
                                }
                            }
                        }).done(response => {

                            if (response !== false && response !== undefined && response !== '') {

                                modal.find("#edit-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {
                                    
                                    if (!$(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        let value = $(this).attr('placeholder');

                                        previousValues[key] = value;
                                    }
                                });
                                
                                modal.find("#edit-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).attr('placeholder', previousValues[key]);
                                    }

                                    $(this).removeClass('is-invalid');
                                });
                                
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

                                    if (response.fragments.notify.type === 'error') {

                                        modal.find('#venue-picture').val('');
                                    }

                                    _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                }

                                for (let key in previousValues) {

                                    if (!modal.find('#' + key.replace("_", "-")).hasClass('is-invalid')) {

                                        if (key !== 'events') {

                                            modal.find('#' + key.replace("_", "-")).attr('placeholder', previousValues[key]);
                                        }
                                    }
                                }
                                
                                if (response.fragments.errors) {

                                    $.each(response.fragments.errors, function (key, value) {

                                        let field = modal.find('#' + key.replace(/_/g, "-"));

                                        let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                        
                                        if (key === 'phone_code_id') {

                                            _Base.displayMessage("error", error.replace(" id", ""));

                                            field.val('+XXX').change();

                                        } else if (key === 'opens') {

                                            _Base.displayMessage("error", error);

                                        } else if (key === 'closes') {

                                            // Needs to be separate than 'opens'

                                        } else if (key === 'events') {

                                            _Base.displayMessage("error", error);

                                        } else {

                                            previousValues[key] = field.attr('placeholder');

                                            field.val('');

                                            field.attr('placeholder', error);

                                            field.attr('title', error);
                                        }

                                        field.addClass('is-invalid');

                                        modal.find("#edit-venue-form select").each(function() {
                                    
                                            if ($(this).hasClass('is-invalid')) {

                                                let key = $(this).attr('name');

                                                $(this).html(previousValues[key]);

                                                if (key === 'phone_code_id' || key === 'events') {
                                                    
                                                    modal.find('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                                    modal.find('#' + key.replace(/_/g, "-")).selectpicker();
                                                }
                                            }
                
                                            $(this).removeClass('is-invalid');

                                            if (key === 'phone_code_id' || key === 'events') {

                                                $(this).parent().removeClass('is-invalid');
                                            }
                                        });
                                        
                                        if (modal.find("#opens").hasClass('is-invalid')) {

                                            let key = modal.find("#opens").attr('name');

                                            modal.find("#opens").val(previousValues[key]);
                                        }

                                        if (modal.find("#closes").hasClass('is-invalid')) {

                                            let key = modal.find("#closes").attr('name');

                                            modal.find("#closes").val(previousValues[key]);
                                        }
                                    });
                                }
                                
                                if (response.fragments.updated_fields) {

                                    modal.modal('hide');

                                    modal.find("#edit-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                                        if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val('') !== '') {

                                            $(this).val('');
                                        }
                                    });

                                    modal.find("#edit-venue-form input[type=time]").each(function() {

                                        if ($(this).hasClass('is-invalid')) {

                                            $(this).removeClass('is-invalid');
                                        }
                                    });
                                    
                                    $.each(response.fragments.updated_fields, function (key, value) {

                                        if (key === 'venue_picture') {

                                            let currentTime = new Date().getTime();

                                            modal.find('#view-venue-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/venue-pictures/0.jpg');
                                            
                                            let removeVenuePictureCheckbox = modal.find('#remove-venue-picture-' + modal.find('#current-id').val());

                                            if (value) {

                                                removeVenuePictureCheckbox.prop("disabled", false);

                                                modal.find('#venue-picture').val('');

                                            } else {

                                                removeVenuePictureCheckbox.prop("checked", false);

                                                removeVenuePictureCheckbox.prop("disabled", true);
                                            }

                                        } else if (key === 'phone_code_id') {
                                            
                                            modal.find("#edit-venue-form select").each(function() {

                                                if (!$(this).hasClass('is-invalid')) {

                                                    let key = $(this).attr('name');

                                                    $(this).html(previousValues[key]);
                                                }
                                            });

                                            let field = modal.find('#' + key.replace(/_/g, "-"));
                                            
                                            field.val(value).change();

                                            modal.find('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                            modal.find('#' + key.replace(/_/g, "-")).selectpicker();

                                            previousValues[key + '_value'] = value;

                                        } else if (key === 'opens' || key === 'closes') {
                                            
                                            if (!modal.find("#opens").hasClass('is-invalid')) {

                                                let key = modal.find("#opens").attr('name');

                                                let value = modal.find("#opens").val();

                                                previousValues[key] = value;
                                            }

                                            if (!modal.find("#closes").hasClass('is-invalid')) {

                                                let key = modal.find("#closes").attr('name');

                                                let value = modal.find("#closes").val();

                                                previousValues[key] = value;
                                            }
                                            
                                            let field = modal.find('#' + key.replace("_", "-"));

                                            field.val(value);

                                        } else if (key === 'events') {

                                            let field = modal.find('#' + key.replace(/_/g, "-"));

                                            field.selectpicker('deselectAll');

                                            field.children().each(function (i) {

                                                if (i > 0) {

                                                    $(this).remove();
                                                }
                                            });

                                            let options = '';

                                            for (let key in value) {

                                                options += '<option value="' + value[key].id + '">' + value[key].name + '</option>';
                                            }

                                            field.append(options);

                                            field.selectpicker('destroy');

                                            field.selectpicker();

                                            let updatedEventsIDsArray = [];

                                            let eventCarouselCardsIDsArray = [];

                                            let eventCarouselCards = $('.carousel-item');

                                            eventCarouselCards.each(function () {

                                                if ($(this).data('venue-id') == response.fragments.updated_fields['venue_id']) {

                                                    eventCarouselCardsIDsArray.push($(this).data('event-id'));
                                                }
                                            });

                                            updatedEventsIDsArray = value.map(function (value) {

                                                return value.id;
                                            });

                                            let eventCarouselCardsToDeleteIDsArray = eventCarouselCardsIDsArray.filter(x => !updatedEventsIDsArray.includes(x));

                                            for (let eventId of eventCarouselCardsToDeleteIDsArray) {

                                                let elementToRemove = $('[data-event-id="' + eventId + '"]');

                                                if (elementToRemove.hasClass('active')) {

                                                    elementToRemove.next().addClass('active');
                                                }

                                                $('[data-event-id="' + eventId + '"]').remove();
                                            };

                                            let carousel = $('#eventsCarousel' + response.fragments.updated_fields['venue_id']);

                                            let eventCards = carousel.children().eq(0).children().length;

                                            if (eventCards > 0) {

                                                carousel = $('#inactiveEventsCarousel' + response.fragments.updated_fields['venue_id']);

                                                eventCards = carousel.children().eq(0).children().length;
                                            }
                                            
                                            if (eventCards == 0) {

                                                carousel.prev().prev().remove();

                                                carousel.prev().remove();
                                                
                                                carousel.remove();
                                            }

                                        } else {

                                            let field = modal.find('#' + key.replace("_", "-"));

                                            previousValues[key] = value;

                                            field.val('');

                                            field.attr('placeholder', value);

                                            field.attr('title', '');

                                            let copyButton = field.next().children().eq(0);

                                            copyButton.attr('disabled', false);
                                        }
                                        
                                        modal.parent().find('.' + key).each(function () {

                                            if (key === 'events') {

                                                $(this).html(value.length);

                                                modal.parent().find('.active-events').html(response.fragments.updated_fields['active_events'].length);

                                                modal.parent().find('.inactive-events').html(response.fragments.updated_fields['inactive_events'].length);

                                            } else {

                                                $(this).html(value);
                                            }
                                        });

                                        if (key === 'phone_code' || key === 'phone_number') {

                                            modal.parent().find('.phone').each(function () {

                                                const phone_code = response.fragments.updated_fields['phone_code'];

                                                const phone_number = response.fragments.updated_fields['phone_number'];

                                                $(this).attr('href', 'tel:' + phone_code + ' ' + phone_number);
                                            });
                                        }

                                        if (key === 'address') {

                                            modal.parent().find('.map').each(function () {

                                                $(this).attr('src', 'https://maps.google.com/maps?q=' + encodeURIComponent(value) + '&ie=UTF8&iwloc=&output=embed');
                                            });
                                        }

                                        if (key === 'venue_picture') {

                                            modal.parent().find('.image').each(function () {

                                                let currentTime = new Date().getTime();

                                                $(this).attr("src", value ? (value + '?' + currentTime) : '/uploads/venue-pictures/0.jpg');
                                            });
                                        }
                                    });

                                } else {

                                    if (!response.fragments.errors && response.fragments.notify.field !== 'venue_picture') {

                                        modal.modal('hide');

                                        modal.find("#edit-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                                            $(this).val('');
                                        });

                                        modal.find("#edit-venue-form input[type=time]").each(function() {

                                            if ($(this).hasClass('is-invalid')) {
                                                
                                                $(this).removeClass('is-invalid');
                                            }
                                        });
                                    }
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the venue editing process. Please reload the page. If the error persists contact support.");
                        });
                    });

                    const deleteVenueButton = modal.find('#delete-venue-button');

                    deleteVenueButton.off('click').on('click', function (e) {

                        e.preventDefault();

                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Once deleted the venue will be gone forever!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete the venue!',
                            allowOutsideClick: false,
                        }).then((result) => {

                            if (result.isConfirmed) {

                                let form = new FormData();
                                
                                form.append("delete_venue_button", modal.find('#delete-venue-button') ? true : false);
                                form.append("current_id", modal.find('#current-id').val());
                                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                
                                _Edit_Venue.xhr = $.ajax({
                                    url: '/owner/venues/' + modal.find('#current-id').val(),
                                    type: 'POST',
                                    contentType: false,
                                    processData: false,
                                    data: form,
                                    beforeSend() {
                                        if (_Edit_Venue.xhr !== false) {
                                            _Edit_Venue.xhr.abort();
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

                                        if (response.fragments.updated_fields) {

                                            let card = $('#card-' + response.fragments.updated_fields['venue_id']);

                                            card.remove();
                                            
                                            modal.modal('hide');
                                        }

                                        if (response.fragments.has_events) {

                                            Swal.fire({
                                                title: 'Are you sure?',
                                                text: "Deleting this venue will leave all events hosted in it without a set venue!",
                                                icon: 'question',
                                                showCancelButton: true,
                                                confirmButtonColor: '#d33',
                                                cancelButtonColor: '#3085d6',
                                                confirmButtonText: 'Yes, delete the venue!',
                                                allowOutsideClick: false,
                                            }).then((result) => {

                                                if (result.isConfirmed) {

                                                    let form = new FormData();
                                
                                                    form.append("delete_venue_button", modal.find('#delete-venue-button') ? true : false);
                                                    form.append("delete_venue_events", true);
                                                    form.append("current_id", modal.find('#current-id').val());
                                                    form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                                    form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                                    
                                                    _Edit_Venue.xhr = $.ajax({
                                                        url: '/owner/venues/' + modal.find('#current-id').val(),
                                                        type: 'POST',
                                                        contentType: false,
                                                        processData: false,
                                                        data: form,
                                                        beforeSend() {
                                                            if (_Edit_Venue.xhr !== false) {
                                                                _Edit_Venue.xhr.abort();
                                                            }
                                                        }
                                                    }).done(response => {

                                                        if (response !== false && response !== undefined && response !== '') {
                                                            
                                                            if (response.fragments.notify) {
        
                                                                _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                                            }

                                                            let card = $('#card-' + response.fragments.updated_fields['venue_id']);

                                                            card.remove();
                                                            
                                                            modal.modal('hide');
                                                        }
                                                    }).fail(response => {
                                                        _Base.displayMessage("error", "Unable to process the venue deletion. Please reload the page. If the error persists contact support.");
                                                    });
                                                }
                                            })
                                        }
                                    }
                                }).fail(response => {
                                    _Base.displayMessage("error", "Unable to process the venue deletion. Please reload the page. If the error persists contact support.");
                                });
                            }
                        })
                    });
                });
            });

            const viewStaticticsButtons = $('.view-venue-statistics-button');
                    
            const statisticsLoaded = [];

            viewStaticticsButtons.each(function (e) {

                const viewStaticticsButton = $(this);
                
                const venueId = viewStaticticsButton.attr('href').replace('#view-venue-statistics-modal-', '');

                let card = $('#card-' + venueId);

                let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);

                card.find('#start').attr('max', localISOTime);

                card.find('#end').attr('max', localISOTime);

                let now = new Date();

                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

                let formatted = now.toISOString().slice(0,16);

                card.find('#start').val(formatted);

                card.find('#end').val(formatted);

                statisticsLoaded[venueId] = false;

                card.find("#view-venue-statistics-modal-" + venueId + " input[type=datetime-local]").each(function() {

                    if (!$(this).hasClass('is-invalid')) {

                        let key = $(this).attr('name');
    
                        let value = $(this).val();
    
                        previousValues[key] = value;
                    }
                });

                let table = card.find('#venueStatisticsTable').DataTable({
                    dom: "<'row'<'col-sm-24 col-md-12'><'col-sm-24 col-md-12 d-flex justify-content-end mb-2'B>>" +
                            "<'row'<'col-sm-24 col-md-12'l><'col-sm-24 col-md-12'f>>" +
                            "<'row'<'col-sm-24'tr>>" +
                            "<'row'<'col-sm-24 col-md-10'i><'col-sm-24 col-md-14'p>>",
                    deferRender: true,
                    'pagingType': 'simple',
                    'columnDefs': [
                        { visible: false, targets: [0] },
                        { orderable: false, targets: [1, 2, 3, 4, 5] },
                        { searchable: false, targets: [0] },
                    ],
                    'order': [
                        [0, 'desc']
                    ],
                    'lengthMenu': [
                        [20, 50, -1],
                        [20, 50, 'All']
                    ],
                    responsive: true,
                    language: {
                        search: '_INPUT_',
                        searchPlaceholder: 'Search Event Statistics'
                    },
                    data: [],
                    columns: [
                        { data: 'order.id', className: 'text-center align-middle' },
                        { data: 'order.user', className: 'text-center align-middle' },
                        { data: 'order.event', className: 'text-center align-middle' },
                        { data: 'order.ticket_quantity', className: 'text-center align-middle' },
                        { data: 'order.single_price', className: 'text-center align-middle' },
                        { data: 'order.total_price', className: 'text-center align-middle' },
                        { data: 'order.date', className: 'text-center align-middle' },
                    ],
                });

                viewStaticticsButton.on('click', function (e) {

                    if (statisticsLoaded[venueId]) {
    
                        return false;
                    }
                    
                    $.ajax({
                        url: '/datatable/read',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'modelData': 'VenueStatistics',
                            'current_id': card.find('#current-id').val(),
                            'start': card.find('#start').val(),
                            'end': card.find('#end').val(),
                            'csrf_name': $('#ajax_csrf_name').data('value'),
                            'csrf_value': $('#ajax_csrf_value').data('value')
                        }
                    }).done(function (response) {
                        
                        if (window.matchMedia("(max-width: 1025px)").matches) {
                        
                            table.columns.adjust().draw();
                        }
                        
                        table.clear().draw();
    
                        table.search('').draw();
    
                        table.columns().search('').draw();
    
                        table.rows.add(response.data).draw();

                        card.find('#total-sold-tickets').html(response.totalTickets);
    
                        card.find('#total-venue-income').html(response.totalIncome);
    
                        _Base.createDataTableDropdownFilters(table);
    
                        statisticsLoaded[venueId] = true;
                    })
                });

                const viewStatisticsResultsButton = card.find('#view-statistics-results-button');

                viewStatisticsResultsButton.on('click', function (e) {

                    if (window.matchMedia("(max-width: 1025px)").matches) {
                        
                        table.columns.adjust().draw();
                    }
                    
                    $.ajax({
                        url: '/datatable/read',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'modelData': 'VenueStatistics',
                            'current_id': card.find('#current-id').val(),
                            'start': card.find('#start').val(),
                            'end': card.find('#end').val(),
                            'csrf_name': $('#ajax_csrf_name').data('value'),
                            'csrf_value': $('#ajax_csrf_value').data('value')
                        }
                    }).done(function (response) {
    
                        if (response.fragments && response.fragments.errors) {
    
                            $.each(response.fragments.errors, function (key, value) {
    
                                let field = card.find('#' + key.replace(/_/g, "-"));
    
                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
    
                                if (key === 'start' || key === 'end') {
    
                                    _Base.displayMessage("error", error);
    
                                } else {
    
                                    field.val('');
    
                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }
    
                                field.addClass('is-invalid');
                            });
    
                            card.find("#view-venue-statistics-modal-" + venueId + " input[type=datetime-local]").each(function() {
    
                                if ($(this).hasClass('is-invalid')) {
    
                                    let key = $(this).attr('name');
    
                                    $(this).val(previousValues[key]);
                                }
                            });
    
                        } else {
                        
                            table.clear().draw();
    
                            table.search('').draw();
    
                            table.columns().search('').draw();
                            
                            table.rows.add(response.data).draw();
    
                            card.find('.dataTables_filter').children().each(function (e) {
    
                                if ($(this).is('select')) {
    
                                    $(this).remove();
                                }
                            });
    
                            card.find('#total-sold-tickets').html(response.totalTickets);
    
                            card.find('#total-venue-income').html(response.totalIncome);
    
                            _Base.createDataTableDropdownFilters(table);
    
                            card.find("#view-venue-statistics-modal-" + venueId + " input[type=datetime-local]").each(function() {
    
                                if ($(this).hasClass('is-invalid')) {
    
                                    $(this).removeClass('is-invalid');
                                }
    
                                if (!$(this).hasClass('is-invalid')) {
    
                                    let key = $(this).attr('name');
                
                                    let value = $(this).val();
                
                                    previousValues[key] = value;
                                }
                            });
                        }
                    })
                });
            });
        },
    };
}))(jQuery);
