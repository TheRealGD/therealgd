'use strict';

import $ from 'jquery';

$(function () {
    $('#submission_form').submit(function () {
      let url = $('#submission_url').val();
      $('#submission_url').val(encodeURI(url))
      return true;
    });
});
