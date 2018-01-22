'use strict';

import { debounce } from 'underscore';
// noinspection NpmUsedModulesInstalled
import Routing from 'fosjsrouting';
import $ from 'jquery';

function createPreview() {
    $.ajax({
        url: Routing.generate('markdown_preview'),
        method: 'POST',
        dataType: 'html',
        data: { markdown: $(this).val() }
    }).done(content => {
        const html = content.length > 0
            ? `<div class="markdown-input__preview">${content}</div>`
            : '';

        $(this)
            .closest('.markdown-input')
            .find('.markdown-input__preview-container')
            .html(html);
    });
}

$(function () {
    $(document).on('input', '.markdown-input__input', debounce(createPreview, 600));
});
