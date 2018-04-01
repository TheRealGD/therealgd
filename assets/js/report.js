'use strict';

import $ from 'jquery';

$(function () {
    $('.confirm-submission-report').click(function () {
        return confirm(Translator.trans('prompt.confirm_submission_report'));
    });
});
