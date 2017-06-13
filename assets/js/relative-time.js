'use strict';

import moment from 'moment/src/moment';
import Translator from 'bazinga-translator';

function makeTimesRelative($) {
    $('.relative-time[datetime]').each(function () {
        const isoTime = $(this).attr('datetime');

        $(this).text(moment(isoTime).fromNow());
    });

    $('.relative-time-diff[datetime][data-compare-to]').each(function () {
        const momentA = moment($(this).attr('datetime'));
        const momentB = moment($(this).data('compare-to'));

        const relativeTime = momentA.from(momentB, true);

        const format = momentB.isBefore(momentA)
            ? 'time.later_format'
            : 'time.earlier_format';

        $(this).text(Translator.trans(format, {relative_time: relativeTime}));
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
