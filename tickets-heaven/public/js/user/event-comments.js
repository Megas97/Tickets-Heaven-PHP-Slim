let _Event_Comments;

jQuery(document).ready(() => {
    _Event_Comments.init();
});

(($ => {
    
    _Event_Comments = {

        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            $('.view-modal').each(function () {

                $(this).on('shown.bs.modal', function (e) {

                    const modal = $(this);

                    modal.find('#comment-box').scrollTop(modal.find('#comment-box').prop('scrollHeight'));

                    modal.find("#add-comment-form").off('submit').on('submit', function (e) {

                        e.preventDefault();

                        let isFormValid = modal.find('#add-comment-form')[0].checkValidity();

                        if (!isFormValid) {

                            modal.find('#add-comment-form')[0].reportValidity();

                            return false;

                        } else {

                            e.preventDefault();
                        }

                        _Event_Comments.xhr = $.ajax({
                            url: '/events/comments/add',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                'event_id': modal.find('#event-id').val(),
                                'comment': modal.find('#comment').val(),
                                'csrf_name': $('#ajax_csrf_name').data('value'),
                                'csrf_value': $('#ajax_csrf_value').data('value')
                            },
                            beforeSend() {
                                if (_Event_Comments.xhr !== false) {
                                    _Event_Comments.xhr.abort();
                                }
                            }
                        }).done(response => {

                            if (response !== false && response !== undefined && response !== '') {
                                
                                modal.find("#add-comment-form input[type=text]").each(function() {

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

                                        let field = modal.find('#' + key.replace("_", "-"));

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

                                    if (modal.find('#comment-box').length == 0) {

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
                                    
                                    modal.find('#comment-box').append(newComment);

                                    modal.find('#comment-box').scrollTop(modal.find('#comment-box').prop('scrollHeight'));
                                    
                                    if (commentBoxExpanded && !_Base.isElementInViewport(modal.find('#comment-box'))) {
                                        
                                        modal.find(".modal-body").animate({ scrollTop: modal.find('.modal-body').prop("scrollHeight")}, 'slow');
                                    }
                                }
                            }
                        }).fail(response => {
                            _Base.displayMessage("error", "Unable to process the comment adding. Please reload the page. If the error persists contact support.");
                        });
                    });
                });
            });
        }
    };
}))(jQuery);
