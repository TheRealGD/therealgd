'use strict';

//noinspection JSUnresolvedVariable
__webpack_public_path__ = window.__webpack_public_path__;

import $ from 'jquery';
import relativeTime from './relative-time';
import enableAjaxVoting from './vote';
import { init as initAlerts } from './alerts';
import {
    initWindow as dropdownInitWindow,
    initRoot as dropdownInitRoot
} from './dropdowns';

$(relativeTime);
$(enableAjaxVoting);
$(initAlerts);
$(dropdownInitRoot);
$(dropdownInitWindow);
