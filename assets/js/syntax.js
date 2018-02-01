'use strict';

import $ from 'jquery';

const languageAliases = {
    'html': 'xml',
    'c': 'cpp',
};

$(function () {
    $('code[class^="language-"]').each(function () {
        const nightMode = $('body').hasClass('night-mode');

        let language = this.className.replace(/.*language-(\S+).*/, '$1');

        if (languageAliases.hasOwnProperty(language)) {
            language = languageAliases[language];
        }

        const theme = nightMode ? 'darkula' : 'tomorrow';

        Promise.all([
            import('highlight.js/lib/highlight'),
            import(`highlight.js/lib/languages/${language}`),
            import(`highlight.js/styles/${theme}.css`)
        ]).then(imports => {
            const [hljs, definition] = imports;

            hljs.registerLanguage(language, definition);
            hljs.highlightBlock(this);
        }).catch(e => {
            console && console.log(e);
        });
    });
});
