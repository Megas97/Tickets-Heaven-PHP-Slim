let _All_Events;

jQuery(document).ready(() => {
    _All_Events.init();
});

(($ => {
    
    _All_Events = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const addEventButton = $('#add-event-button');

            addEventButton.on('click', function (e) {

                base_url = window.location.origin;

                location.href = base_url + '/admin/events/add';

                return false;
            });

            let table = $('#eventsListTable').DataTable({

                initComplete: function() {

                    this.api().columns().every(function() {

                        const column = this;

                        if ($(column.header()).hasClass('dropdown-filter')) {

                            const select = $('<select class="mx-1 mb-2 mb-0 form-control-sm"><option value="">-- ' + $(column.header()).html() + ' --</option></select>')
                                .prependTo($('.dataTables_filter'))
                                .on('change', function() {
                                    const val = $.fn.dataTable.util.escapeRegex($(this).val());
                                    const regExSearch = '^' + val + '';

                                    column.search(val ? "^" + regExSearch : '', true, false, false).draw();
                                });

                            const sortVals = [];

                            column.data().unique().sort().each(function(d, j) {

                                const split = d.toString().split(', ');

                                split.forEach(function(val) {

                                    val = _Base.removeTags(val).trim();

                                    if ($.inArray(val, sortVals) === -1) {

                                        sortVals[j] = d;

                                        if (val != '') {

                                            select.append('<option value="' + val + '">' + val + '</option>');
                                        }
                                    }
                                });
                            });
                        }
                    });
                },
                dom: "<'row'<'col-sm-24 col-md-12'><'col-sm-24 col-md-12 d-flex justify-content-end mb-2'B>>" +
                        "<'row'<'col-sm-24 col-md-12'l><'col-sm-24 col-md-12'f>>" +
                        "<'row'<'col-sm-24'tr>>" +
                        "<'row'<'col-sm-24 col-md-10'i><'col-sm-24 col-md-14'p>>",
                deferRender: true,
                'pagingType': 'simple',
                'columnDefs': [
                    { visible: false, targets: [0, 3, 4, 6, 7] }, // id, start date, start time, end date, end time
                    { orderable: false, targets: [1, 9, 11] }, // image, venue, actions
                    { searchable: false, targets: [11] }, // actions
                ],
                'order': [
                    [0, 'asc']
                ],
                'lengthMenu': [
                    [20, 50, -1],
                    [20, 50, 'All']
                ],
                responsive: true,
                language: {
                    search: '_INPUT_',
                    searchPlaceholder: 'Search Events'
                },
                ajax: {
                    'url': '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'Event',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'event.id', className: 'text-center align-middle' },
                    { data: 'event.image', className: 'text-center align-middle' },
                    { data: 'event.name', className: 'text-center align-middle' },
                    { data: 'event.start_date', className: 'text-center align-middle' },
                    { data: 'event.start_time', className: 'text-center align-middle' },
                    { data: 'event.starts', className: 'text-center align-middle' },
                    { data: 'event.end_date', className: 'text-center align-middle' },
                    { data: 'event.end_time', className: 'text-center align-middle' },
                    { data: 'event.ends', className: 'text-center align-middle' },
                    { data: 'event.venue', className: 'text-center align-middle' },
                    { data: 'event.host', className: 'text-center align-middle' },
                    { data: 'event.actions', className: 'text-center align-middle' },
                ],
            });

            table.on('draw responsive-display', function() {

                let deleteButtons = $('.datatable-delete-button');
                
                $.each(deleteButtons, function() {
                    
                    let deleteButton = $(this);

                    deleteButton.on('click', function (e) {
                        
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
                                
                                _All_Events.xhr = $.ajax({
                                    url: '/datatable/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'modelData': 'Event',
                                        'modelId': deleteButton.data('id'),
                                        'csrf_name': $('#ajax_csrf_name').data('value'),
                                        'csrf_value': $('#ajax_csrf_value').data('value')
                                    },
                                    beforeSend() {
                                        if (_All_Events.xhr !== false) {
                                            _All_Events.xhr.abort();
                                        }
                                    }
                                }).done(response => {

                                    if (response !== false && response !== undefined && response !== '') {

                                        if (response.fragments.notify) {
                
                                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
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
                                                    
                                                    _All_Events.xhr = $.ajax({
                                                        url: '/datatable/delete',
                                                        type: 'POST',
                                                        dataType: 'json',
                                                        data: {
                                                            'modelData': 'Event',
                                                            'modelId': deleteButton.data('id'),
                                                            'delete_event_participants': true,
                                                            'csrf_name': $('#ajax_csrf_name').data('value'),
                                                            'csrf_value': $('#ajax_csrf_value').data('value')
                                                        },
                                                        beforeSend() {
                                                            if (_All_Events.xhr !== false) {
                                                                _All_Events.xhr.abort();
                                                            }
                                                        }
                                                    }).done(response => {

                                                        if (response !== false && response !== undefined && response !== '') {

                                                            if (response.fragments.notify) {
                
                                                                _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                                            }

                                                            table.ajax.reload();
                                                        }
                                                    }).fail(response => {
                                                        _Base.displayMessage("error", "Unable to process the event deletion. Please reload the page. If the error persists contact support.");
                                                    });
                                                }
                                            })

                                        } else {
                                            
                                            table.ajax.reload();
                                        }
                                    }
                                }).fail(response => {
                                    _Base.displayMessage("error", "Unable to process the event deletion. Please reload the page. If the error persists contact support.");
                                });
                            }
                        })
                    });
                });
            });
        }
    };
}))(jQuery);
