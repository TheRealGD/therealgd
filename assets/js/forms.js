'use strict';

import $ from 'jquery';

const widgetsChanged = new Set();
let hasBeforeUnloadListener = false;

function beforeUnloadHandler(event) {
    event.preventDefault();

    // the message doesn't matter since modern browsers don't display it
    event.returnValue = 'Leave the page? You\'ll lose your changes.';

    // some crappy browsers want this
    return event.returnValue;
}

function changeHandler(event) {
    // todo: need a better way to check nothing was changed
    if (event.target.value !== '') {
        widgetsChanged.add(event.target);
    } else if (widgetsChanged.has(event.target)) {
        widgetsChanged.delete(event.target);
    }

    if (!hasBeforeUnloadListener && widgetsChanged.size > 0) {
        $(window).on('beforeunload', beforeUnloadHandler);

        hasBeforeUnloadListener = true;
    } else if (hasBeforeUnloadListener && widgetsChanged.size === 0) {
        $(window).off('beforeunload', beforeUnloadHandler);

        hasBeforeUnloadListener = false;
    }
}

$(document)
    .on('change', '.form', changeHandler)
    .on('input', '.form', changeHandler)
    .on('submit', '.form', event => {
        if (!event.isPropagationStopped()) {
            $(window).off('beforeunload', beforeUnloadHandler);

            hasBeforeUnloadListener = false;
        }
    });
