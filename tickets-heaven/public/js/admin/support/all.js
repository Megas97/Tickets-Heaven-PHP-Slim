let _All_SupportTickets;

jQuery(document).ready(() => {
    _All_SupportTickets.init();
});

(($ => {
    
    _All_SupportTickets = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            let table = $('#supportTicketsListTable').DataTable({

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
                    { visible: false, targets: [0, 3] }, // id, full name
                    { orderable: false, targets: [6] }, // actions
                    { searchable: false, targets: [6] }, // actions
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
                    searchPlaceholder: 'Search Events'
                },
                ajax: {
                    'url': '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'SupportTicket',
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'supportTicket.id', className: 'text-center align-middle' },
                    { data: 'supportTicket.first_name', className: 'text-center align-middle' },
                    { data: 'supportTicket.last_name', className: 'text-center align-middle' },
                    { data: 'supportTicket.full_name', className: 'text-center align-middle' },
                    { data: 'supportTicket.email', className: 'text-center align-middle' },
                    { data: 'supportTicket.subject', className: 'text-center align-middle' },
                    { data: 'supportTicket.submitted', className: 'text-center align-middle' },
                    { data: 'supportTicket.actions', className: 'text-center align-middle' },
                ],
            });

            table.on('draw responsive-display', function() {

                let deleteButtons = $('.datatable-delete-button');
                
                $.each(deleteButtons, function() {
                    
                    let deleteButton = $(this);

                    deleteButton.on('click', function (e) {
                        
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "Once deleted the support ticket will be gone forever!",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Yes, delete the support ticket!',
                            allowOutsideClick: false,
                        }).then((result) => {

                            if (result.isConfirmed) {
                                
                                _All_SupportTickets.xhr = $.ajax({
                                    url: '/datatable/delete',
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        'modelData': 'SupportTicket',
                                        'modelId': deleteButton.data('id'),
                                        'csrf_name': $('#ajax_csrf_name').data('value'),
                                        'csrf_value': $('#ajax_csrf_value').data('value')
                                    },
                                    beforeSend() {
                                        if (_All_SupportTickets.xhr !== false) {
                                            _All_SupportTickets.xhr.abort();
                                        }
                                    }
                                }).done(response => {

                                    if (response !== false && response !== undefined && response !== '') {

                                        if (response.fragments.notify) {
                
                                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                        }

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
                                        
                                        table.ajax.reload();
                                    }
                                }).fail(response => {
                                    _Base.displayMessage("error", "Unable to process the support ticket deletion. Please reload the page. If the error persists contact support.");
                                });
                            }
                        })
                    });
                });
            });
        }
    };
}))(jQuery);
