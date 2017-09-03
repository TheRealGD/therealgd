'use strict';

function initAlerts($) {
    $('.alert--dismissable')
        .css('cursor', 'pointer')
        .click(function () {
            $(this).slideUp(400, () => {
                $(this).remove();
            });
        });
}

export { initAlerts as init };
