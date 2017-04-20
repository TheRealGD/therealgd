'use strict';

import moment from 'moment';

function makeTimesRelative($) {
    $('.relative-time[datetime]').each(function () {
        const isoTime = $(this).attr('datetime');

        $(this).text(moment(isoTime).fromNow());
    });
}

function loadLocaleAndMakeTimesRelative($, locale) {
    import(`moment/src/locale/${locale}.js`).then(() => {
        moment.locale(locale);

        makeTimesRelative($);
    }).catch(error => {
        console && console.log('relative-time.js - ' + error);
    });
}

export default function ($) {
    const locale = $(':root').attr('lang');

    if (!locale || locale === 'en') {
        // english is the default, always-loaded locale in moment
        makeTimesRelative($);
    } else {
        loadLocaleAndMakeTimesRelative($, locale);
    }
};
