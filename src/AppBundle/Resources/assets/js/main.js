'use strict';

const $ = require('jquery');

import relativeTime from './relative-time';
import enableAjaxVoting from './vote';
import { init as initAlerts } from './alerts';

$(relativeTime);
$(enableAjaxVoting);
$(initAlerts);
