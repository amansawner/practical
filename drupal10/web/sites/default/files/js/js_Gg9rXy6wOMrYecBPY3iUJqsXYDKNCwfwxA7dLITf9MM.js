/**
 * @file
 * JavaScript API for the History module, with client-side caching.
 *
 * May only be loaded for authenticated users, with the History module enabled.
 */

(function ($, Drupal, drupalSettings, storage) {
  const currentUserID = parseInt(drupalSettings.user.uid, 10);

  // Any comment that is older than 30 days is automatically considered read,
  // so for these we don't need to perform a request at all!
  const secondsIn30Days = 2592000;
  const thirtyDaysAgo =
    Math.round(new Date().getTime() / 1000) - secondsIn30Days;

  // Use the data embedded in the page, if available.
  let embeddedLastReadTimestamps = false;
  if (drupalSettings.history && drupalSettings.history.lastReadTimestamps) {
    embeddedLastReadTimestamps = drupalSettings.history.lastReadTimestamps;
  }

  /**
   * @namespace
   */
  Drupal.history = {
    /**
     * Fetch "last read" timestamps for the given nodes.
     *
     * @param {Array} nodeIDs
     *   An array of node IDs.
     * @param {function} callback
     *   A callback that is called after the requested timestamps were fetched.
     */
    fetchTimestamps(nodeIDs, callback) {
      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps) {
        callback();
        return;
      }

      $.ajax({
        url: Drupal.url('history/get_node_read_timestamps'),
        type: 'POST',
        data: { 'node_ids[]': nodeIDs },
        dataType: 'json',
        success(results) {
          Object.keys(results || {}).forEach((nodeID) => {
            storage.setItem(
              `Drupal.history.${currentUserID}.${nodeID}`,
              results[nodeID],
            );
          });
          callback();
        },
      });
    },

    /**
     * Get the last read timestamp for the given node.
     *
     * @param {number|string} nodeID
     *   A node ID.
     *
     * @return {number}
     *   A UNIX timestamp.
     */
    getLastRead(nodeID) {
      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps && embeddedLastReadTimestamps[nodeID]) {
        return parseInt(embeddedLastReadTimestamps[nodeID], 10);
      }
      return parseInt(
        storage.getItem(`Drupal.history.${currentUserID}.${nodeID}`) || 0,
        10,
      );
    },

    /**
     * Marks a node as read, store the last read timestamp client-side.
     *
     * @param {number|string} nodeID
     *   A node ID.
     */
    markAsRead(nodeID) {
      $.ajax({
        url: Drupal.url(`history/${nodeID}/read`),
        type: 'POST',
        dataType: 'json',
        success(timestamp) {
          // If the data is embedded in the page, don't store on the client
          // side.
          if (
            embeddedLastReadTimestamps &&
            embeddedLastReadTimestamps[nodeID]
          ) {
            return;
          }

          storage.setItem(
            `Drupal.history.${currentUserID}.${nodeID}`,
            timestamp,
          );
        },
      });
    },

    /**
     * Determines whether a server check is necessary.
     *
     * Any content that is >30 days old never gets a "new" or "updated"
     * indicator. Any content that was published before the oldest known reading
     * also never gets a "new" or "updated" indicator, because it must've been
     * read already.
     *
     * @param {number|string} nodeID
     *   A node ID.
     * @param {number} contentTimestamp
     *   The time at which some content (e.g. a comment) was published.
     *
     * @return {boolean}
     *   Whether a server check is necessary for the given node and its
     *   timestamp.
     */
    needsServerCheck(nodeID, contentTimestamp) {
      // First check if the content is older than 30 days, then we can bail
      // early.
      if (contentTimestamp < thirtyDaysAgo) {
        return false;
      }

      // Use the data embedded in the page, if available.
      if (embeddedLastReadTimestamps && embeddedLastReadTimestamps[nodeID]) {
        return (
          contentTimestamp > parseInt(embeddedLastReadTimestamps[nodeID], 10)
        );
      }

      const minLastReadTimestamp = parseInt(
        storage.getItem(`Drupal.history.${currentUserID}.${nodeID}`) || 0,
        10,
      );
      return contentTimestamp > minLastReadTimestamp;
    },
  };
})(jQuery, Drupal, drupalSettings, window.localStorage);
;
/**
 * @file
 * Marks the nodes listed in drupalSettings.history.nodesToMarkAsRead as read.
 *
 * Uses the History module JavaScript API.
 *
 * @see Drupal.history
 */

(function (window, Drupal, drupalSettings) {
  // When the window's "load" event is triggered, mark all enumerated nodes as
  // read. This still allows for Drupal behaviors (which are triggered on the
  // "DOMContentReady" event) to add "new" and "updated" indicators.
  window.addEventListener('load', () => {
    if (drupalSettings.history && drupalSettings.history.nodesToMarkAsRead) {
      Object.keys(drupalSettings.history.nodesToMarkAsRead).forEach(
        Drupal.history.markAsRead,
      );
    }
  });
})(window, Drupal, drupalSettings);
;
/**
 * @file
 * better_exposed_filters.js
 *
 * Provides some client-side functionality for the Better Exposed Filters module.
 */

(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.betterExposedFilters = {
    attach: function (context, settings) {
      // Add highlight class to checked checkboxes for better theming.
      $('.bef-tree input[type=checkbox], .bef-checkboxes input[type=checkbox]')
        // Highlight newly selected checkboxes.
        .change(function () {
          _bef_highlight(this, context);
        })
        .filter(':checked').closest('.form-item', context).addClass('highlight');
    }
  };

  /*
   * Helper functions
   */

  /**
   * Adds/Removes the highlight class from the form-item div as appropriate.
   */
  function _bef_highlight(elem, context) {
    $elem = $(elem, context);
    $elem.attr('checked')
      ? $elem.closest('.form-item', context).addClass('highlight')
      : $elem.closest('.form-item', context).removeClass('highlight');
  }

})(jQuery, Drupal, drupalSettings);
;
/**
 * @file
 * bef_links_use_ajax.js
 *
 * Allows to use ajax with Bef links.
 */

(function ($, once) {

  // This is only needed to provide ajax functionality
  Drupal.behaviors.better_exposed_filters_select_as_links = {
    attach: function (context, settings) {
      $(once('bef-links-use-ajax', '.bef-links.bef-links-use-ajax', context)).each(function () {
        let $links = $(this);
        let links_name = $(this).attr('name');
        let links_multiple = $(this).attr('multiple');
        let $form = $(this).closest('form');
        let $filters = $form.find('input[name^="' + links_name + '"]');

        $(this).find('a').click(function (event) {
          // Prevent following the link URL.
          event.preventDefault();

          let link_name = links_multiple ? $(this).attr('name') : links_name;
          let link_value = $(this).attr('name').substring(links_name.length).replace(/^\[|\]$/g, '');
          let $filter = $form.find('input[name="' + link_name + '"]');

          if ($(this).hasClass('bef-link--selected')) {
            // The previously selected link is selected again. Deselect it.
            $(this).removeClass('bef-link--selected');
            let all = $links.find('a[name="' + links_name + '[All]"]').addClass('bef-link--selected');
            if (!links_multiple || link_value == 'All') {
              $filters.remove();
            }
            else {
              $filter.remove();
            }
          }
          else {
            if (!links_multiple || link_value == 'All') {
              $links.find('.bef-link--selected').removeClass('bef-link--selected');
            }
            $(this).addClass('bef-link--selected');

            if (!$filter.length) {
              $filter = $('<input type="hidden" name="' + link_name + '" />')
                .prependTo($links);
            }
            $filter.val(link_value);
          }

          // Submit the form.
          $form.find('.form-submit').click();
        });
      });
    }
  };
})(jQuery, once);
;