'use strict';

import $ from 'jquery';

$(function () {
    $('.select2').each(function () {
        Promise.all([
            import('select2'),
            import('select2/dist/css/select2.css')
        ]).then(() => {
            $(this).select2();
        }).catch(e => {
            console.log(e);
        });
    });
});
