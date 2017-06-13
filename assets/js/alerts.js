'use strict';

function initAlerts($) {
    $('.site-alerts .alert')
        .css('cursor', 'pointer')
        .click(function () {
            $(this).fadeOut(400, () => {
                $(this).remove();
            });
        });
}

export { initAlerts as init };
