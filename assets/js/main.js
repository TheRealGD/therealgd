'use strict';

// set up some global variables
window.$ = require('jquery');
window.Translator = require('bazinga-translator');

// actually initialise stuff
import './alerts';
import './commenting';
import './delete';
import './dropdowns';
import './fetch_titles';
import './forms';
import './markdown';
import './relative-time';
import './report';
import './select2';
import './submit';
import './subscribe';
import './syntax';
import './vote';

import Vue from 'vue';

import TranslatedText from './components/translatedtext';
import ReportLink from './components/reportlink';
import ModLinks from './components/modlinks';

new Vue({
  el: '#vue-app',
  components: {TranslatedText, ReportLink, ModLinks}
});
