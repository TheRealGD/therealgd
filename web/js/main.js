(function (app) {
    'use strict';

    app.makeTimesRelative = function () {
        var timestamps = document.getElementsByClassName('relative-time');

        for (var i = 0; i < timestamps.length; i++) {
            var isoTime = timestamps[i].getAttribute('datetime');
            timestamps[i].innerText = moment(isoTime).fromNow();
        }
    };

    addEventListener('DOMContentLoaded', app.makeTimesRelative);
})(window.raddit = window.raddit || {});
