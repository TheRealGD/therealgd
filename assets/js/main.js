'use strict';

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
