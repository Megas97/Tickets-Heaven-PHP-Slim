let _Event_Details;

jQuery(document).ready(() => {
    _Event_Details.init();
});

(($ => {
    
    _Event_Details = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            _Base.handleEventHappeningLabel('.big-event-card');

            let currentDateTime = new Date(); 

            let cards = $('.big-event-card');

            cards.each(function (e) {

                let card = $(this);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);

                if (currentDateTime >= endDatetime) {

                    let buyTicketForm = $('#buy-ticket-form');

                    let disabledButton = $('<button type="button" class="btn btn-secondary mt-1 mt-md-4 mb-1" disabled>Event Has Passed</button>');

                    disabledButton.insertAfter(buyTicketForm);

                    buyTicketForm.remove();
                }
            });
            
            let decreaseQuantity = $("#decrease-ticket-quantity");

            let ticketQuantity = $("#choose-ticket-quantity");

            let increaseQuantity = $("#increase-ticket-quantity");
			
			let buyTicketButton = $('#buy-ticket-button');
            
            decreaseQuantity.on('click', function () {

                if (+ticketQuantity.val() - +1 < ticketQuantity.attr('min')) {

                    return false;
                }

                ticketQuantity.val(+ticketQuantity.val() - +1);
				
				buyTicketButton.html(ticketQuantity.val() == 1 ? 'Buy Ticket' : 'Buy Tickets');
            });

            increaseQuantity.on('click', function () {

                if (+ticketQuantity.val() + +1 > ticketQuantity.attr('max')) {

                    return false;
                }

                ticketQuantity.val(+ticketQuantity.val() + +1);
				
				buyTicketButton.html(ticketQuantity.val() == 1 ? 'Buy Ticket' : 'Buy Tickets');
            });

            ticketQuantity.keypress(function (e) {

                e.preventDefault();
            });

            $('#comment-box').scrollTop($('#comment-box').prop('scrollHeight'));

            $("#add-comment-form").submit(function (e) {

                let isFormValid = $('#add-comment-form')[0].checkValidity();

                if (!isFormValid) {

                    $('#add-comment-form')[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Event_Details.xhr = $.ajax({
                    url: '/events/comments/add',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'event_id': $('#event-id').val(),
                        'comment': $('#comment').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Event_Details.xhr !== false) {
                            _Event_Details.xhr.abort();
                        }
                    }
                }).done(response => {

                    if (response !== false && response !== undefined && response !== '') {
                        
                        $("#add-comment-form input[type=text]").each(function() {

                            $(this).removeClass('is-invalid');

                            $(this).val('');

                            $(this).attr('placeholder', 'Add comment');
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

                        if (response.fragments.errors) {

                            $.each(response.fragments.errors, function (key, value) {

                                let field = $('#' + key.replace("_", "-"));

                                let error = value[_Base.capitalizeFirstLetter(key).replace("_", " ")];

                                field.val('');

                                field.attr('placeholder', error);

                                field.addClass('is-invalid');
                            });
                        }

                        if (response.fragments.comment) {

                            let commentObj = response.fragments.comment;

                            let separator = '<br>';

                            let commentBoxExpanded = false;

                            if ($('#comment-box').length == 0) {

                                $(this).parent().prepend('<div class="comment-box" id="comment-box"></div>');

                                separator = '';

                                $(this).removeClass('no-comments');

                                commentBoxExpanded = true;
                            }

                            let newComment = separator +
                                '<div class="d-flex align-items-center">' +
                                    '<img class="img-fluid img-responsive rounded-circle mb-2 me-1" src="' + (commentObj.user_picture ? commentObj.user_picture : '/uploads/profile-pictures/0.jpg') + '" width="18">' +
                                    '<h6>' + commentObj.full_name + '</h6><span class="mb-2 comment-poster-dot"></span><span class="mb-2">' + commentObj.created_diff + '</span>' +
                                '</div>' +
                                ' <div>' + commentObj.comment + '</div>';

                            $('#comment-box').append(newComment);

                            $('#comment-box').scrollTop($('#comment-box').prop('scrollHeight'));

                            if (commentBoxExpanded && !_Base.isElementInViewport($('#comment-box'))) {
                                
                                window.scrollTo(0, document.body.scrollHeight);
                            }
                        }
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the comment adding. Please reload the page. If the error persists contact support.");
                });
            });

            $('#buy-ticket-form').submit(function (e) {

                let isFormValid = $(this)[0].checkValidity();

                if (!isFormValid) {

                    $(this)[0].reportValidity();

                    return false;

                } else {

                    e.preventDefault();
                }

                _Event_Details.xhr = $.ajax({
                    url: '/event-details/' + $('#event-id').val(),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'choose_ticket_quantity': $('#choose-ticket-quantity').val(),
                        'csrf_name': $('#ajax_csrf_name').data('value'),
                        'csrf_value': $('#ajax_csrf_value').data('value')
                    },
                    beforeSend() {
                        if (_Event_Details.xhr !== false) {
                            _Event_Details.xhr.abort();
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

                        $('#cart-items-quantity').html(response.fragments.cart_items_count > 0 ? response.fragments.cart_items_count : '');
                    }
                }).fail(response => {
                    _Base.displayMessage("error", "Unable to process the ticket buying. Please reload the page. If the error persists contact support.");
                });
            });
        }
    };
}))(jQuery);
