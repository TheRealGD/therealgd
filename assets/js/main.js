'use strict';

// all of the js needs refactoring - it's so bad i don't even care anymore

window.$ = window.jQuery = require('jquery');
window.Translator = require('bazinga-translator');

import $ from 'jquery';

import relativeTime from './relative-time';
$(relativeTime);

import enableAjaxVoting from './vote';
$(enableAjaxVoting);

import { init as initAlerts } from './alerts';
$(initAlerts);

import {
    initWindow as dropdownInitWindow,
    initRoot as dropdownInitRoot
} from './dropdowns';
$(dropdownInitRoot);
$(dropdownInitWindow);

import initCommenting from './commenting';
$(initCommenting);

import fetchTitles from './fetch_titles';
$(fetchTitles);

import markdownPreview from './markdown';
$(markdownPreview);

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

$('.confirm-comment-delete').click(function () {
    return confirm(Translator.trans('prompt.confirm_comment_delete'));
});

$('code[class^="language-"]').each(function () {
    const nightMode = $('body').hasClass('night-mode');
    let language = this.className.replace(/.*language-(\S+).*/, "$1");

    if (language === 'html') {
        language = 'xml';
    }

    let theme;

    if (nightMode) {
        theme = import('highlight.js/styles/darkula.css');
    } else {
        theme = import('highlight.js/styles/tomorrow.css');
    }

    Promise.all([
        import('highlight.js/lib/highlight'),
        import(`highlight.js/lib/languages/${language}`),
        theme,
    ]).then(imports => {
        const [hljs, definition] = imports;

        hljs.registerLanguage(language, definition);
        hljs.highlightBlock(this);
    }).catch(e => {
        console && console.log(e);
    });
});
