let _Edit_Event;

jQuery(document).ready(() => {
    _Edit_Event.init();
});

(($ => {
    
    _Edit_Event = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.event-card');

            let previousValues = {};

            $('.edit-modal').each(function () {

                $(this).on('hide.bs.modal', function (e) {

                    const pressedElement = document.activeElement;
                    
                    if ($(pressedElement).attr('id') !== 'update-event-button') {
                        
                        const modal = $(this);

                        modal.find("#edit-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                            $(this).val('');
                        });

                        for (let key in previousValues) {

                            let field = modal.find('#' + key.replace(/_/g, "-"));
                            
                            if (key === 'starts' || key === 'ends') {

                                field.val(previousValues[key]);

                            } else if (key === 'venue' || key === 'artists' || key === 'currency_id') {

                                field.html(previousValues[key]);

                                field.selectpicker('destroy');

                                field.selectpicker();

                            } else {

                                if (field.hasClass('is-invalid')) {

                                    field.attr('placeholder', previousValues[key]);

                                    field.attr('title', '');

                                    field.removeClass('is-invalid');
                                }
                            }
                        }

                        modal.find('#event-picture').val('');
                    }
                });

                $(this).on('show.bs.modal', function (e) {

                    const modal = $(this);

                    modal.find('#venue').selectpicker();

                    modal.find('#artists').selectpicker();

                    modal.find('#currency-id').selectpicker();

                    let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                    let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
                    
                    modal.find('#starts').attr('min', localISOTime);

                    modal.find('#ends').attr('min', localISOTime);

                    modal.find('#remove-event-picture-' + modal.find('#current-id').val()).click(function() {

                        if ($(this).is(':checked')) {

                            modal.find('#event-picture').val('');

                        }
                    });
                    
                    modal.find("#event-picture").on('change', function() {

                        modal.find("#remove-event-picture-" + modal.find('#current-id').val()).prop("checked", false);
                    });

                    $("#edit-event-form input[type=datetime-local]").each(function() {
                
                        if (!$(this).hasClass('is-invalid')) {

                            let key = $(this).attr('name');

                            let value = $(this).val();

                            previousValues[key] = value;
                        }

                        $(this).removeClass('is-invalid');
                    });

                    const updateEventButton = modal.find('#update-event-button');
                    
                    modal.find("#edit-event-form select").each(function() {

                        if (!$(this).hasClass('is-invalid')) {

                            let key = $(this).attr('name');

                            let value = $(this).html();

                            previousValues[key] = value;
                        }
                    });
                    
                    if (!modal.find("#starts").hasClass('is-invalid')) {

                        let key = modal.find("#starts").attr('name');

                        let value = modal.find("#starts").val();

                        previousValues[key] = value;
                    }

                    if (!modal.find("#ends").hasClass('is-invalid')) {

                        let key = modal.find("#ends").attr('name');

                        let value = modal.find("#ends").val();

                        previousValues[key] = value;
                    }

                    updateEventButton.off('click').on('click', function (e) {

                        modal.find('#starts').removeAttr('min');

                        modal.find('#ends').removeAttr('min');

                        let isFormValid = modal.find('#edit-event-form')[0].checkValidity();

                        if (!isFormValid) {

                            modal.find('#edit-event-form')[0].reportValidity();

                            return false;

                        } else {

                            e.preventDefault();
                        }

                        let file = modal.find('#event-picture').prop("files")[0];

                        let form = new FormData();

                        form.append("name", modal.find('#name').val());
                        form.append("description", modal.find('#description').val());
                        form.append("location", modal.find('#location').val());
                        form.append("starts", modal.find('#starts').val());
                        form.append("ends", modal.find('#ends').val());
                        form.append("venue", modal.find('#venue').val());
                        form.append("artists", modal.find('#artists').val());
                        form.append("currency_id", modal.find('#currency-id').val());
                        form.append("ticket_price", modal.find('#ticket-price').val());
                        form.append("remove_event_picture", modal.find('#remove-event-picture-' + modal.find('#current-id').val()).is(':checked') ? modal.find('#remove-event-picture-' + modal.find('#current-id').val()).val() : '');
                        form.append("event_picture", file);
                        form.append("type", $('#type').val());
                        form.append("csrf_name", modal.find('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", modal.find('#ajax_csrf_value').data('value'));

                        _Edit_Event.xhr = $.ajax({
                            url: '/host/events/' + modal.find('#current-id').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_Edit_Event.xhr !== false) {
                                    _Edit_Event.xhr.abort();
                                }
                            }
                        }).done(response => {

                            if (response !== false && response !== undefined && response !== '') {

                                let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                                let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
                                
                                modal.find('#starts').attr('min', localISOTime);

                                modal.find('#ends').attr('min', localISOTime);

                                modal.find("#edit-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                                    
                                    if (!$(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        let value = $(this).attr('placeholder');

                                        previousValues[key] = value;
                                    }
                                });
                                
                                modal.find("#edit-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

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

                                        modal.find('#event-picture').val('');
                                    }

                                    _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                                }

                                for (let key in previousValues) {

                                    if (!modal.find('#' + key.replace("_", "-")).hasClass('is-invalid')) {

                                        if (key !== 'venue' && key !== 'artists' && key !== 'currency_id') {

                                            modal.find('#' + key.replace("_", "-")).attr('placeholder', previousValues[key]);
                                        }
                                    }
                                }
                                
                                if (response.fragments.errors) {

                                    $.each(response.fragments.errors, function (key, value) {

                                        let field = modal.find('#' + key.replace(/_/g, "-"));

                                        let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                        
                                        if (key === 'starts') {

                                            _Base.displayMessage("error", error);

                                        } else if (key === 'ends') {

                                            // Needs to be separate than 'starts'

                                        } else if (key === 'venue' || key === 'artists') {

                                            _Base.displayMessage("error", error);

                                        } else if (key === 'currency_id') {

                                            _Base.displayMessage("error", error.replace(' id', ''));
        
                                            field.val('XXX').change();

                                        } else {

                                            previousValues[key] = field.attr('placeholder');

                                            field.val('');

                                            field.attr('placeholder', error);

                                            field.attr('title', error);
                                        }

                                        field.addClass('is-invalid');

                                        modal.find("#edit-event-form select").each(function() {
                                    
                                            if ($(this).hasClass('is-invalid')) {

                                                let key = $(this).attr('name');

                                                $(this).html(previousValues[key]);

                                                if (key === 'venue' || key === 'artists' || key === 'currency_id') {
                                                    
                                                    modal.find('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                                    modal.find('#' + key.replace(/_/g, "-")).selectpicker();
                                                }
                                            }
                
                                            $(this).removeClass('is-invalid');

                                            if (key === 'venue' || key === 'artists' || key === 'currency_id') {

                                                $(this).parent().removeClass('is-invalid');
                                            }
                                        });
                                        
                                        if (modal.find("#starts").hasClass('is-invalid')) {

                                            let key = modal.find("#starts").attr('name');

                                            modal.find("#starts").val(previousValues[key]);
                                        }

                                        if (modal.find("#ends").hasClass('is-invalid')) {

                                            let key = modal.find("#ends").attr('name');

                                            modal.find("#ends").val(previousValues[key]);
                                        }

                                        $("#edit-event-form input[type=datetime-local]").each(function() {

                                            if ($(this).hasClass('is-invalid')) {

                                                let key = $(this).attr('name');

                                                $(this).val(previousValues[key]);
                                            }
                                        });
                                    });
                                }
                                
                                if (response.fragments.updated_fields) {

                                    modal.modal('hide');

                                    modal.find("#edit-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                        if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val('') !== '') {

                                            $(this).val('');
                                        }
                                    });

                                    $("#edit-event-form input[type=datetime-local]").each(function() {

                                        if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val() !== '') {

                                            if ($(this).hasClass('is-invalid')) {

                                                $(this).removeClass('is-invalid');
                                            }
                                        }
                                    });
                                    
                                    $.each(response.fragments.updated_fields, function (key, value) {
                                        
                                        if (key === 'event_picture') {

                                            let currentTime = new Date().getTime();

                                            modal.find('#view-event-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/event-pictures/0.jpg');
                                            
                                            let removeEventPictureCheckbox = modal.find('#remove-event-picture-' + modal.find('#current-id').val());

                                            if (value) {

                                                removeEventPictureCheckbox.prop("disabled", false);

                                                modal.find('#venue-picture').val('');

                                            } else {

                                                removeEventPictureCheckbox.prop("checked", false);

                                                removeEventPictureCheckbox.prop("disabled", true);
                                            }

                                        } else if (key === 'starts' || key === 'ends') {
                                            
                                            if (!modal.find("#starts").hasClass('is-invalid')) {

                                                let key = modal.find("#starts").attr('name');

                                                let value = modal.find("#starts").val();

                                                previousValues[key] = value;
                                            }

                                            if (!modal.find("#ends").hasClass('is-invalid')) {

                                                let key = modal.find("#ends").attr('name');

                                                let value = modal.find("#ends").val();

                                                previousValues[key] = value;
                                            }

                                            $("#edit-event-form input[type=datetime-local]").each(function() {
                            
                                                if (!$(this).hasClass('is-invalid')) {

                                                    let key = $(this).attr('name');

                                                    previousValues[key] = value;
                                                }
                                            });
                                            
                                            let field = modal.find('#' + key.replace("_", "-"));

                                            field.val(value);

                                        } else if (key === 'artists') {
                                            
                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                            let participants = card.find('[id*="participant_"]');

                                            $.each(participants, function () {

                                                $(this).remove();
                                            });

                                            let currentParticipants = [];

                                            $.each(value, function (k, v) {

                                                currentParticipants[v.id] = v;
                                            });

                                            currentParticipants = currentParticipants.filter(function (element) {
                                                return element !== undefined;
                                            });

                                            if (currentParticipants.length > 0) {

                                                let selectElement = card.find('#' + key);

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

                                                    let fullName = v.first_name + ' ' + v.last_name;

                                                    let profilePicture = v.profile_picture ? v.profile_picture : '/uploads/profile-pictures/0.jpg';

                                                    let artistStatus = '';

                                                    if (v.pivot.artist_approved == null) {

                                                        artistStatus = '<p>Artist pending</p>';
                                                        
                                                    } else if (v.pivot.artist_approved == false) {

                                                        artistStatus = '<p>Artist rejected</p>';
                                                    }

                                                    let participantCard = $('<div class="card small-artist-card me-3 mb-3" id="participant_"' + v.id + '>' + 
                                                                                '<img src="' + profilePicture + '" class="card-img-top mt-2 artist-card-picture image" alt="' + fullName + ' Event Participant Picture">' + 
                                                                                '<div class="card-body d-flex flex-column text-center">' + 
                                                                                    '<h5>' + fullName + '</h5>' + 
                                                                                    '<a href="/artist-details/' + v.username + '" class="btn btn-primary">View Details</a>' + 
                                                                                    artistStatus + 
                                                                                '</div>' + 
                                                                            '</div>'
                                                    );

                                                    card.find('.no-participants-text').remove();

                                                    card.find('.participants-container').append(participantCard);

                                                    let selectElement = card.find('#' + key);

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

                                                let noParticipantsText = $('<div class="row mt-sm-4 no-participants-text">' + 
                                                                                '<div class="text-center"><h5>There are no participants for this event yet.</h5></div>' + 
                                                                            '</div>'
                                                );

                                                noParticipantsText.insertBefore(card.find('.participants-container'));

                                                let selectElement = card.find('#' + key);

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

                                            let selectElement = card.find('#' + key);

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

                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                            let selectElement = card.find('#' + key.replace('_', '-'));
                                            
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
                                        
                                        } else if (key === 'venue_id' && response.fragments.updated_fields['type'] == 'active') {
                                            
                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                            card.remove();

                                            if ($('.card').length == 0) {

                                                $('.row').prepend('<p class="text-center">There are no active events</p>');
                                            }

                                        } else if (key === 'venue_id' && response.fragments.updated_fields['type'] == 'inactive') {

                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                            // the elements below can be empty as they get populated thanks to their classes after updating the event

                                            $('<p class="card-text">Venue: <span class="venue"></span></p>').insertBefore(card.find('.main-card-body .error-message'));

                                            let newElement = '<p class="card-text mt-0">' +
                                                                'Tel: <a href="" class="phone"><span class="phone_code"></span> <span class="phone_number"></span></a>' +
                                                                '<br>' +
                                                                '<span class="phone_code_country"></span>, <span class="phone_code_continent"></span>' +
                                                            '</p>';
                                            
                                            $(newElement).insertBefore(card.find('.sub-card-body .error-message'));

                                            card.find('.error-message').html('Event not yet approved by venue owner');

                                            if ($('.card').length == 0) {

                                                $('.row').prepend('<p class="text-center">There are no inactive events</p>');
                                            }

                                        } else {

                                            let field = modal.find('#' + key.replace("_", "-"));

                                            previousValues[key] = value;

                                            field.val('');

                                            field.attr('placeholder', key === 'ticket_price' ? parseFloat(value).toFixed(2) : value);

                                            field.attr('title', '');

                                            let copyButton = field.next().children().eq(0);

                                            copyButton.attr('disabled', false);
                                        }
                                        
                                        modal.parent().find('.' + key).each(function () {

                                            $(this).html(value);
                                        });

                                        if (key === 'phone_code' || key === 'phone_number') {

                                            modal.parent().find('.phone').each(function () {

                                                const phone_code = response.fragments.updated_fields['phone_code'];

                                                const phone_number = response.fragments.updated_fields['phone_number'];

                                                $(this).attr('href', 'tel:' + phone_code + ' ' + phone_number);
                                            });
                                        }

                                        if (key === 'location') {
                                            
                                            modal.parent().find('.map').each(function () {

                                                $(this).parent().parent().prev().children().eq(0).attr('title', value);

                                                $(this).attr('src', 'https://maps.google.com/maps?q=' + encodeURIComponent(value) + '&ie=UTF8&iwloc=&output=embed');
                                            });
                                        }

                                        if (key === 'event_picture') {

                                            modal.parent().find('.image').each(function () {

                                                let currentTime = new Date().getTime();

                                                $(this).attr("src", value ? (value + '?' + currentTime) : '/uploads/event-pictures/0.jpg');
                                            });
                                        }

                                        if (key === 'venue') {
                                            
                                            modal.parent().find('.' + key).html(response.fragments.updated_fields['venue_url']);
                                        }

                                        if (key === 'currency_id' || key === 'ticket_price') {

                                            let ticketPrices = modal.parent().find('.ticket-price');

                                            $.each(ticketPrices, function () {

                                                if (!response.fragments.updated_fields['ticket_price_extra']) {

                                                    let price = parseFloat(response.fragments.updated_fields['ticket_price']).toFixed(2);

                                                    $(this).val(price + ' ' + response.fragments.updated_fields['currency']);

                                                    $(this).parent().removeClass('price-container-extra-currency').addClass('price-container');

                                                    $(this).removeClass('price-currency-extra-currency').addClass('price-currency');

                                                } else {

                                                    $(this).val(response.fragments.updated_fields['ticket_price_extra']);

                                                    $(this).parent().removeClass('price-container').addClass('price-container-extra-currency');

                                                    $(this).removeClass('price-currency').addClass('price-currency-extra-currency');
                                                }
                                            });
                                        }
                                    });

                                } else {

                                    if (!response.fragments.errors && response.fragments.notify.field !== 'event_picture') {

                                        modal.modal('hide');

                                        modal.find("#edit-event-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                            $(this).val('');
                                        });

                                        $("#edit-event-form input[type=datetime-local]").each(function() {

                                            if ($(this).hasClass('is-invalid')) {
                                                
                                                $(this).removeClass('is-invalid');
                                            }
                                        });
                                    }
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the event editing process. Please reload the page. If the error persists contact support.");
                        });
                    });

                    const deleteEventButton = modal.find('#delete-event-button');

                    deleteEventButton.off('click').on('click', function (e) {

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
                                
                                form.append("delete_event_button", modal.find('#delete-event-button') ? true : false);
                                form.append("current_id", modal.find('#current-id').val());
                                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                
                                _Edit_Event.xhr = $.ajax({
                                    url: '/host/events/' + modal.find('#current-id').val(),
                                    type: 'POST',
                                    contentType: false,
                                    processData: false,
                                    data: form,
                                    beforeSend() {
                                        if (_Edit_Event.xhr !== false) {
                                            _Edit_Event.xhr.abort();
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

                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                            card.remove();
                                            
                                            modal.modal('hide');
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
                                
                                                    form.append("delete_event_button", modal.find('#delete-event-button') ? true : false);
                                                    form.append("delete_event_participants", true);
                                                    form.append("current_id", modal.find('#current-id').val());
                                                    form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                                    form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                                    
                                                    _Edit_Event.xhr = $.ajax({
                                                        url: '/host/events/' + $('#current-id').val(),
                                                        type: 'POST',
                                                        contentType: false,
                                                        processData: false,
                                                        data: form,
                                                        beforeSend() {
                                                            if (_Edit_Event.xhr !== false) {
                                                                _Edit_Event.xhr.abort();
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

                                                            let card = $('#card-' + response.fragments.updated_fields['event_id']);

                                                            card.remove();
                                                            
                                                            modal.modal('hide');
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
                });
            });

            _Base.handleEventStatisticsForMultipleCards(previousValues);
        },
    };
}))(jQuery);
