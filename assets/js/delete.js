'use strict';

import $ from 'jquery';

$(function () {
    $('.confirm-comment-delete').click(function () {
        return confirm(Translator.trans('prompt.confirm_comment_delete'));
    });

    $('.confirm-submission-delete').click(function () {
        return confirm(Translator.trans('prompt.confirm_submission_delete'));
    });
});
