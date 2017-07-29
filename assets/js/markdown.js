'use strict';

import { debounce } from 'underscore';
import Routing from 'fosjsrouting';
import $ from 'jquery';

function createPreview() {
    $.ajax({
        url: Routing.generate('raddit_app_markdown_preview'),
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

export default function ($) {
    $('.markdown-input__input').on('input', debounce(createPreview, 600));
};
