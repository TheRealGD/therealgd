'use strict';

import $ from 'jquery';
import moment from 'moment';

export default function (root) {
    root = root || ':root';

    $(root).find('.relative-time[datetime]').each(function () {
        const isoTime = $(this).attr('datetime');

        $(this).text(moment(isoTime).fromNow());
    });
};
