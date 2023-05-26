let _Settings;

jQuery(document).ready(() => {
    _Settings.init();
});

(($ => {
    
    _Settings = {

        xhr: false,

        init() {

            _Base.ajaxDisplayMessage();

            let previousValues = {};

            $("#view-settings-form input").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).val();

                    previousValues[key] = value;
                }
            });

            const updateSettingsButton = $('#update-settings-button');

            updateSettingsButton.on('click', function (e) {

                let isFormValid = $('#view-settings-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#view-settings-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let form = new FormData();

                // the 'email[user]' and similar keys below need to be the same as the keys defined in the Controller.php file in the 'emailTemplates' array

                form.append("currency", $('input[name="currency"]:checked').val());

                // user settings
                form.append("email[user][hostChanged]", $('#event-host-changed-email').is(':checked') ? 1 : 0);
                form.append("email[user][hostDeleted]", $('#event-host-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[user][venueSet]", $('#event-venue-restored-email').is(':checked') ? 1 : 0);
                form.append("email[user][eventUpdatedHost]", $('#event-venue-changed-email').is(':checked') ? 1 : 0);
                form.append("email[user][venueDeleted]", $('#event-venue-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[user][eventDeleted]", $('#event-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[user][ownerApproved]", $('#owner-approved-event-email').is(':checked') ? 1 : 0);
                form.append("email[user][ownerRejected]", $('#owner-rejected-event-email').is(':checked') ? 1 : 0);
                form.append("email[user][artistApproved]", $('#artist-approved-event-email').is(':checked') ? 1 : 0);
                form.append("email[user][artistRejected]", $('#artist-rejected-event-email').is(':checked') ? 1 : 0);

                // owner settings
                form.append("email[owner][venueDeleted]", $('#venue-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[owner][eventDeleted]", $('#event-in-venue-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[owner][artistApproved]", $('#artist-approved-event-in-venue-email').is(':checked') ? 1 : 0);
                form.append("email[owner][artistRejected]", $('#artist-rejected-event-in-venue-email').is(':checked') ? 1 : 0);
                form.append("email[owner][hostDeleted]", $('#host-deleted-event-in-venue-email').is(':checked') ? 1 : 0);
                form.append("email[owner][hostSet]", $('#host-set-event-in-venue-email').is(':checked') ? 1 : 0);
                form.append("email[owner][artistDeleted]", $('#artist-deleted-event-in-venue-email').is(':checked') ? 1 : 0);
                form.append("email[owner][eventAddRequested]", $('#host-add-event-in-venue-email').is(':checked') ? 1 : 0);

                // host settings
                form.append("email[host][venueSet]", $('#host-event-venue-restored-email').is(':checked') ? 1 : 0);
                form.append("email[host][eventDeleted]", $('#host-event-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[host][venueDeleted]", $('#host-event-venue-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[host][ownerDeleted]", $('#host-event-venue-owner-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[host][ownerSet]", $('#host-event-venue-owner-restored-email').is(':checked') ? 1 : 0);
                form.append("email[host][ownerApproved]", $('#host-owner-approved-event-email').is(':checked') ? 1 : 0);
                form.append("email[host][ownerRejected]", $('#host-owner-rejected-event-email').is(':checked') ? 1 : 0);
                form.append("email[host][artistApproved]", $('#host-artist-approved-event-email').is(':checked') ? 1 : 0);
                form.append("email[host][artistRejected]", $('#host-artist-rejected-event-email').is(':checked') ? 1 : 0);
                form.append("email[host][artistDeleted]", $('#host-artist-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[host][eventAdded]", $('#host-admin-added-event-host-email').is(':checked') ? 1 : 0);
                form.append("email[host][eventUpdatedAdmin]", $('#host-admin-moved-event-new-venue-email').is(':checked') ? 1 : 0);

                // artist settings
                form.append("email[artist][ownerSet]", $('#artist-event-venue-owner-restored-email').is(':checked') ? 1 : 0);
                form.append("email[artist][venueDeleted]", $('#artist-event-venue-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[artist][ownerDeleted]", $('#artist-event-venue-owner-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[artist][hostChanged]", $('#artist-event-host-changed-email').is(':checked') ? 1 : 0);
                form.append("email[artist][venueSet]", $('#artist-event-venue-restored-email').is(':checked') ? 1 : 0);
                form.append("email[artist][eventDeleted]", $('#artist-event-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[artist][ownerApproved]", $('#artist-venue-owner-approved-event-email').is(':checked') ? 1 : 0);
                form.append("email[artist][ownerRejected]", $('#artist-venue-owner-rejected-event-email').is(':checked') ? 1 : 0);
                form.append("email[artist][hostDeleted]", $('#artist-event-host-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[artist][hostSet]", $('#artist-event-host-restored-email').is(':checked') ? 1 : 0);
                form.append("email[artist][artistDeleted]", $('#artist-event-participant-deleted-email').is(':checked') ? 1 : 0);
                form.append("email[artist][artistPending]", $('#artist-event-participant-added-email').is(':checked') ? 1 : 0);

                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                let username = location.pathname.split('/')[2];

                if (username == undefined) {

                    username = '';

                } else {

                    username = '/' + username;
                }

                _Settings.xhr = $.ajax({
                    url: '/settings' + username,
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_Settings.xhr !== false) {
                            _Settings.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {

                        if (response.fragments.notify) {
                            
                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.errors) {

                            $("#view-settings-form input").each(function() {

                                if ($(this).hasClass('is-invalid')) {
                
                                    $(this).removeClass('is-invalid');
                                }
                            });

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('input[name="' + key.replace(/_/g, "-") + '"]:checked');

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];

                                if (key === 'currency') {

                                    _Base.displayMessage("error", error);

                                    field.val(previousValues[key]);

                                } else if (key === 'user') {

                                    _Base.displayMessage("error", error);
                                }

                                field.addClass('is-invalid');
                            });

                        } else {

                            $("#view-settings-form input").each(function() {

                                if ($(this).hasClass('is-invalid')) {
                
                                    $(this).removeClass('is-invalid');
                                }
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the settings update. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
