let _Host_Add_Event;

jQuery(document).ready(() => {
    _Host_Add_Event.init();
});

(($ => {

    _Host_Add_Event = {

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
            
            $('#remove-event-picture').click(function() {

                if ($(this).is(':checked')) {

                    $("#remove-event-picture").prop("disabled", true);

                    $('#event-picture').val('');
                }
            });
            
            $("#event-picture").on('change', function() {

                $("#remove-event-picture").prop("disabled", false);

                $("#remove-event-picture").prop("checked", false);
            });

            const addEventButton = $('#add-event-button');

            let previousValues = {};

            $("#add-event-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            addEventButton.on('click', function (e) {

                let isFormValid = $('#add-event-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#add-event-form')[0].reportValidity();

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
                form.append("event_picture", file);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _Host_Add_Event.xhr = $.ajax({
                    url: '/host/events/add',
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_Host_Add_Event.xhr !== false) {
                            _Host_Add_Event.xhr.abort();
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

                        $("#add-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#add-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                            if ($(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                $(this).attr('placeholder', previousValues[key]);
                            }

                            $(this).removeClass('is-invalid');
                        });

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

                                $("#add-event-form select").each(function() {
                            
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
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the event adding process. Please reload the page. If the error persists contact support.");
                });
            });
        },
    };
}))(jQuery);
