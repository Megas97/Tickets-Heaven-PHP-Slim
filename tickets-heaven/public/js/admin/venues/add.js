let _Add_Venue;

jQuery(document).ready(() => {
    _Add_Venue.init();
});

(($ => {
    
    _Add_Venue = {

        xhr: false,

        init() {

            $('#phone-code-id').selectpicker();

            $('#owner').selectpicker();

            $('#phone-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));
            });

            const addVenueButton = $('#add-venue-button');

            let previousValues = {};

            $("#add-venue-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            addVenueButton.on('click', function (e) {

                let isFormValid = $('#add-venue-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#add-venue-form')[0].reportValidity();

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
                form.append("venue_picture", file);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _Add_Venue.xhr = $.ajax({
                    url: '/admin/venues/add',
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_Add_Venue.xhr !== false) {
                            _Add_Venue.xhr.abort();
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

                        $("#add-venue-form input[type=text],[type=password],[type=email],[type=time], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#add-venue-form input[type=text],[type=password],[type=email],[type=time], textarea[type=text]").each(function() {

                            if ($(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                $(this).attr('placeholder', previousValues[key]);
                            }

                            $(this).removeClass('is-invalid');
                        });
                        
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

                                $("#add-venue-form select").each(function() {
                            
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
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the venue adding process. Please reload the page. If the error persists contact support.");
                });
            });
        },
    };
}))(jQuery);
