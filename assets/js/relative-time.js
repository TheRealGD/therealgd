'use strict';

import $ from 'jquery';
import distanceInWords from 'date-fns/distance_in_words';
import distanceInWordsToNow from 'date-fns/distance_in_words_to_now';
import isBefore from 'date-fns/is_before';

function makeTimesRelative(locale) {
    $('.relative-time[datetime]').each(function () {
        const isoTime = $(this).attr('datetime');

        $(this).text(distanceInWordsToNow(isoTime, {
            addSuffix: true,
            locale: locale,
        }));
    });

    $('.relative-time-diff[datetime][data-compare-to]').each(function () {
        const timeA = $(this).attr('datetime');
        const timeB = $(this).data('compare-to');

        const relativeTime = distanceInWords(timeA, timeB, { locale: locale });

        const format = isBefore(timeB, timeA)
            ? 'time.later_format'
            : 'time.earlier_format';

        $(this).text(Translator.trans(format, { relative_time: relativeTime }));
    });
}

function loadLocaleAndMakeTimesRelative(lang) {
    lang = lang.toLowerCase().replace('-', '_');

    import(`date-fns/locale/${lang}`).then(locale => {
        makeTimesRelative(locale);
    }).catch(error => {
        if (lang.indexOf('_') !== -1) {
            const newLang = lang.replace(/_.*/, '');

            console && console.log(`Couldn't load ${lang}; trying ${newLang}`);

            loadLocaleAndMakeTimesRelative(newLang);
        } else {
            console && console.log(error.toString());

            // give up and just do english
            makeTimesRelative();
        }
    });
}

$(function () {
    const locale = $(':root').attr('lang');

    if (!locale || locale === 'en') {
        // english is the default, always-loaded locale
        makeTimesRelative();
    } else {
        loadLocaleAndMakeTimesRelative(locale);
    }
});
