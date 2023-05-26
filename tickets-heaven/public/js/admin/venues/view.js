let _View_Venue;

jQuery(document).ready(() => {
    _View_Venue.init();
});

(($ => {
    
    _View_Venue = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.event-card');

            $('#phone-code-id').selectpicker();

            $('#owner').selectpicker();

            $('#phone-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));
            });

            $('#remove-venue-picture').click(function() {

                if ($(this).is(':checked')) {

                    $('#venue-picture').val('');
                }
            });
            
            $("#venue-picture").on('change', function() {

                $("#remove-venue-picture").prop("checked", false);
            });

            let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

            let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);

            $('#start').attr('max', localISOTime);

            $('#end').attr('max', localISOTime);

            let now = new Date();

            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

            let formatted = now.toISOString().slice(0,16);

            $('#start').val(formatted);

            $('#end').val(formatted);

            const updateVenueButton = $('#update-venue-button');

            let previousValues = {};

            $("#view-venue-form input[type=datetime-local]").each(function() {
                
                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).val();

                    previousValues[key] = value;
                }
            });

            $("#view-venue-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });
            
            if (!$("#opens").hasClass('is-invalid')) {

                let key = $("#opens").attr('name');

                let value = $("#opens").val();

                previousValues[key] = value;
            }

            if (!$("#closes").hasClass('is-invalid')) {

                let key = $("#closes").attr('name');

                let value = $("#closes").val();

                previousValues[key] = value;
            }

            updateVenueButton.on('click', function (e) {

                let isFormValid = $('#view-venue-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#view-venue-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let file = $('#venue-picture').prop("files")[0];

                let form = new FormData();

                form.append("name", $('#name').val());
                form.append("description", $('#description').val());
                form.append("address", $('#address').val());
                form.append("phone_code_id", $('#phone-code-id').val());
                form.append("phone_number", $('#phone-number').val());
                form.append("opens", $('#opens').val());
                form.append("closes", $('#closes').val());
                form.append("owner", $('#owner').val());
                form.append("remove_venue_picture", $('#remove-venue-picture').is(':checked') ? $('#remove-venue-picture').val() : '');
                form.append("venue_picture", file);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _View_Venue.xhr = $.ajax({
                    url: '/admin/venues/' + $('#current-id').val(),
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_View_Venue.xhr !== false) {
                            _View_Venue.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        $("#view-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#view-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

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

                                $('#venue-picture').val('');
                            }

                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }
                        
                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                
                                if (key === 'owner') {

                                    _Base.displayMessage("error", error);

                                    field.val('Venue owner').change();

                                } else if (key === 'phone_code_id') {

                                    _Base.displayMessage("error", error.replace(" id", ""));

                                    field.val('+XXX').change();

                                } else if (key === 'opens') {

                                    _Base.displayMessage("error", error);

                                } else if (key === 'closes') {

                                    // Needs to be separate than 'opens'

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#view-venue-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'owner' || key === 'phone_code_id') {

                                            $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                            $('#' + key.replace(/_/g, "-")).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'owner' || key === 'phone_code_id') {

                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });
                                
                                if ($("#opens").hasClass('is-invalid')) {

                                    let key = $("#opens").attr('name');

                                    $("#opens").val(previousValues[key]);
                                }

                                if ($("#closes").hasClass('is-invalid')) {

                                    let key = $("#closes").attr('name');

                                    $("#closes").val(previousValues[key]);
                                }
                            });
                        }
                        
                        if (response.fragments.updated_fields) {

                            $("#view-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val('') !== '') {

                                    $(this).val('');
                                }
                            });

                            $("#view-venue-form input[type=time]").each(function() {

                                if ($(this).hasClass('is-invalid')) {

                                    $(this).removeClass('is-invalid');
                                }
                            });
                            
                            $.each(response.fragments.updated_fields, function (key, value) {

                                if (key === 'venue_picture') {

                                    let currentTime = new Date().getTime();

                                    $('#view-venue-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/venue-pictures/0.jpg');
                                    
                                    let removeVenuePictureCheckbox = $('#remove-venue-picture');

                                    if (value) {

                                        removeVenuePictureCheckbox.prop("disabled", false);

                                        $('#venue-picture').val('');

                                    } else {

                                        removeVenuePictureCheckbox.prop("checked", false);

                                        removeVenuePictureCheckbox.prop("disabled", true);
                                    }

                                } else if (key === 'phone_code_id') {
                                    
                                    $("#view-venue-form select").each(function() {

                                        if (!$(this).hasClass('is-invalid')) {

                                            let key = $(this).attr('name');

                                            $(this).html(previousValues[key]);
                                        }
                                    });

                                    let field = $('#' + key.replace(/_/g, "-"));

                                    field.val(value).change();

                                    $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                    $('#' + key.replace(/_/g, "-")).selectpicker();

                                } else if (key === 'opens' || key === 'closes') {
                                    
                                    if (!$("#opens").hasClass('is-invalid')) {

                                        let key = $("#opens").attr('name');

                                        let value = $("#opens").val();

                                        previousValues[key] = value;
                                    }

                                    if (!$("#closes").hasClass('is-invalid')) {

                                        let key = $("#closes").attr('name');
                                        
                                        let value = $("#closes").val();

                                        previousValues[key] = value;
                                    }
                                    
                                    let field = $('#' + key.replace("_", "-"));

                                    field.val(value);

                                } else {

                                    let field = $('#' + key.replace("_", "-"));

                                    field.val('');

                                    field.attr('placeholder', value);

                                    field.attr('title', '');

                                    let copyButton = field.next().children().eq(0);

                                    copyButton.attr('disabled', false);
                                }
                            });

                        } else {

                            if (!response.fragments.errors) {

                                $("#view-venue-form input[type=text],[type=password],[type=email], textarea[type=text]").each(function() {

                                    $(this).val('');
                                });

                                $("#view-venue-form input[type=time]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        $(this).removeClass('is-invalid');
                                    }
                                });
                            }
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the venue updating process. Please reload the page. If the error persists contact support.");
                });
            });

            $('.view-modal').each(function () {
                
                $(this).on('show.bs.modal', function (e) {
                    
                    const modal = $(this);

                    const removeVenueEventButtons = modal.find('.remove-venue-event-button');
                    
                    removeVenueEventButtons.each(function (e) {
                        
                        const removeVenueEventButton = $(this);

                        removeVenueEventButton.on('click', function (e) {

                            e.preventDefault();

                            const eventId = removeVenueEventButton.parent().find('.event-id').val();

                            Swal.fire({
                                title: 'Are you sure?',
                                text: "Once removed the event can be added back again if desired!",
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, remove the event!',
                                allowOutsideClick: false,
                            }).then((result) => {
            
                                if (result.isConfirmed) {
            
                                    let form = new FormData();
                                    
                                    form.append("remove_venue_event_button", removeVenueEventButton ? true : false);
                                    form.append("event_id", eventId);
                                    form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                    form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                    
                                    _View_Venue.xhr = $.ajax({
                                        url: '/admin/venues/' + $('#current-id').val(),
                                        type: 'POST',
                                        contentType: false,
                                        processData: false,
                                        data: form,
                                        beforeSend() {
                                            if (_View_Venue.xhr !== false) {
                                                _View_Venue.xhr.abort();
                                            }
                                        }
                                    }).done(response => {
            
                                        if (response !== false && response !== undefined && response !== '') {
                                            
                                            if (response.fragments.notify) {
                                    
                                                _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                            }

                                            let cards = $('#venue-details-card').find('.card');

                                            if (response.fragments.venueEvents.length == 0) {

                                                cards.each(function (e) {

                                                    cards.remove();
                                                });

                                                let modal = $('#view-venue-events-modal');

                                                let noEventsText = $('<div class="row mt-sm-4 no-venue-events-text">' +
                                                                        '<div class="text-center"><h5>There are no events in this venue yet.</h5></div>' +
                                                                    '</div>');

                                                noEventsText.prependTo(modal.find('.modal-body'));

                                                modal.modal('hide');

                                            } else {
                                                
                                                cards.each(function (e) {

                                                    let card = $(this);

                                                    if (jQuery.inArray(parseInt(card.attr('id').replace('venue-event-', '')), response.fragments.venueEventsIDs) === -1) {

                                                        card.remove();
                                                    }
                                                });
                                            }
                                        }
                                    }).fail(response => {
                                        _Base.displayMessage("error", "Unable to process the event removal. Please reload the page. If the error persists contact support.");
                                    });
                                }
                            })
                        });
                    });
                });
            });

            let table = $('#venueStatisticsTable').DataTable({
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
                    searchPlaceholder: 'Search Venue Statistics'
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

            const viewStaticticsButton = $('#view-venue-statistics-button');

            let statisticsLoaded = false;

            viewStaticticsButton.on('click', function (e) {

                if (statisticsLoaded) {

                    return false;
                }
                
                $.ajax({
                    url: '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'VenueStatistics',
                        'current_id': $('#current-id').val(),
                        'start': $('#start').val(),
                        'end': $('#end').val(),
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

                    $('#total-sold-tickets').html(response.totalTickets);

                    $('#total-venue-income').html(response.totalIncome);

                    _Base.createDataTableDropdownFilters(table);

                    statisticsLoaded = true;
                })
            });

            const viewStatisticsResultsButton = $('#view-statistics-results-button');

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
                        'current_id': $('#current-id').val(),
                        'start': $('#start').val(),
                        'end': $('#end').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                }).done(function (response) {

                    if (response.fragments && response.fragments.errors) {

                        $.each(response.fragments.errors, function (key, value) {

                            let field = $('#' + key.replace(/_/g, "-"));

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

                        $("#view-venue-form input[type=datetime-local]").each(function() {

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
                        
                        $('.dataTables_filter').children().each(function (e) {

                            if ($(this).is('select')) {

                                $(this).remove();
                            }
                        });

                        $('#total-sold-tickets').html(response.totalTickets);

                        $('#total-venue-income').html(response.totalIncome);

                        _Base.createDataTableDropdownFilters(table);

                        $("#view-venue-form input[type=datetime-local]").each(function() {

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

            const deleteVenueButton = $('#delete-venue-button');

            deleteVenueButton.on('click', function (e) {

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
                        
                        form.append("delete_venue_button", $('#delete-venue-button') ? true : false);
                        form.append("current_id", $('#current-id').val());
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_Venue.xhr = $.ajax({
                            url: '/admin/venues/' + $('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_Venue.xhr !== false) {
                                    _View_Venue.xhr.abort();
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
                        
                                            form.append("delete_venue_button", $('#delete-venue-button') ? true : false);
                                            form.append("delete_venue_events", true);
                                            form.append("current_id", $('#current-id').val());
                                            form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                            form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                            
                                            _View_Venue.xhr = $.ajax({
                                                url: '/admin/venues/' + $('#current-id').val(),
                                                type: 'POST',
                                                contentType: false,
                                                processData: false,
                                                data: form,
                                                beforeSend() {
                                                    if (_View_Venue.xhr !== false) {
                                                        _View_Venue.xhr.abort();
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
        },
    };
}))(jQuery);
