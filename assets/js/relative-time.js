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
    locale = locale.toLowerCase().replace('_', '-');

    import(`moment/src/locale/${locale}.js`).then(() => {
        moment.locale(locale);

        makeTimesRelative($);
    }).catch(error => {
        if (locale.indexOf('-') !== -1) {
            const newLocale = locale.replace(/-.*/, '');

            if (console) {
                console.log(`Couldn't load ${locale}; trying ${newLocale}`);
            }

            loadLocaleAndMakeTimesRelative($, newLocale);
        } else if (console) {
            console.log(error.toString());
        }
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
