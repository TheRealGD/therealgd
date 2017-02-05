'use strict';

const $ = require('jquery');

import './relative-time';
import enableAjaxVoting from './vote';

$(() => {
    enableAjaxVoting();
});
