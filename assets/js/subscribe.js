'use strict';

import $ from 'jquery';
import Router from 'fosjsrouting';

$(() => {
    $(document).on('submit', '.subscribe-form', function (event) {
        const $form = $(this);
        const forum = $form.data('forum');

        if (forum === undefined) {
            console && console.log('Missing data-forum', $form);

            return;
        }

        event.preventDefault();

        const $button = $form.find('.subscribe-button');
        const subscribe = $button.hasClass('subscribe-button--subscribe');

        $button.prop('disabled', true);

        $.ajax({
            url: Router.generate(subscribe ? 'subscribe' : 'unsubscribe', {
                forum_name: forum,
                _format: 'json'
            }),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json'
        }).done(() => {
            const proto = $button.data('toggle-prototype');

            $button
                .removeClass(`subscribe-button--${subscribe ? '' : 'un'}subscribe`)
                .addClass(`subscribe-button--${!subscribe ? '' : 'un'}subscribe`)
                .data('toggle-prototype', $button.html())
                .html(proto);
        }).fail(err => {
            console && console.log('Failed to (un)subscribe', err);
        }).always(() => {
            $button.prop('disabled', false);
        });
    });
});
