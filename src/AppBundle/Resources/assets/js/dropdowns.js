'use strict';

import $ from 'jquery';

/**
 * Make all dropdown menus interactive.
 *
 * @param {string} root
 */
function initRoot(root) {
    root = root || ':root';

    const container = $(root).find('.dropdown-container');

    $(container)
        .addClass('js')
        .find('.dropdown-toggle')
        .click(function (event) {
            event.stopPropagation();

            // close all other dropdowns
            $('.dropdown-container.expanded')
                .not(container)
                .removeClass('expanded')
                .find('.dropdown-toggle')
                .attr('aria-expanded', false);

            // toggle the current dropdown
            $(container).toggleClass('expanded');
            $(this).attr('aria-expanded', $(container).hasClass('expanded'));

            return false;
        });
}

/**
 * Adds a global click handler that closes dropdowns when clicking on something
 * that isn't a toggle.
 */
function initWindow() {
    $(window).click(() => {
        $('.dropdown-container.expanded')
            .removeClass('expanded')
            .find('.dropdown-toggle')
            .attr('aria-expanded', false);
    });
}

export { initWindow, initRoot };
