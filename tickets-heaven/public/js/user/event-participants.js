let _Event_Participants;

jQuery(document).ready(() => {
    _Event_Participants.init();
});

(($ => {
    
    _Event_Participants = {
        
        xhr: false,

        init() {
            
            _Base.ajaxDisplayMessage();

            const participantsModals = $('[id*="view-participants-modal-button-"]');

            participantsModals.each(function () {

                $(this).on('click', function (e) {

                    let modalElements = $('.modal');

                    modalElements.each(function () {

                        if ($(this).hasClass('show')) {

                            $(this).modal('hide');

                        }

                    });

                    let participantsModalId = $(this).attr('id').replace('-button', '');
                    
                    let participantsModalElement = $('#' + participantsModalId);

                    participantsModalElement.modal('show');

                    participantsModalElement.on('hide.bs.modal', function (e) {

                        let detailsModalId = $(this).attr('id').replace('participants', 'details');

                        let detailsModalElement = $('#' + detailsModalId);

                        detailsModalElement.modal('show');

                    });
                });
            });
        },
    };
}))(jQuery);
