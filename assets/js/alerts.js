'use strict';

import $ from 'jquery';

$(function () {
    $('.alert--dismissable')
        .css('cursor', 'pointer')
        .click(function () {
            $(this).slideUp(400, () => {
                $(this).remove();
            });
        });
});
