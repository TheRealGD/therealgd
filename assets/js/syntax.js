'use strict';

import $ from 'jquery';

$(function () {
    $('code[class^="language-"]').each(function () {
        const nightMode = $('body').hasClass('night-mode');
        let language = this.className.replace(/.*language-(\S+).*/, "$1");

        if (language === 'html') {
            language = 'xml';
        }

        let theme;

        if (nightMode) {
            theme = import('highlight.js/styles/darkula.css');
        } else {
            theme = import('highlight.js/styles/tomorrow.css');
        }

        Promise.all([
            import('highlight.js/lib/highlight'),
            import(`highlight.js/lib/languages/${language}`),
            theme,
        ]).then(imports => {
            const [hljs, definition] = imports;

            hljs.registerLanguage(language, definition);
            hljs.highlightBlock(this);
        }).catch(e => {
            console && console.log(e);
        });
    });
});
