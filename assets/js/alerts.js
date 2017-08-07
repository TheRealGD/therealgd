'use strict';

function initAlerts($) {
    $('.alert--dismissable')
        .css('cursor', 'pointer')
        .click(function () {
            $(this).fadeOut(400, () => {
                $(this).remove();
            });
        });
}

export { initAlerts as init };
