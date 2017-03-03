'use strict';

import $ from 'jquery';

function initAlerts(root) {
    $(root || ':root').find('.site-alerts .alert')
        .css('cursor', 'pointer')
        .click(function () {
            $(this).fadeOut(400, () => {
                $(this).remove();
            });
        });
}

export { initAlerts as init };
