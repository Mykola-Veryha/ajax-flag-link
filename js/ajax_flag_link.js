/**
 *
 * @file
 * User popup.
 */
(function ($, Drupal, drupalSettings) {
  'use strict';
  Drupal.behaviors.userPopup = {
    attach: function (context, settings) {
      $('.ln-flag-ajax-link').once('ln-flag-ajax-link').each(function () {
        function validate ($element) {
          if ($element.attr('data-entity-id') === undefined || $element.attr('data-entity-id') === null) {
            return false;
          }
          return !($element.attr('data-flag-id') === undefined || $element.attr('data-flag-id') === null);
        }

        if (validate($(this))) {
          let entity_id = $(this).attr('data-entity-id');
          let link_path = '/ln-flag-ajax-link/' + $(this).attr('data-flag-id') + '/' + entity_id;
          $.ajax({
            url: link_path,
            success: function (response) {
              $('.ln-flag-ajax-link-' + entity_id).html(response);
              Drupal.ajax.bindAjaxLinks('.ln-flag-ajax-link-' + entity_id);
            }
          });
        } else {
          console.log('There is invalid data attributes!');
        }
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
