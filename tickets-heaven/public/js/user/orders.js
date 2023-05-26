let _Orders;

jQuery(document).ready(() => {
    _Orders.init();
});

(($ => {
    
    _Orders = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();

            let table = $('#ordersTable').DataTable({

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
                    { visible: false, targets: [0, 7] }, // id, date
                    { orderable: false, targets: [1, 7, 8] }, // image, hidden date, actions
                    { searchable: false, targets: [6, 8] }, // date, actions
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
                    searchPlaceholder: 'Search Orders'
                },
                ajax: {
                    'url': '/datatable/read',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'modelData': 'Order',
                        'current_username': $('#current-username').val(),
                        'is_in_admin_panel': window.location.toString().includes('admin') ? 1 : 0,
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    }
                },
                columns: [
                    { data: 'order.id', className: 'text-center align-middle' },
                    { data: 'order.event_image', className: 'text-center align-middle' },
                    { data: 'order.event_name', className: 'text-center align-middle' },
                    { data: 'order.ticket_quantity', className: 'text-center align-middle' },
                    { data: 'order.ticket_single_price', className: 'text-center align-middle' },
                    { data: 'order.ticket_total_price', className: 'text-center align-middle' },
                    { data: 'order.datetime', className: 'text-center align-middle' },
                    { data: 'order.date', className: 'text-center align-middle' },
                    { data: 'order.actions', className: 'text-center align-middle' },
                ],
            });
        }
    };
}))(jQuery);
