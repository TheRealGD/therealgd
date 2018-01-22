'use strict';

import $ from 'jquery';
import Routing from 'fosjsrouting';

$(function () {
    $('.auto-fetch-submission-titles .fetch-title').blur(function () {
        const $receiver = $('.receive-title');
        const url = $(this).val().trim();

        if ($receiver.val().trim() === '' && /^https?:\/\//.test(url)) {
            $.ajax({
                url: Routing.generate('fetch_title'),
                method: 'POST',
                dataType: 'json',
                data: { url: url }
            }).done(data => {
                if ($receiver.val().trim() === '') {
                    $('.receive-title').val(data.title);
                }
            }).fail(err => {
                console && console.log(err);
            });
        }
    });
});
