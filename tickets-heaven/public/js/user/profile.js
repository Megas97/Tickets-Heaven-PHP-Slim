let _Profile;

jQuery(document).ready(() => {
    _Profile.init();
});

(($ => {
    
    _Profile = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $('#phone-code-id').selectpicker();

            $('#default-currency-id').selectpicker();

            $('#phone-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));
            });

            $('#credit-card-number').on('input', function (e) {

                $(this).val($(this).val().replace(/[^0-9]/, ''));

                $("#remove-credit-card-number").prop("checked", false);
            });

            $('#remove-phone-number').click(function() {

                if ($(this).is(':checked')) {

                    $('#phone-number').val('');
                }
            });
            
            $("#phone-code-id").on('change', function() {

                $("#remove-phone-number").prop("checked", false);
            });

            $("#phone-number").on('input', function() {

                $("#remove-phone-number").prop("checked", false);
            });

            $('#remove-credit-card-number').click(function() {

                if ($(this).is(':checked')) {

                    $('#credit-card-number').val('');
                }
            });

            $('#remove-profile-picture').click(function() {

                if ($(this).is(':checked')) {

                    $('#profile-picture').val('');
                }
            });
            
            $("#profile-picture").on('change', function() {

                $("#remove-profile-picture").prop("checked", false);
            });

            const updateProfileButton = $('#update-profile-button');

            let previousValues = {};

            $("#profile-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            updateProfileButton.on('click', function (e) {

                let isFormValid = $('#profile-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#profile-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let file = $('#profile-picture').prop("files")[0];

                let form = new FormData();

                form.append("username", $('#username').val());
                form.append("email", $('#email').val());
                form.append("first_name", $('#first-name').val());
                form.append("last_name", $('#last-name').val());
                form.append("phone_code_id", $('#phone-code-id').val());
                form.append("phone_number", $('#phone-number').val());
                form.append("credit_card_number", $('#credit-card-number').val());
                form.append("default_currency_id", $('#default-currency-id').val());
                form.append("address", $('#address').val());
                form.append("description", $('#description').val());
                form.append("remove_phone_number", $('#remove-phone-number').is(':checked') ? $('#remove-phone-number').val() : '');
                form.append("remove_credit_card_number", $('#remove-credit-card-number').is(':checked') ? $('#remove-credit-card-number').val() : '');
                form.append("remove_profile_picture", $('#remove-profile-picture').is(':checked') ? $('#remove-profile-picture').val() : '');
                form.append("profile_picture", file);
                form.append("update_profile_button", $('#update-profile-button') ? true : false);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _Profile.xhr = $.ajax({
                    url: '/profile',
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_Profile.xhr !== false) {
                            _Profile.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        $("#profile-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#profile-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

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

                                $('#profile-picture').val('');
                            }
                            
                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
                                
                                if (key === 'phone_code_id') {

                                    _Base.displayMessage("error", error.replace(" id", ""));

                                    field.val('+XXX').change();

                                } else if (key === 'default_currency_id') {

                                    _Base.displayMessage("error", error.replace(" id", ""));

                                    field.val('Default currency').change();

                                } else if (key === 'phone') {

                                    _Base.displayMessage("error", error);

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#profile-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'phone_code_id' || key === 'default_currency_id') {

                                            $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                            $('#' + key.replace(/_/g, "-")).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'phone_code_id' || key === 'default_currency_id') {

                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });
                            });
                        }

                        if (response.fragments.updated_fields) {

                            $("#profile-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val('') !== '') {

                                    $(this).val('');
                                }
                            });
                            
                            $.each(response.fragments.updated_fields, function (key, value) {

                                if (key === 'profile_picture') {

                                    let currentTime = new Date().getTime();

                                    $('#view-profile-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/profile-pictures/0.jpg');

                                    $('#navbar-profile-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/profile-pictures/0.jpg');
                                    
                                    let removeProfilePictureCheckbox = $('#remove-profile-picture');

                                    if (value) {

                                        removeProfilePictureCheckbox.prop("disabled", false);

                                        $('#profile-picture').val('');

                                    } else {

                                        removeProfilePictureCheckbox.prop("checked", false);

                                        removeProfilePictureCheckbox.prop("disabled", true);
                                    }

                                } else if (key === 'phone_number') {

                                    let removePhoneNumberCheckbox = $('#remove-phone-number');
                                    
                                    if (value) {

                                        removePhoneNumberCheckbox.prop("disabled", false);

                                        $('#phone-number').val('');

                                        $('#phone-number').attr('placeholder', value);

                                    } else {

                                        removePhoneNumberCheckbox.prop("checked", false);

                                        removePhoneNumberCheckbox.prop("disabled", true);

                                        $('#phone-code-id').val('+XXX').change();

                                        $('#phone-code-id').selectpicker('destroy');

                                        $('#phone-code-id').selectpicker();

                                        $('#phone-number').attr('placeholder', 'Phone number');
                                    }

                                    $('#phone-number').attr('title', '');

                                } else if (key === 'credit_card_number') {

                                    let removeCreditCardCheckbox = $('#remove-credit-card-number');
                                    
                                    if (value) {

                                        removeCreditCardCheckbox.prop("disabled", false);

                                        $('#credit-card-number').val('');

                                        $('#credit-card-number').attr('placeholder', value);

                                    } else {

                                        removeCreditCardCheckbox.prop("checked", false);

                                        removeCreditCardCheckbox.prop("disabled", true);

                                        $('#credit-card-number').attr('placeholder', 'Credit card number');
                                    }

                                    $('#credit-card-number').attr('title', '');

                                } else if (key === 'phone_code_id' || key === 'default_currency_id') {

                                    $("#profile-form select").each(function() {

                                        if (!$(this).hasClass('is-invalid')) {

                                            let key = $(this).attr('name');

                                            $(this).html(previousValues[key]);
                                        }
                                    });
                                    
                                    let field = $('#' + key.replace(/_/g, "-"));

                                    field.val(value).change();

                                    $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                    $('#' + key.replace(/_/g, "-")).selectpicker();

                                } else if ((key === 'address' || key === 'description') && !value) {
                                    
                                    let field = $('#' + key.replace(/_/g, "-"));

                                    field.val('');

                                    field.attr('placeholder', _Base.capitalizeFirstLetter(key));

                                    field.attr('title', '');

                                    let copyButton = field.next().children().eq(0);

                                    copyButton.attr('disabled', true);
                                    
                                } else {

                                    let field = $('#' + key.replace(/_/g, "-"));
                                    
                                    field.val('');

                                    field.attr('placeholder', value);

                                    field.attr('title', '');

                                    let copyButton = field.next().children().eq(0);

                                    copyButton.attr('disabled', false);
                                }
                            });

                        } else {

                            if (!response.fragments.errors) {

                                $("#profile-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                                    $(this).val('');
                                });
                            }
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the profile update. Please reload the page. If the error persists contact support.");
                });
            });

            const deleteProfileButton = $('#delete-profile-button');

            deleteProfileButton.on('click', function (e) {

                e.preventDefault();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "Once deleted your account will be gone forever!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete my account!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("delete_profile_button", $('#delete-profile-button') ? true : false);
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _Profile.xhr = $.ajax({
                            url: '/profile',
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_Profile.xhr !== false) {
                                    _Profile.xhr.abort();
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
                            _Base.displayMessage("error", "Unable to process the profile deletion. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });
        }
    };
}))(jQuery);
