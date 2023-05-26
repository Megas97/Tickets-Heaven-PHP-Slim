let _View_User;

jQuery(document).ready(() => {
    _View_User.init();
});

(($ => {
    
    _View_User = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();

            $('#phone-code-id').selectpicker();

            $('#default-currency-id').selectpicker();

            $('#role').selectpicker();

            $('#remove-phone-number').click(function() {

                if ($(this).is(':checked')) {

                    $('#phone-number').val('');
                }
            });
            
            $('#phone-code-id').on('change', function() {

                $("#remove-phone-number").prop("checked", false);
            });

            $('#phone-number').on('input', function() {

                $(this).val($(this).val().replace(/[^0-9]/, ''));

                $("#remove-phone-number").prop("checked", false);
            });
            
            $('#remove-profile-picture').click(function() {

                if ($(this).is(':checked')) {

                    $('#profile-picture').val('');
                }
            });
            
            $('#profile-picture').on('change', function() {

                $("#remove-profile-picture").prop("checked", false);
            });

            const updateProfileButton = $('#update-profile-button');

            let previousValues = {};

            $("#view-user-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            updateProfileButton.on('click', function (e) {

                let isFormValid = $('#view-user-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#view-user-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let file = $('#profile-picture').prop("files")[0];

                let form = new FormData();

                form.append("username", $('#username').val());
                form.append("email", $('#email').val());
                form.append("password", $('#password').val());
                form.append("first_name", $('#first-name').val());
                form.append("last_name", $('#last-name').val());
                form.append("phone_code_id", $('#phone-code-id').val());
                form.append("phone_number", $('#phone-number').val());
                form.append("default_currency_id", $('#default-currency-id').val());
                form.append("address", $('#address').val());
                form.append("description", $('#description').val());
                form.append("role", $('#role').val());
                form.append("remove_phone_number", $('#remove-phone-number').is(':checked') ? $('#remove-phone-number').val() : '');
                form.append("remove_credit_card_number", $('#remove-credit-card-number').is(':checked') ? $('#remove-credit-card-number').val() : '');
                form.append("remove_profile_picture", $('#remove-profile-picture').is(':checked') ? $('#remove-profile-picture').val() : '');
                form.append("profile_picture", file);
                form.append("update_profile_button", $('#update-profile-button') ? true : false);
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _View_User.xhr = $.ajax({
                    url: '/admin/users/' + $('#current-username').val(),
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_View_User.xhr !== false) {
                            _View_User.xhr.abort();
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

                        let type = '';

                        let message = '';

                        if (response.fragments.owner_has_venues) {

                            type = 'owner';

                            message = 'Changing this user\'s role will leave all their venues without a set owner!';

                        } else if (response.fragments.host_has_events) {

                            type = 'host';

                            message = 'Changing this user\'s role will leave all their events without a set host!';

                        } else if (response.fragments.artist_has_events) {

                            type = 'artist';

                            message = 'Changing this user\'s role will remove them from all events where they are participating!';
                        }

                        if (type != '' && message != '') {

                            Swal.fire({
                                title: 'Are you sure?',
                                text: message,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#d33',
                                cancelButtonColor: '#3085d6',
                                confirmButtonText: 'Yes, change user role!',
                                allowOutsideClick: false,
                            }).then((result) => {

                                if (result.isConfirmed) {

                                    let form = new FormData();
                                    
                                    form.append("change_" + type + "_role", true);
                                    form.append("username", response.fragments.updated_fields['username'] ?? '');
                                    form.append("email", response.fragments.updated_fields['email'] ?? '');
                                    form.append("password", response.fragments.updated_fields['password'] ?? '');
                                    form.append("first_name", response.fragments.updated_fields['first_name'] ?? '');
                                    form.append("last_name", response.fragments.updated_fields['last_name'] ?? '');
                                    form.append("phone_code_id", response.fragments.updated_fields['phone_code_id'] ?? '');
                                    form.append("phone_number", response.fragments.updated_fields['phone_number'] ?? '');
                                    form.append("default_currency_id", response.fragments.updated_fields['default_currency_id'] ?? '');
                                    form.append("address", response.fragments.updated_fields['address'] ?? '');
                                    form.append("description", response.fragments.updated_fields['description'] ?? '');
                                    form.append("role", response.fragments.updated_fields['role'] ?? '');
                                    form.append("remove_credit_card_number", response.fragments.updated_fields['remove_credit_card_number']);
                                    form.append("remove_profile_picture", response.fragments.updated_fields['remove_profile_picture']);
                                    form.append("profile_picture", file);
                                    form.append("update_profile_button", response.fragments.updated_fields['update_profile_button']);
                                    form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                    form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                    
                                    _View_User.xhr = $.ajax({
                                        url: '/admin/users/' + $('#current-username').val(),
                                        type: 'POST',
                                        contentType: false,
                                        processData: false,
                                        data: form,
                                        beforeSend() {
                                            if (_View_User.xhr !== false) {
                                                _View_User.xhr.abort();
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
                                            
                                            _View_User.handleUserUpdate(previousValues, response);
                                        }
                                    }).fail(response => {
                                        _Base.displayMessage("error", "Unable to process the role change. Please reload the page. If the error persists contact support.");
                                    });

                                } else {

                                    $.each(response.fragments.updated_fields, function (key, value) {
                                        
                                        $('#' + key.replace(/_/g, "-")).val(value ?? '');

                                        if (key === 'phone_code_id' || key === 'default_currency_id' || key === 'role') {

                                            $('#' + key.replace(/_/g, "-")).selectpicker('destroy');
            
                                            $('#' + key.replace(/_/g, "-")).selectpicker();
                                        }
                                    });
                                }
                            })

                        } else {

                            _View_User.handleUserUpdate(previousValues, response);
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
                    text: "Once deleted the account will be gone forever!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete the account!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("delete_profile_button", $('#delete-profile-button') ? true : false);
                        form.append("current_username", $('#current-username').val());
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_User.xhr = $.ajax({
                            url: '/admin/users/' + $('#current-username').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_User.xhr !== false) {
                                    _View_User.xhr.abort();
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
                                        confirmButtonText: 'Yes, delete the account!',
                                        allowOutsideClick: false,
                                    }).then((result) => {

                                        if (result.isConfirmed) {

                                            let form = new FormData();
                        
                                            form.append("delete_profile_button", $('#delete-profile-button') ? true : false);
                                            form.append("reset_" + type + "_" + keyword, true);
                                            form.append("current_username", $('#current-username').val());
                                            form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                                            form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                                            
                                            _View_User.xhr = $.ajax({
                                                url: '/admin/users/' + $('#current-username').val(),
                                                type: 'POST',
                                                contentType: false,
                                                processData: false,
                                                data: form,
                                                beforeSend() {
                                                    if (_View_User.xhr !== false) {
                                                        _View_User.xhr.abort();
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
                                                _Base.displayMessage("error", "Unable to process the user deletion. Please reload the page. If the error persists contact support.");
                                            });
                                        }
                                    })
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the profile deletion. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });

            const activateProfileButton = $('#activate-profile-button');

            activateProfileButton.on('click', function (e) {

                e.preventDefault();

                let activateButtonText = $('#activate-button-text').text();

                let action = activateButtonText == 'Activate' ? 'Activating' : 'Deactivating';

                let text = activateButtonText == 'Activate' ? 'allow them to use ' : 'prevent them from using';

                Swal.fire({
                    title: 'Are you sure?',
                    text: action + " this user\'s account will " + text + " all public functions of the website!",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, ' + activateButtonText.toLowerCase() + ' the account!',
                    allowOutsideClick: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        let form = new FormData();
                        
                        form.append("activate_profile_button", $('#activate-profile-button') ? true : false);
                        form.append("current_username", $('#current-username').val());
                        form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                        form.append("csrf_value", $('#ajax_csrf_value').data('value'));
                        
                        _View_User.xhr = $.ajax({
                            url: '/admin/users/' + $('#current-username').val(),
                            type: 'POST',
                            contentType: false,
                            processData: false,
                            data: form,
                            beforeSend() {
                                if (_View_User.xhr !== false) {
                                    _View_User.xhr.abort();
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

                                if (response.fragments.updated_button_text) {

                                    if (response.fragments.updated_button_text == 'Deactivate') {

                                        $('#user-settings-button').parent().parent().removeClass('margin-left-minus-62');

                                        $('#user-settings-button').parent().parent().addClass('margin-left-minus-46');

                                    } else {

                                        $('#user-settings-button').parent().parent().removeClass('margin-left-minus-46');

                                        $('#user-settings-button').parent().parent().addClass('margin-left-minus-62');
                                    }

                                    $('#activate-button-text').text(_Base.capitalizeFirstLetter(response.fragments.updated_button_text));
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the profile activation. Please reload the page. If the error persists contact support.");
                        });
                    }
                })
            });
        },

        handleUserUpdate(previousValues, response) {

            $("#view-user-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).attr('placeholder');

                    previousValues[key] = value;
                }
            });
            
            $("#view-user-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

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

                    } else if (key === 'role') {

                        _Base.displayMessage("error", error);

                        field.val('User ' + key).change();

                    } else if (key === 'phone') {

                        _Base.displayMessage("error", error);

                    } else {

                        field.val('');

                        field.attr('placeholder', error);

                        field.attr('title', error);
                    }

                    field.addClass('is-invalid');

                    $("#view-user-form select").each(function() {
                        
                        if ($(this).hasClass('is-invalid')) {

                            let key = $(this).attr('name');

                            $(this).html(previousValues[key]);

                            if (key === 'phone_code_id' || key === 'default_currency_id' || key === 'role') {

                                if (key === 'phone_code_id') {

                                    $('#' + key.replace(/_/g, "-")).val('+XXX').change();
                                }

                                $('#' + key.replace(/_/g, "-")).selectpicker('destroy');

                                $('#' + key.replace(/_/g, "-")).selectpicker();
                            }
                        }

                        $(this).removeClass('is-invalid');

                        if (key === 'phone_code_id' || key === 'default_currency_id' || key === 'role') {

                            $(this).parent().removeClass('is-invalid');
                        }
                    });
                });
            }

            if (response.fragments.updated_fields) {

                $("#view-user-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                    if (!($(this).attr('name') in response.fragments.updated_fields) && $(this).val('') !== '') {

                        $(this).val('');
                    }
                });
                
                $.each(response.fragments.updated_fields, function (key, value) {

                    if (key === 'profile_picture') {

                        let currentTime = new Date().getTime();

                        $('#view-profile-picture').attr("src", value ? (value + '?' + currentTime) : '/uploads/profile-pictures/0.jpg');
                        
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

                            $('#phone-number').attr('title', '');

                        } else {

                            removePhoneNumberCheckbox.prop("checked", false);

                            removePhoneNumberCheckbox.prop("disabled", true);

                            $('#phone-code-id').val('+XXX').change();

                            $('#phone-code-id').selectpicker('destroy');

                            $('#phone-code-id').selectpicker();

                            $('#phone-number').attr('placeholder', 'Phone number');

                            $('#phone-number').attr('title', '');
                        }

                    } else if (key === 'credit_card_number') {

                        let removeCreditCardCheckbox = $('#remove-credit-card-number');
                        
                        if (value) {

                            removeCreditCardCheckbox.prop("disabled", false);

                        } else {

                            removeCreditCardCheckbox.prop("checked", false);

                            removeCreditCardCheckbox.prop("disabled", true);
                        }
                        
                    } else if (key === 'phone_code_id' || key === 'default_currency_id' || key === 'role') {

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

                        if (key !== 'password') {

                            field.attr('placeholder', value);
                        }

                        field.attr('title', '');

                        let copyButton = field.next().children().eq(0);

                        copyButton.attr('disabled', false);
                    }
                });

            } else {

                if (!response.fragments.errors) {

                    $("#view-user-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                        $(this).val('');
                    });
                }
            }
        }
    };
}))(jQuery);
