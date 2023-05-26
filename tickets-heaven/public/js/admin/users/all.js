let _All_Users;

jQuery(document).ready(() => {
    _All_Users.init();
});

(($ => {
    
    _All_Users = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();

            let table = $('#usersListTable').DataTable({

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
                    { visible: false, targets: [0, 4, 5, 7, 8, 13] }, // id, first name, last name, phone code, phone number, joined date
                    { orderable: false, targets: [1, 13, 14] }, // image, joined date, actions
                    { searchable: false, targets: [14] }, // actions
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
                        'modelData': 'User',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'user.id', className: 'text-center align-middle' },
                    { data: 'user.image', className: 'text-center align-middle' },
                    { data: 'user.username', className: 'text-center align-middle' },
                    { data: 'user.email', className: 'text-center align-middle' },
                    { data: 'user.first_name', className: 'text-center align-middle' },
                    { data: 'user.last_name', className: 'text-center align-middle' },
                    { data: 'user.full_name', className: 'text-center align-middle' },
                    { data: 'user.phone_code', className: 'text-center align-middle' },
                    { data: 'user.phone_number', className: 'text-center align-middle' },
                    { data: 'user.phone', className: 'text-center align-middle' },
                    { data: 'user.active', className: 'text-center align-middle' },
                    { data: 'user.role', className: 'text-center align-middle' },
                    { data: 'user.joined', className: 'text-center align-middle' },
                    { data: 'user.joined_date', className: 'text-center align-middle' },
                    { data: 'user.actions', className: 'text-center align-middle' },
                ],
            });

            table.on('draw responsive-display', function() {

                let deleteButtons = $('.datatable-delete-button');
                
                $.each(deleteButtons, function() {
                    
                    let deleteButton = $(this);

                    deleteButton.on('click', function (e) {
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Once deleted the user will be gone forever!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete the user!',
                            allowOutsideClick: false,
                        }).then((result) => {

                            if (result.isConfirmed) {

                                _All_Users.xhr = $.ajax({
                                    url: '/datatable/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'modelData': 'User',
                                        'modelId': deleteButton.data('username'),
                                        'csrf_name': $('#ajax_csrf_name').data('value'),
                                        'csrf_value': $('#ajax_csrf_value').data('value')
                                    },
                                    beforeSend() {
                                        if (_All_Users.xhr !== false) {
                                            _All_Users.xhr.abort();
                                        }
                                    }
                                }).done(response => {

                                    if (response !== false && response !== undefined && response !== '') {

                                        if (response.fragments.notify) {
                
                                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                        }

                                        let type = '';

                                        let message = '';

                                        let keyword = '';

                                        if (response.fragments.owner_has_venues) {

                                            type = 'owner';

                                            message = 'Deleting this owner will leave all their venues without a set owner!';

                                            keyword = 'venues';

                                        } else if (response.fragments.host_has_events) {

                                            type = 'host';

                                            message = 'Deleting this host will leave all their events without a set host!';

                                            keyword = 'events';

                                        } else if (response.fragments.artist_has_events) {

                                            type = 'artist';

                                            message = 'Deleting this artist will remove them from all events where they are participating!';

                                            keyword = 'events';
                                        }

                                        if (type != '' && message != '' && keyword != '') {

                                            Swal.fire({
                                                title: 'Are you sure?',
                                                text: message,
                                                icon: 'question',
                                                showCancelButton: true,
                                                confirmButtonColor: '#d33',
                                                cancelButtonColor: '#3085d6',
                                                confirmButtonText: 'Yes, delete the user!',
                                                allowOutsideClick: false,
                                            }).then((result) => {

                                                if (result.isConfirmed) {
                                                    
                                                    let form = new FormData();

                                                    form.append("modelData", 'User');
                                                    form.append("modelId", deleteButton.data('username'));
                                                    form.append("reset_" + type + "_" + keyword, true);
                                                    form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                                    form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                                    
                                                    _All_Users.xhr = $.ajax({
                                                        url: '/datatable/delete',
                                                        type: 'POST',
                                                        contentType: false,
                                                        processData: false,
                                                        data: form,
                                                        beforeSend() {
                                                            if (_All_Users.xhr !== false) {
                                                                _All_Users.xhr.abort();
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
                                                        _Base.displayMessage("error", "Unable to process the user deletion. Please reload the page. If the error persists contact support.");
                                                    });
                                                }
                                            })

                                        } else {

                                            table.ajax.reload();
                                        }
                                    }
                                }).fail(response => {
                                    _Base.displayMessage("error", "Unable to process the user deletion. Please reload the page. If the error persists contact support.");
                                });
                            }
                        })
                    });
                });
            });
        }
    };
}))(jQuery);
