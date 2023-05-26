let _View_Event;

jQuery(document).ready(() => {
    _View_Event.init();
});

(($ => {
    
    _View_Event = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();
            
            $('#host').selectpicker();

            $('#venue').selectpicker();

            $('#artists').selectpicker();

            $('#currency-id').selectpicker();

            let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

            let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
            
            $('#starts').attr('min', localISOTime);

            $('#ends').attr('min', localISOTime);

            $('#start').attr('max', localISOTime);

            $('#end').attr('max', localISOTime);

            let now = new Date();

            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

            let formatted = now.toISOString().slice(0,16);

            $('#start').val(formatted);

            $('#end').val(formatted);

            $('#remove-event-picture').click(function() {

                if ($(this).is(':checked')) {

                    $('#event-picture').val('');
                }
            });
            
            $("#event-picture").on('change', function() {

                $("#remove-event-picture").prop("checked", false);
            });

            const updateEventButton = $('#update-event-button');

            let previousValues = {};

            $("#view-event-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            $("#view-event-form input[type=datetime-local]").each(function() {
                
                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).val();

                    previousValues[key] = value;
                }
            });

            updateEventButton.on('click', function (e) {

                $('#starts').removeAttr('min');

                $('#ends').removeAttr('min');

                let isFormValid = $('#view-event-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#view-event-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let file = $('#event-picture').prop("files")[0];

                let form = new FormData();

                form.append("name", $('#name').val());
                form.append("description", $('#description').val());
                form.append("location", $('#location').val());
                form.append("starts", $('#starts').val());
                form.append("ends", $('#ends').val());
                form.append("host", $('#host').val());
                form.append("venue", $('#venue').val());
                form.append("artists", $('#artists').val());
                form.append("currency_id", $('#currency-id').val());
                form.append("ticket_price", $('#ticket-price').val());
                form.append("remove_event_picture", $('#remove-event-picture').is(':checked') ? $('#remove-event-picture').val() : '');
                form.append("event_picture", file);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _View_Event.xhr = $.ajax({
                    url: '/admin/events/' + $('#current-id').val(),
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_View_Event.xhr !== false) {
                            _View_Event.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                        let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
                        
                        $('#starts').attr('min', localISOTime);

                        $('#ends').attr('min', localISOTime);

                        $("#view-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#view-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

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

                                $('#event-picture').val('');
                            }

                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                
                                if (key === 'venue' || key === 'host') {

                                    _Base.displayMessage("error", error);

                                    field.val('Event ' + key).change();

                                } else if (key === 'artists') {

                                    _Base.displayMessage("error", error.replace('Artists', 'Event participants'));

                                    $('#' + key).selectpicker('deselectAll');

                                } else if (key === 'starts' || key === 'ends') {

                                    _Base.displayMessage("error", error);

                                } else if (key === 'currency_id') {

                                    _Base.displayMessage("error", error.replace(' id', ''));

                                    field.val('XXX').change();

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#view-event-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'artists' || key === 'venue' || key === 'host' || key === 'currency_id') {

                                            $('#' + key.replace('_', '-')).selectpicker('destroy');

                                            $('#' + key.replace('_', '-')).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'artists' || key === 'venue' || key === 'host' || key === 'currency_id') {

                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });

                                $("#view-event-form input[type=datetime-local]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).val(previousValues[key]);
                                    }
                                });
                            });
                        }

                        if (response.fragments.updated_fields) {

                            $("#view-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val() !== '') {

                                    $(this).val('');
                                }
                            });

                            $("#view-event-form input[type=datetime-local]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val() !== '') {

                                    if ($(this).hasClass('is-invalid')) {

                                        $(this).removeClass('is-invalid');
                                    }
                                }
                            });

                            $.each(response.fragments.updated_fields, function (key, value) {

                                if (key === 'event_picture') {

                                    let currentTime = new Date().getTime();

                                    $('#view-event-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/event-pictures/0.jpg');
                                    
                                    let removeEventPictureCheckbox = $('#remove-event-picture');

                                    if (value) {

                                        removeEventPictureCheckbox.prop("disabled", false);

                                        $('#event-picture').val('');

                                    } else {

                                        removeEventPictureCheckbox.prop("checked", false);

                                        removeEventPictureCheckbox.prop("disabled", true);
                                    }

                                } else if (key === 'starts' || key === 'ends') {

                                    $("#view-event-form input[type=datetime-local]").each(function() {
                            
                                        if (!$(this).hasClass('is-invalid')) {

                                            let key = $(this).attr('name');

                                            previousValues[key] = value;
                                        }
                                    });
                                    
                                    let field = $('#' + key.replace("_", "-"));

                                    field.val(value);

                                } else if (key === 'artists') {

                                    let currentParticipants = [];

                                    $.each(value, function (k, v) {

                                        currentParticipants[v.id] = v;
                                    });

                                    currentParticipants = currentParticipants.filter(function (element) {
                                        return element !== undefined;
                                    });

                                    if (currentParticipants.length > 0) {

                                        let selectElement = $('#' + key);

                                        let currentlySelectedValue = selectElement.val();

                                        let htmlOptions = $(selectElement.html());

                                        let cleanOptions = [];

                                        $.each(htmlOptions, function (i, j) {

                                            let optionElement = $(j);
                                            
                                            if (optionElement.prop('tagName') == 'OPTION') {
                                                
                                                optionElement.html(optionElement.html().replace('*', ''));

                                                cleanOptions.push(j);
                                            }
                                        });
                                        
                                        $.each(cleanOptions, function (i, j) {

                                            let elem = $(j);

                                            if (elem.attr('selected')) {

                                                elem.attr('selected', false);
                                            }

                                            if (jQuery.inArray(elem.val(), currentlySelectedValue) !== -1) {

                                                elem.attr('selected', true);
                                            }
                                        });

                                        selectElement.html(cleanOptions);

                                        selectElement.selectpicker('destroy');

                                        selectElement.selectpicker();

                                        $.each(currentParticipants, function (k, v) {

                                            let selectElement = $('#' + key);

                                            let currentlySelectedValue = selectElement.val();

                                            let htmlOptions = $(selectElement.html());

                                            let cleanOptions = [];

                                            $.each(htmlOptions, function (i, j) {

                                                let optionElement = $(j);
                                                
                                                if (optionElement.prop('tagName') == 'OPTION') {

                                                    if (optionElement.val() == v.id && v.pivot.artist_approved == null) {
                                                        
                                                        optionElement.html('*' + optionElement.html());
                                                    }

                                                    cleanOptions.push(j);
                                                }
                                            });
                                            
                                            $.each(cleanOptions, function (i, j) {

                                                let elem = $(j);

                                                if (elem.attr('selected')) {

                                                    elem.attr('selected', false);
                                                }

                                                if (jQuery.inArray(elem.val(), currentlySelectedValue) !== -1) {

                                                    elem.attr('selected', true);
                                                }
                                            });

                                            selectElement.html(cleanOptions);

                                            selectElement.selectpicker('destroy');

                                            selectElement.selectpicker();
                                        });

                                    } else {

                                        let selectElement = $('#' + key);

                                        let currentlySelectedValue = selectElement.val();

                                        let htmlOptions = $(selectElement.html());

                                        let cleanOptions = [];

                                        $.each(htmlOptions, function (i, j) {

                                            let optionElement = $(j);
                                            
                                            if (optionElement.prop('tagName') == 'OPTION') {
                                                
                                                optionElement.html(optionElement.html().replace('*', ''));

                                                cleanOptions.push(j);
                                            }
                                        });
                                        
                                        $.each(cleanOptions, function (i, j) {

                                            let elem = $(j);

                                            if (elem.attr('selected')) {

                                                elem.attr('selected', false);
                                            }

                                            if (jQuery.inArray(elem.val(), currentlySelectedValue) !== -1) {

                                                elem.attr('selected', true);
                                            }
                                        });

                                        selectElement.html(cleanOptions);

                                        selectElement.selectpicker('destroy');

                                        selectElement.selectpicker();
                                    }

                                    let selectElement = $('#' + key);

                                    let currentlySelectedValue = selectElement.val();

                                    let htmlOptions = $(selectElement.html());

                                    let cleanOptions = [];

                                    $.each(htmlOptions, function (k, v) {
                                        
                                        if ($(v).prop('tagName') == 'OPTION') {

                                            cleanOptions.push(v);
                                        }
                                    });
                                    
                                    $.each(cleanOptions, function (k, v) {

                                        let elem = $(v);

                                        if (elem.attr('selected')) {

                                            elem.attr('selected', false);
                                        }

                                        if (jQuery.inArray(elem.val(), currentlySelectedValue) !== -1) {

                                            elem.attr('selected', true);
                                        }
                                    });

                                    selectElement.html(cleanOptions);

                                    selectElement.selectpicker('destroy');

                                    selectElement.selectpicker();
                                    
                                    previousValues[key] = selectElement.html();

                                } else if (key === 'currency_id') {

                                    $("#view-event-form select").each(function() {

                                        if (!$(this).hasClass('is-invalid')) {

                                            let key = $(this).attr('name');

                                            $(this).html(previousValues[key]);
                                        }
                                    });

                                    let field = $('#' + key.replace(/_/g, "-"));

                                    field.val(value).change();

                                    $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                    $('#' + key.replace(/_/g, "-")).selectpicker();

                                } else {

                                    let field = $('#' + key.replace("_", "-"));

                                    field.val('');
                                    
                                    field.attr('placeholder', key === 'ticket_price' ? parseFloat(value).toFixed(2) : value);

                                    field.attr('title', '');
                                    
                                    let copyButton = field.next().children().eq(0);

                                    copyButton.attr('disabled', false);
                                }
                            });

                        } else {

                            if (!response.fragments.errors) {

                                $("#view-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                    $(this).val('');
                                });

                                $("#view-event-form input[type=datetime-local]").each(function() {

                                    if ($(this).hasClass('is-invalid')) {

                                        $(this).removeClass('is-invalid');
                                    }
                                });
                            }
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the event updating process. Please reload the page. If the error persists contact support.");
                });
            });

            let table = $('#eventStatisticsTable').DataTable({
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
                    { data: 'order.ticket_quantity', className: 'text-center align-middle' },
                    { data: 'order.single_price', className: 'text-center align-middle' },
                    { data: 'order.total_price', className: 'text-center align-middle' },
                    { data: 'order.date', className: 'text-center align-middle' },
                ],
            });

            const viewStaticticsButton = $('#view-event-statistics-button');

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
                        'modelData': 'EventStatistics',
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

                    $('#total-event-income').html(response.totalIncome);

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
                        'modelData': 'EventStatistics',
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

                        $("#view-event-form input[type=datetime-local]").each(function() {

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

                        $('#total-event-income').html(response.totalIncome);

                        _Base.createDataTableDropdownFilters(table);

                        $("#view-event-form input[type=datetime-local]").each(function() {

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

            const deleteEventButton = $('#delete-event-button');

            deleteEventButton.on('click', function (e) {

                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Once deleted the event will be gone forever!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete the event!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("delete_event_button", $('#delete-event-button') ? true : false);
                        form.append("current_id", $('#current-id').val());
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_Event.xhr = $.ajax({
                            url: '/admin/events/' + $('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_Event.xhr !== false) {
                                    _View_Event.xhr.abort();
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

                                if (response.fragments.has_participants) {

                                    Swal.fire({
                                        title: 'Are you sure?',
                                        text: "Deleting this event will remove all participants from it!",
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonColor: '#d33',
                                        cancelButtonColor: '#3085d6',
                                        confirmButtonText: 'Yes, delete the event!',
                                        allowOutsideClick: false,
                                    }).then((result) => {

                                        if (result.isConfirmed) {

                                            let form = new FormData();
                        
                                            form.append("delete_event_button", $('#delete-event-button') ? true : false);
                                            form.append("delete_event_participants", true);
                                            form.append("current_id", $('#current-id').val());
                                            form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                            form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                            
                                            _View_Event.xhr = $.ajax({
                                                url: '/admin/events/' + $('#current-id').val(),
                                                type: 'POST',
                                                contentType: false,
                                                processData: false,
                                                data: form,
                                                beforeSend() {
                                                    if (_View_Event.xhr !== false) {
                                                        _View_Event.xhr.abort();
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
                                                        
                                                        if (response.fragments) {
                                                            
                                                            fragmentsString = '?fragments=' + JSON.stringify(response.fragments);
                                                        }
                                                        
                                                        location.href = base_url + response.fragments.redirectUrl + fragmentsString;
                                                        
                                                        return false;
                                                    }
                    
                                                }
                                            }).fail(response => {
                                                _Base.displayMessage("error", "Unable to process the event deletion. Please reload the page. If the error persists contact support.");
                                            });
                                        }
                                    })
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the event deletion. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });
        },
    };
}))(jQuery);
