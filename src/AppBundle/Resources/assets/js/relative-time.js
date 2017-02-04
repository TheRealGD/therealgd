'use strict';

import moment from 'moment';

addEventListener('DOMContentLoaded', () => {
    const timestamps = document.getElementsByClassName('relative-time');

    for (let i = 0; i < timestamps.length; i++) {
        const isoTime = timestamps[i].getAttribute('datetime');
        timestamps[i].innerText = moment(isoTime).fromNow();
    }
});
