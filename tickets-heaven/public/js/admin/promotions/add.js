let _Add_Promo_Code;

jQuery(document).ready(() => {
    _Add_Promo_Code.init();
});

(($ => {

    _Add_Promo_Code = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $('#event').selectpicker();

            let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

            let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);
            
            $('#deadline').attr('min', localISOTime);

            const generatePromoCodeButton = $('#generate-promo-code');

            generatePromoCodeButton.on('click', function (e) {

                e.preventDefault();

                const promoCode = _Base.generatePromoCode(6);

                $('#code').val(promoCode);
            });

            const addPromoCodeButton = $('#add-promo-code-button');

            let previousValues = {};

            $("#add-promo-code-form select").each(function() {

                if (!$(this).hasClass('is-invalid')) {

                    let key = $(this).attr('name');

                    let value = $(this).html();

                    previousValues[key] = value;
                }
            });

            addPromoCodeButton.on('click', function (e) {

                let isFormValid = $('#add-promo-code-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#add-promo-code-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                let form = new FormData();

                form.append("code", $('#code').val());
                form.append("event", $('#event').val());
                form.append("percent", $('#percent').val());
                form.append("deadline", $('#deadline').val());
                form.append("csrf_name", $('#ajax_csrf_name').data('value'));
                form.append("csrf_value", $('#ajax_csrf_value').data('value'));

                _Add_Promo_Code.xhr = $.ajax({
                    url: '/admin/promotions/add',
                    type: 'POST',
                    contentType: false,
                    processData: false,
                    data: form,
                    beforeSend() {
                        if (_Add_Promo_Code.xhr !== false) {
                            _Add_Promo_Code.xhr.abort();
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

                        $("#add-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {
                            
                            if (!$(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                let value = $(this).attr('placeholder');

                                previousValues[key] = value;
                            }
                        });
                        
                        $("#add-promo-code-form input[type=text],[type=password],[type=email],[type=number], textarea[type=text]").each(function() {

                            if ($(this).hasClass('is-invalid')) {

                                let key = $(this).attr('name');

                                $(this).attr('placeholder', previousValues[key]);
                            }

                            $(this).removeClass('is-invalid');
                        });

                        if (response.fragments.notify) {
                            
                            _Base.displayMessage(response.fragments.notify.type, response.fragments.notify.notice);
                        }

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace(/_/g, "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];

                                if (key === 'event' || key === 'deadline') {

                                    _Base.displayMessage("error", error);

                                } else {

                                    field.val('');

                                    field.attr('placeholder', error);

                                    field.attr('title', error);
                                }

                                field.addClass('is-invalid');

                                $("#add-promo-code-form select").each(function() {
                            
                                    if ($(this).hasClass('is-invalid')) {

                                        let key = $(this).attr('name');

                                        $(this).html(previousValues[key]);

                                        if (key === 'event') {

                                            $('#' + key.replace('_', '-')).selectpicker('destroy');

                                            $('#' + key.replace('_', '-')).selectpicker();
                                        }
                                    }
        
                                    $(this).removeClass('is-invalid');

                                    if (key === 'event') {
                                        
                                        $(this).parent().removeClass('is-invalid');
                                    }
                                });
                            });
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the promo code adding process. Please reload the page. If the error persists contact support.");
                });
            });
        },
    };
}))(jQuery);
