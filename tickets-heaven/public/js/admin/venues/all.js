let _All_Venues;

jQuery(document).ready(() => {
    _All_Venues.init();
});

(($ => {
    
    _All_Venues = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const addVenueButton = $('#add-venue-button');

            addVenueButton.on('click', function (e) {

                base_url = window.location.origin;

                location.href = base_url + '/admin/venues/add';

                return false;
            });

            let table = $('#venuesListTable').DataTable({

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
                    { visible: false, targets: [0, 3, 4, 6, 7] }, // id, phone code, phone number, opens, closes
                    { orderable: false, targets: [1, 10] }, // image, actions
                    { searchable: false, targets: [10] }, // actions
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
                    searchPlaceholder: 'Search Venues'
                },
                ajax: {
                    'url': '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'Venue',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'venue.id', className: 'text-center align-middle' },
                    { data: 'venue.image', className: 'text-center align-middle' },
                    { data: 'venue.name', className: 'text-center align-middle' },
                    { data: 'venue.phone_code', className: 'text-center align-middle' },
                    { data: 'venue.phone_number', className: 'text-center align-middle' },
                    { data: 'venue.phone', className: 'text-center align-middle' },
                    { data: 'venue.opens', className: 'text-center align-middle' },
                    { data: 'venue.closes', className: 'text-center align-middle' },
                    { data: 'venue.work_time', className: 'text-center align-middle' },
                    { data: 'venue.owner', className: 'text-center align-middle' },
                    { data: 'venue.actions', className: 'text-center align-middle' },
                ]
            });

            table.on('draw responsive-display', function() {

                let deleteButtons = $('.datatable-delete-button');
                
                $.each(deleteButtons, function() {
                    
                    let deleteButton = $(this);

                    deleteButton.on('click', function (e) {
                        
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

                                _All_Venues.xhr = $.ajax({
                                    url: '/datatable/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'modelData': 'Venue',
                                        'modelId': deleteButton.data('id'),
                                        'csrf_name': $('#ajax_csrf_name').data('value'),
                                        'csrf_value': $('#ajax_csrf_value').data('value')
                                    },
                                    beforeSend() {
                                        if (_All_Venues.xhr !== false) {
                                            _All_Venues.xhr.abort();
                                        }
                                    }
                                }).done(response => {

                                    if (response !== false && response !== undefined && response !== '') {

                                        if (response.fragments.notify) {
                
                                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
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
                                                    
                                                    _All_Venues.xhr = $.ajax({
                                                        url: '/datatable/delete',
                                                        type: 'POST',
                                                        dataType: 'json',
                                                        data: {
                                                            'modelData': 'Venue',
                                                            'modelId': deleteButton.data('id'),
                                                            'delete_venue_events': true,
                                                            'csrf_name': $('#ajax_csrf_name').data('value'),
                                                            'csrf_value': $('#ajax_csrf_value').data('value')
                                                        },
                                                        beforeSend() {
                                                            if (_All_Venues.xhr !== false) {
                                                                _All_Venues.xhr.abort();
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
                                                        _Base.displayMessage("error", "Unable to process the venue deletion. Please reload the page. If the error persists contact support.");
                                                    });
                                                }
                                            })

                                        } else {

                                            table.ajax.reload();
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
        }
    };
}))(jQuery);
