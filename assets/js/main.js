'use strict';

window.$ = window.jQuery = require('jquery');
window.Translator = require('bazinga-translator');

import $ from 'jquery';
import relativeTime from './relative-time';
import enableAjaxVoting from './vote';
import { init as initAlerts } from './alerts';
import {
    initWindow as dropdownInitWindow,
    initRoot as dropdownInitRoot
} from './dropdowns';
import initCommenting from './commenting';
import fetchTitles from './fetch_titles';
import markdownPreview from './markdown';

$(relativeTime);
$(enableAjaxVoting);
$(initAlerts);
$(dropdownInitRoot);
$(dropdownInitWindow);
$(initCommenting);
$(fetchTitles);
$(markdownPreview);
