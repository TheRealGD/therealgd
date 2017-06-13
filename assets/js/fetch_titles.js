'use strict';

const Routing = require('fosjsrouting');

export default function ($) {
    $('.fetch-title').blur(function () {
        const $receiver = $('.receive-title');
        const url = $(this).val().trim();

        if ($receiver.val().trim() === '' && /^https?:\/\//.test(url)) {
            $.ajax({
                url: Routing.generate('raddit_app_fetch_title'),
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
}
