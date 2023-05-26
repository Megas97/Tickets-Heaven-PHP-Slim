let _Base;

jQuery(document).ready(() => {
    _Base.init();
});

(($ => {
    
    _Base = {

        xhr: false,

        autoScrollInterval: [],

        init() {
            
            history.replaceState("", document.title, window.location.pathname + window.location.search);

            $('#toastMessageContainer').addClass('d-none');

            $('input, textarea').on('input', function (e) {

                e.preventDefault();

                let copyButton = $(this).next().children().eq(0);

                if (copyButton.length > 0 && copyButton.hasClass('copy-button') && copyButton.attr('disabled')) {

                    copyButton.attr('disabled', false);

                    copyButton.data('disable-after-click', true);
                }
            });

            $('.copy-button').each(function () {

                let copyButton = $(this);

                copyButton.on('click', function (e) {

                    let inputElement = $(this).parent().prev();
                    
                    if (inputElement.val() == '') {

                        let savedData = inputElement.attr('placeholder');

                        inputElement.val(savedData);

                    } else {

                        inputElement.val('');

                        if (copyButton.data('disable-after-click')) {

                            copyButton.attr('disabled', true);

                            copyButton.removeAttr('data-disable-after-click');
                        }
                    }
                });
            });

            $('.clear-button').each(function () {

                let clearButton = $(this);

                clearButton.on('click', function (e) {

                    let inputElement = $(this).parent().prev();

                    if (inputElement.attr('id') == 'checkout-remove-promo-code-button') {

                        inputElement = inputElement.prev().prev();
                    }

                    inputElement.val('');
                });
            });

            let originalSelectWidth = [];

            $('span[title]').click(function () {

                if (!$(this).hasClass('cut-text-off')) {
                    
                    let title = $(this).find('.title');

                    if (!title.length) {

                        let extraInfoElement = $('<span class="title ms-1 extra-info unselectable">' + $(this).attr("title") + '</span>');

                        $(this).append(extraInfoElement);
                        
                        let element = $(this).parent().next();
                        
                        if ((element.is('select') || element.is('div')) && !element.next().is('#phone-number') && !element.next().is('#ticket-price')) {

                            originalSelectWidth[element.attr('id')] = element.css('width').replace('px', '');

                            let widthToRemove = extraInfoElement.parent().css('width').replace('px', '');

                            let width = element.css('width').replace('px', '');

                            let pixelsToAdd = element.is('select') ? 42 : 38;

                            element.css('width', width - widthToRemove + pixelsToAdd);
                        }

                    } else {

                        title.remove();

                        let element = $(this).parent().next();

                        if ((element.is('select') || element.is('div')) && !element.next().is('#phone-number') && !element.next().is('#ticket-price') && originalSelectWidth[element.attr('id')] != null) {

                            element.css('width', originalSelectWidth[element.attr('id')]);

                            originalSelectWidth[element.attr('id')] = null;
                        }
                    }
                }
            });

            $('.carousel-caption').on('click', function (e) {

                if ($(this).css('opacity') == 0) {

                    e.preventDefault();
                }
            });

            $(document).on('click', function (e) {

                let navBarElement = $('#navbarSupportedContent')[0];

                if (!navBarElement.contains(e.target)) {

                    $('#navbarSupportedContent').collapse('hide');
                }
            });
        },

        displayMessage(type, message) {

            $('#toastMessageContainer').removeClass('d-none');

            let messageElement = $('#' + type + '-message');

            let toastElement = $('#toastMessage');

            // Reset all previous toast elements before creating a new one

            toastElement.removeClass('info-toast');

            $('#info-message').addClass('d-none');

            $('#info-message').prop('innerHTML', '');

            toastElement.removeClass('error-toast');

            $('#error-message').addClass('d-none');

            $('#error-message').prop('innerHTML', '');

            messageElement.prop('innerHTML', message);
            
            messageElement.removeClass('d-none');

            toastElement.addClass(type + '-toast');

            toastElement.toast("show");

            setTimeout(function() {

                $('#toastMessageContainer').addClass('d-none');
            }, 5500);
        },

        ajaxDisplayMessage() {

            let urlParams = new URLSearchParams(window.location.search);
            
            if (urlParams.has('fragments')) {

                let fragments = JSON.parse(urlParams.get('fragments'));

                urlParams.delete('fragments');

                let questionMark = urlParams == '' ? '' : '?';

                history.replaceState("", document.title, window.location.pathname + questionMark + urlParams);

                if (fragments.notify) {
                    
                    _Base.displayMessage(fragments.notify.type, fragments.notify.notice);
                }
            }
        },

        capitalizeFirstLetter(string) {

            return string.charAt(0).toUpperCase() + string.slice(1);
        },

        removeTags(str) {

            if ((str === null) || (str === '')) {

                return false;

            } else {

                str = str.toString();
            }

            return str.replace(/(<([^>]+)>)/ig, '');
        },

        isElementInViewport(el) {

            // Special bonus for those using jQuery
            if (typeof jQuery === "function" && el instanceof jQuery) {
                
                el = el[0];
            }
        
            var rect = el.getBoundingClientRect();
        
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) && /* or $(window).height() */
                rect.right <= (window.innerWidth || document.documentElement.clientWidth) /* or $(window).width() */
            );
        },

        handleEventHappeningLabel(parentElementIdentifier) {

            let currentDateTime = new Date();

            let cards = $(parentElementIdentifier);
            
            cards.each(function (e) {

                let card = $(this);
                
                let startDateArray = card.find('.start-date').val().split('.');

                let startTimeArray = card.find('.start-time').val().split(':');
                
                let startDatetime = new Date(startDateArray[2] + '-' + startDateArray[1] + '-' + startDateArray[0] + 'T' + startTimeArray[0] + ':' + startTimeArray[1]);
                
                let endDateArray = card.find('.end-date').val().split('.');

                let endTimeArray = card.find('.end-time').val().split(':');
                
                let endDatetime = new Date(endDateArray[2] + '-' + endDateArray[1] + '-' + endDateArray[0] + 'T' + endTimeArray[0] + ':' + endTimeArray[1]);
                
                if ((currentDateTime >= startDatetime) && (currentDateTime < endDatetime)) {
                    
                    card.find('.happening-text').removeClass('d-none');

                } else if (currentDateTime > endDatetime) {
                    
                    card.find('.approve-event-button').remove();

                    if ($('#type').val() != 'pending') {
                        
                        card.find('.reject-event-button').remove();
                    }
                }
            });
        },

        handleMultipleCarouselBehavior(targetCarousel) {

            let carouselWidth = targetCarousel.find('.carousel-inner')[0].scrollWidth;

            let cardWidth = targetCarousel.find(".carousel-item").width();

            let visibleCardsCount = targetCarousel.attr('id') == 'multiArtistsCarousel' ? 5 : 3; // change this value if you increase the cards displayed at the same time on the screen

            let interval = 5000; // interval (in milliseconds) after which the carousel goes to the next / previous card automatically

            let scrollPosition = 0;

            let currentClickCount = 0;

            let carouselCardsCount = targetCarousel.find('.carousel-item').length;

            let carouselCardsCountMinusVisibleOnes = carouselCardsCount - visibleCardsCount;
            
            if (carouselCardsCount > visibleCardsCount) {

                let direction = 'next';

                let navbarDropdownOpen = false;

                targetCarousel.find('.carousel-control-next').on('click', function () {
                    
                    if (scrollPosition < (carouselWidth - cardWidth * 4)) {

                        scrollPosition += cardWidth;

                        currentClickCount++;

                        targetCarousel.find('.carousel-inner').animate({ scrollLeft: scrollPosition }, 600);

                        clearInterval(_Base.autoScrollInterval[targetCarousel.attr('id')]);

                        _Base.autoScrollInterval[targetCarousel.attr('id')] = setInterval(function (e) {

                            if (currentClickCount == 0) {
    
                                direction = 'next';
            
                            } else if (currentClickCount == carouselCardsCountMinusVisibleOnes) {
            
                                direction = 'prev';
                            }
    
                            if (!navbarDropdownOpen) {
                                
                                targetCarousel.find('.carousel-control-' + direction).click();
                            }
                        }, interval);
                    }
                });

                targetCarousel.find('.carousel-control-prev').on('click', function () {

                    if (scrollPosition > 0) {

                        scrollPosition -= cardWidth;

                        currentClickCount--;

                        targetCarousel.find('.carousel-inner').animate({scrollLeft: scrollPosition}, 600);

                        clearInterval(_Base.autoScrollInterval[targetCarousel.attr('id')]);

                        _Base.autoScrollInterval[targetCarousel.attr('id')] = setInterval(function (e) {

                            if (currentClickCount == 0) {
    
                                direction = 'next';
            
                            } else if (currentClickCount == carouselCardsCountMinusVisibleOnes) {
            
                                direction = 'prev';
                            }
    
                            if (!navbarDropdownOpen) {
                                
                                targetCarousel.find('.carousel-control-' + direction).click();
                            }
                        }, interval);
                    }
                });
                
                if (window.matchMedia("(min-width: 768px)").matches) {
                    
                    let carousel = new bootstrap.Carousel(targetCarousel, {interval: false});

                    $(document).on('click', function (e) {

                        let navBarElement = $('#navbarSupportedContent')[0];
        
                        if (navBarElement.contains(e.target)) {

                            navbarDropdownOpen = !navbarDropdownOpen ? true : false;
                            
                        } else {

                            navbarDropdownOpen = false;
                        }
                    });
                    
                    _Base.autoScrollInterval[targetCarousel.attr('id')] = setInterval(function (e) {

                        if (currentClickCount == 0) {

                            direction = 'next';
        
                        } else if (currentClickCount == carouselCardsCountMinusVisibleOnes) {
        
                            direction = 'prev';
                        }

                        if (!navbarDropdownOpen) {
                            
                            targetCarousel.find('.carousel-control-' + direction).click();
                        }
                    }, interval);

                } else {
                    
                    $(targetCarousel).addClass("slide");
                }

            } else {

                if (window.matchMedia("(max-width: 767px)").matches) {

                    $(targetCarousel).addClass("slide");
                }
            }
        },

        handlePanelButtonBrightnessAnimation() {

            $('.image-button').on('mouseover', function (e) {

                $(this).css('filter', 'brightness(80%)');
            });

            $('.image-button').on('mouseout', function (e) {

                $(this).css('filter', 'brightness(100%)');
            });
        },

        createDataTableDropdownFilters(table) {

            table.columns().every(function() {

                const column = this;

                if ($(column.header()).hasClass('dropdown-filter')) {

                    const select = $('<select class="mx-1 mb-2 mb-0 form-control-sm"><option value="">-- ' + $(column.header()).html() + ' --</option></select>')
                        .prependTo($(table.table().container()).find('.dataTables_filter'))
                        .on('change', function() {
                            const val = $.fn.dataTable.util.escapeRegex($(this).val());
                            const regExSearch = '^' + val + '';

                            column.search(val ? "^" + regExSearch : '', true, false, false).draw();
                        });

                    const sortVals = [];

                    column.data().unique().sort().each(function(d, j) {

                        const split = d.toString().split(', ');

                        split.forEach(function(val) {

                            val = _Base.removeTags(val).trim();

                            if ($.inArray(val, sortVals) === -1) {

                                sortVals[j] = d;

                                if (val != '') {

                                    select.append('<option value="' + val + '">' + val + '</option>');
                                }
                            }
                        });
                    });
                }
            });
        },

        handleEventStatisticsForMultipleCards(previousValues) {

            const viewStaticticsButtons = $('.view-event-statistics-button');
            
            const statisticsLoaded = [];

            viewStaticticsButtons.each(function (e) {

                const viewStaticticsButton = $(this);

                const eventId = viewStaticticsButton.attr('href').replace('#view-event-statistics-modal-', '');

                let card = $('#card-' + eventId);

                let timezoneOffset = (new Date()).getTimezoneOffset() * 60000;

                let localISOTime = (new Date(Date.now() - timezoneOffset)).toISOString().slice(0, 16);

                card.find('#start').attr('max', localISOTime);

                card.find('#end').attr('max', localISOTime);

                let now = new Date();

                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

                let formatted = now.toISOString().slice(0,16);

                card.find('#start').val(formatted);

                card.find('#end').val(formatted);

                statisticsLoaded[eventId] = false;

                card.find("#view-event-statistics-modal-" + eventId + " input[type=datetime-local]").each(function() {

                    if (!$(this).hasClass('is-invalid')) {

                        let key = $(this).attr('name');
    
                        let value = $(this).val();
    
                        previousValues[key] = value;
                    }
                });

                let table = card.find('#eventStatisticsTable').DataTable({
                    dom: "<'row'<'col-sm-24 col-md-12'><'col-sm-24 col-md-12 d-flex justify-content-end mb-2'B>>" +
                            "<'row'<'col-sm-24 col-md-12'l><'col-sm-24 col-md-12'f>>" +
                            "<'row'<'col-sm-24'tr>>" +
                            "<'row'<'col-sm-24 col-md-10'i><'col-sm-24 col-md-14'p>>",
                    deferRender: true,
                    'pagingType': 'simple',
                    'columnDefs': [
                        { visible: false, targets: [0] },
                        { orderable: false, targets: [1, 2, 3, 4, 5] },
                        { searchable: false, targets: [0] },
                    ],
                    'order': [
                        [0, 'desc']
                    ],
                    'lengthMenu': [
                        [20, 50, -1],
                        [20, 50, 'All']
                    ],
                    responsive: true,
                    language: {
                        search: '_INPUT_',
                        searchPlaceholder: 'Search Event Statistics'
                    },
                    data: [],
                    columns: [
                        { data: 'order.id', className: 'text-center align-middle' },
                        { data: 'order.user', className: 'text-center align-middle' },
                        { data: 'order.ticket_quantity', className: 'text-center align-middle' },
                        { data: 'order.single_price', className: 'text-center align-middle' },
                        { data: 'order.total_price', className: 'text-center align-middle' },
                        { data: 'order.date', className: 'text-center align-middle' },
                    ],
                });

                viewStaticticsButton.on('click', function (e) {

                    if (statisticsLoaded[eventId]) {
    
                        return false;
                    }
                    
                    $.ajax({
                        url: '/datatable/read',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'modelData': 'EventStatistics',
                            'current_id': card.find('#current-id').val(),
                            'start': card.find('#start').val(),
                            'end': card.find('#end').val(),
                            'csrf_name': $('#ajax_csrf_name').data('value'),
                            'csrf_value': $('#ajax_csrf_value').data('value')
                        }
                    }).done(function (response) {
                        
                        if (window.matchMedia("(max-width: 1025px)").matches) {
                        
                            table.columns.adjust().draw();
                        }
                        
                        table.clear().draw();
    
                        table.search('').draw();
    
                        table.columns().search('').draw();
    
                        table.rows.add(response.data).draw();

                        card.find('#total-sold-tickets').html(response.totalTickets);
    
                        card.find('#total-event-income').html(response.totalIncome);
    
                        _Base.createDataTableDropdownFilters(table);
    
                        statisticsLoaded[eventId] = true;
                    })
                });

                const viewStatisticsResultsButton = card.find('#view-statistics-results-button');

                viewStatisticsResultsButton.on('click', function (e) {

                    if (window.matchMedia("(max-width: 1025px)").matches) {
                        
                        table.columns.adjust().draw();
                    }
                    
                    $.ajax({
                        url: '/datatable/read',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'modelData': 'EventStatistics',
                            'current_id': card.find('#current-id').val(),
                            'start': card.find('#start').val(),
                            'end': card.find('#end').val(),
                            'csrf_name': $('#ajax_csrf_name').data('value'),
                            'csrf_value': $('#ajax_csrf_value').data('value')
                        }
                    }).done(function (response) {
    
                        if (response.fragments && response.fragments.errors) {
    
                            $.each(response.fragments.errors, function (key, value) {
    
                                let field = card.find('#' + key.replace(/_/g, "-"));
    
                                let error = value[_Base.capitalizeFirstLetter(key).replace(/_/g, " ")];
    
                                if (key === 'start' || key === 'end') {
    
                                    _Base.displayMessage("error", error);
    
                                } else {
    
                                    field.val('');
    
                                    field.attr('placeholder', error);
                                }
    
                                field.addClass('is-invalid');
                            });
    
                            card.find("#view-event-statistics-modal-" + eventId + " input[type=datetime-local]").each(function() {
    
                                if ($(this).hasClass('is-invalid')) {
    
                                    let key = $(this).attr('name');
    
                                    $(this).val(previousValues[key]);
                                }
                            });
    
                        } else {
                        
                            table.clear().draw();
    
                            table.search('').draw();
    
                            table.columns().search('').draw();
                            
                            table.rows.add(response.data).draw();
    
                            card.find('.dataTables_filter').children().each(function (e) {
    
                                if ($(this).is('select')) {
    
                                    $(this).remove();
                                }
                            });
    
                            card.find('#total-sold-tickets').html(response.totalTickets);
    
                            card.find('#total-event-income').html(response.totalIncome);
    
                            _Base.createDataTableDropdownFilters(table);
    
                            card.find("#view-event-statistics-modal-" + eventId + " input[type=datetime-local]").each(function() {
    
                                if ($(this).hasClass('is-invalid')) {
    
                                    $(this).removeClass('is-invalid');
                                }
    
                                if (!$(this).hasClass('is-invalid')) {
    
                                    let key = $(this).attr('name');
                
                                    let value = $(this).val();
                
                                    previousValues[key] = value;
                                }
                            });
                        }
                    })
                });
            });
        },

        generatePromoCode(length) {
            
            let result = '';

            let characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

            let charactersLength = characters.length;

            for (let i = 0; i < length; i++) {

                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }

            return result;
        },

        handleCarouselCardTextTruncation(targetElements, page = null) {

            let descriptionCharactersToShow = 130;

            let titleCharactersToShow = page == 'home' ? 28 : 42;

            if (window.matchMedia("(max-width: 300px)").matches) {

                descriptionCharactersToShow = page == 'home' ? 40 : 70;

                titleCharactersToShow = page == 'home' ? 14 : 32;

            } else if (window.matchMedia("(min-width: 301px)").matches && window.matchMedia("(max-width: 379px)").matches) {

                descriptionCharactersToShow = page == 'home' ? 60 : 110;

                titleCharactersToShow = page == 'home' ? 16 : 22;

            } else if (window.matchMedia("(min-width: 380px)").matches && window.matchMedia("(max-width: 430px)").matches) {

                descriptionCharactersToShow = 120;

                titleCharactersToShow = page == 'home' ? 22 : 40;

            } else if (window.matchMedia("(min-width: 431px)").matches && window.matchMedia("(max-width: 500px)").matches) {

                descriptionCharactersToShow = 120;

                titleCharactersToShow = page == 'home' ? 24 : 42;

            } else if (window.matchMedia("(min-width: 501px)").matches && window.matchMedia("(max-width: 510px)").matches) {

                descriptionCharactersToShow = 120;

                titleCharactersToShow = page == 'home' ? 26 : 42;

            } else if (window.matchMedia("(min-width: 511px)").matches && window.matchMedia("(max-width: 550px)").matches) {

                descriptionCharactersToShow = 120;

                titleCharactersToShow = page == 'home' ? 30 : 42;

            } else if (window.matchMedia("(min-width: 551px)").matches && window.matchMedia("(max-width: 767px)").matches) {

                descriptionCharactersToShow = 120;

                titleCharactersToShow = page == 'home' ? 32 : 42;

            } else if (window.matchMedia("(min-width: 768px)").matches && window.matchMedia("(max-width: 991px)").matches) {

                descriptionCharactersToShow = page == 'home' ? 60 : 120;

                titleCharactersToShow = page == 'home' ? 14 : 42;

            } else if (window.matchMedia("(min-width: 992px)").matches && window.matchMedia("(max-width: 1199px)").matches) {

                descriptionCharactersToShow = page == 'home' ? 90 : 120;

                titleCharactersToShow = page == 'home' ? 18 : 42;

            } else if (window.matchMedia("(min-width: 1200px)").matches && window.matchMedia("(max-width: 1399px)").matches) {

                descriptionCharactersToShow = page == 'home' ? 100 : 120;

                titleCharactersToShow = page == 'home' ? 20 : 42;
            }
            
            targetElements.each(function (e) {

                let description = $(this);
                
                if (description.text().length > descriptionCharactersToShow) {

                    description.text(description.text().substring(0, descriptionCharactersToShow - 3) + '...');
                }

                let title = description.prev();

                if (title.text().length > titleCharactersToShow) {

                    title.text(title.text().substring(0, titleCharactersToShow - 3) + '...');
                }
            });
        }
    };
}))(jQuery);
