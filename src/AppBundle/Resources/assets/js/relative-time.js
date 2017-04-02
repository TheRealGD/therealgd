'use strict';

import moment from 'moment';

export default function ($) {
    $('.relative-time[datetime]').each(function () {
        const isoTime = $(this).attr('datetime');

        $(this).text(moment(isoTime).fromNow());
    });
};
