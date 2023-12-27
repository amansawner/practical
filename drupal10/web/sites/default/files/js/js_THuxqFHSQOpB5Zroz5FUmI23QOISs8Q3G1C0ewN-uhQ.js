/**
 * @file
 * JavaScript behaviors for Select2 integration.
 */

(function ($, Drupal, once) {

  'use strict';

  // @see https://select2.github.io/options.html
  Drupal.webform = Drupal.webform || {};
  Drupal.webform.select2 = Drupal.webform.select2 || {};
  Drupal.webform.select2.options = Drupal.webform.select2.options || {};
  Drupal.webform.select2.options.width = Drupal.webform.select2.options.width || '100%';
  Drupal.webform.select2.options.widthInline = Drupal.webform.select2.options.widthInline || '50%';

  /**
   * Initialize Select2 support.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelect2 = {
    attach: function (context) {
      if (!$.fn.select2) {
        return;
      }

      $(once('webform-select2', 'select.js-webform-select2, .js-webform-select2 select', context))
        .each(function () {
          var $select = $(this);

          var options = {};
          if ($select.parents('.webform-element--title-inline').length) {
            options.width = Drupal.webform.select2.options.widthInline;
          }
          options = $.extend(options, Drupal.webform.select2.options);
          if ($select.data('placeholder')) {
            options.placeholder = $select.data('placeholder');
            if (!$select.prop('multiple')) {
              // Allow single option to be deselected.
              options.allowClear = true;
            }
          }
          if ($select.data('limit')) {
            options.maximumSelectionLength = $select.data('limit');
          }

          // Remove required attribute from IE11 which breaks
          // HTML5 clientside validation.
          // @see https://github.com/select2/select2/issues/5114
          if (window.navigator.userAgent.indexOf('Trident/') !== -1
            && $select.attr('multiple')
            && $select.attr('required')) {
            $select.removeAttr('required');
          }

          $select.select2(options);
        });

    }
  };

  /**
   * ISSUE:
   * Hiding/showing element via #states API cause select2 dropdown to appear in the wrong position.
   *
   * WORKAROUND:
   * Close (aka hide) select2 dropdown when #states API hides or shows an element.
   *
   * Steps to reproduce:
   * - Add custom 'Submit button(s)'
   * - Hide submit button
   * - Save
   * - Open 'Submit button(s)' dialog
   *
   * Dropdown body is positioned incorrectly when dropdownParent isn't statically positioned.
   * @see https://github.com/select2/select2/issues/3303
   */
  $(function () {
    if ($.fn.select2) {
      $(document).on('state:visible state:visible-slide', function (e) {
        $('select.select2-hidden-accessible').select2('close');
      });
    }

    // Select2 search broken inside jQuery UI 1.10.x modal Dialog.
    // @see https://github.com/select2/select2/issues/1246
    if ($.ui && $.ui.dialog && $.ui.dialog.prototype._allowInteraction) {
      var ui_dialog_interaction = $.ui.dialog.prototype._allowInteraction;
      $.ui.dialog.prototype._allowInteraction = function (e) {
        if ($(e.target).closest('.select2-dropdown').length) {
          return true;
        }
        return ui_dialog_interaction.apply(this, arguments);
      };
    }
  });

})(jQuery, Drupal,  once);
;
/**
 * @file
 * JavaScript behaviors for select menu.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Disable select menu options using JavaScript.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelectOptionsDisabled = {
    attach: function (context) {
      $(once('webform-select-options-disabled', 'select[data-webform-select-options-disabled]', context)).each(function () {
        var $select = $(this);
        var disabled = $select.attr('data-webform-select-options-disabled').split(/\s*,\s*/);
        $select.find('option').filter(function isDisabled() {
          return ($.inArray(this.value, disabled) !== -1);
        }).attr('disabled', 'disabled');
      });
    }
  };

})(jQuery, Drupal, once);
;
/**
 * @file
 * JavaScript behaviors for other elements.
 */

(function ($, Drupal, once) {

  'use strict';

  /**
   * Toggle other input (text) field.
   *
   * @param {boolean} show
   *   TRUE will display the text field. FALSE with hide and clear the text field.
   * @param {object} $element
   *   The input (text) field to be toggled.
   * @param {string} effect
   *   Effect.
   */
  function toggleOther(show, $element, effect) {
    var $input = $element.find('input');
    var hideEffect = (effect === false) ? 'hide' : 'slideUp';
    var showEffect = (effect === false) ? 'show' : 'slideDown';

    if (show) {
      // Limit the other inputs width to the parent's container.
      // If the parent container is not visible it's width will be 0
      // and ignored.
      var width = $element.parent().width();
      if (width) {
        $element.width(width);
      }

      // Display the element.
      $element[showEffect]();
      // If not initializing, then focus the other element.
      if (effect !== false) {
        $input.trigger('focus');
      }
      // Require the input.
      $input.prop('required', true).attr('aria-required', 'true');
      // Restore the input's value.
      var value = $input.data('webform-value');
      if (typeof value !== 'undefined') {
        $input.val(value);
        var input = $input.get(0);
        // Move cursor to the beginning of the other text input.
        // @see https://stackoverflow.com/questions/21177489/selectionstart-selectionend-on-input-type-number-no-longer-allowed-in-chrome
        if ($.inArray(input.type, ['text', 'search', 'url', 'tel', 'password']) !== -1) {
          input.setSelectionRange(0, 0);
        }
      }
      // Refresh CodeMirror used as other element.
      $element.parent().find('.CodeMirror').each(function (index, $element) {
        $element.CodeMirror.refresh();
      });
    }
    else {
      // Hide the element.
      $element[hideEffect]();
      // Save the input's value.
      if ($input.val() !== '') {
        $input.data('webform-value', $input.val());
      }
      // Empty and un-required the input.
      $input.val('').prop('required', false).removeAttr('aria-required');
    }
  }

  /**
   * Attach handlers to select other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformSelectOther = {
    attach: function (context) {
      $(once('webform-select-other', '.js-webform-select-other', context)).each(function () {
        var $element = $(this);

        var $select = $element.find('select');
        var $input = $element.find('.js-webform-select-other-input');

        $select.on('change', function () {
          var isOtherSelected = $select
            .find('option[value="_other_"]')
            .is(':selected');
          toggleOther(isOtherSelected, $input);
        });

        var isOtherSelected = $select
          .find('option[value="_other_"]')
          .is(':selected');
        toggleOther(isOtherSelected, $input, false);
      });
    }
  };

  /**
   * Attach handlers to checkboxes other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformCheckboxesOther = {
    attach: function (context) {
      $(once('webform-checkboxes-other', '.js-webform-checkboxes-other', context)).each(function () {
        var $element = $(this);
        var $checkbox = $element.find('input[value="_other_"]');
        var $input = $element.find('.js-webform-checkboxes-other-input');

        $checkbox.on('change', function () {
          toggleOther(this.checked, $input);
        });

        toggleOther($checkbox.is(':checked'), $input, false);
      });
    }
  };

  /**
   * Attach handlers to radios other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformRadiosOther = {
    attach: function (context) {
      $(once('webform-radios-other', '.js-webform-radios-other', context)).each(function () {
        var $element = $(this);

        var $radios = $element.find('input[type="radio"]');
        var $input = $element.find('.js-webform-radios-other-input');

        $radios.on('change', function () {
          toggleOther(($radios.filter(':checked').val() === '_other_'), $input);
        });

        toggleOther(($radios.filter(':checked').val() === '_other_'), $input, false);
      });
    }
  };

  /**
   * Attach handlers to buttons other elements.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.webformButtonsOther = {
    attach: function (context) {
      $(once('webform-buttons-other', '.js-webform-buttons-other', context)).each(function () {
        var $element = $(this);

        var $buttons = $element.find('input[type="radio"]');
        var $input = $element.find('.js-webform-buttons-other-input');
        var $container = $(this).find('.js-webform-webform-buttons');

        // Create set onchange handler.
        $container.on('change', function () {
          toggleOther(($(this).find(':radio:checked').val() === '_other_'), $input);
        });

        toggleOther(($buttons.filter(':checked').val() === '_other_'), $input, false);
      });
    }
  };

})(jQuery, Drupal, once);
;
/**
 * @file
 * Attaches behavior for the Filter module.
 */

(function ($, Drupal) {
  /**
   * Displays the guidelines of the selected text format automatically.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches behavior for updating filter guidelines.
   */
  Drupal.behaviors.filterGuidelines = {
    attach(context) {
      function updateFilterGuidelines(event) {
        const $this = $(event.target);
        const { value } = event.target;
        $this
          .closest('.js-filter-wrapper')
          .find('[data-drupal-format-id]')
          .hide()
          .filter(`[data-drupal-format-id="${value}"]`)
          .show();
      }

      $(once('filter-guidelines', '.js-filter-guidelines', context))
        .find(':header')
        .hide()
        .closest('.js-filter-wrapper')
        .find('select.js-filter-list')
        .on('change.filterGuidelines', updateFilterGuidelines)
        // Need to trigger the namespaced event to avoid triggering formUpdated
        // when initializing the select.
        .trigger('change.filterGuidelines');
    },
  };
})(jQuery, Drupal);
;
/**
 * @file
 * Manages elements that can offset the size of the viewport.
 *
 * Measures and reports viewport offset dimensions from elements like the
 * toolbar that can potentially displace the positioning of other elements.
 */

/**
 * @typedef {object} Drupal~displaceOffset
 *
 * @prop {number} top
 * @prop {number} left
 * @prop {number} right
 * @prop {number} bottom
 */

/**
 * Triggers when layout of the page changes.
 *
 * This is used to position fixed element on the page during page resize and
 * Toolbar toggling.
 *
 * @event drupalViewportOffsetChange
 */
(function ($, Drupal, debounce) {
  /**
   *
   * @type {Drupal~displaceOffset}
   */
  const cache = {
    right: 0,
    left: 0,
    bottom: 0,
    top: 0,
  };
  /**
   * The prefix used for the css custom variable name.
   *
   * @type {string}
   */
  const cssVarPrefix = '--drupal-displace-offset';
  const documentStyle = document.documentElement.style;
  const offsetKeys = Object.keys(cache);
  /**
   * The object with accessors that update the CSS variable on value update.
   *
   * @type {Drupal~displaceOffset}
   */
  const offsetProps = {};
  offsetKeys.forEach((edge) => {
    offsetProps[edge] = {
      // Show this property when using Object.keys().
      enumerable: true,
      get() {
        return cache[edge];
      },
      set(value) {
        // Only update the CSS custom variable when the value changed.
        if (value !== cache[edge]) {
          documentStyle.setProperty(`${cssVarPrefix}-${edge}`, `${value}px`);
        }
        cache[edge] = value;
      },
    };
  });

  /**
   * Current value of the size of margins on the page.
   *
   * This property is read-only and the object is sealed to prevent key name
   * modifications since key names are used to dynamically construct CSS custom
   * variable names.
   *
   * @name Drupal.displace.offsets
   *
   * @type {Drupal~displaceOffset}
   */
  const offsets = Object.seal(Object.defineProperties({}, offsetProps));

  /**
   * Calculates displacement for element based on its dimensions and placement.
   *
   * @param {HTMLElement} el
   *   The element whose dimensions and placement will be measured.
   *
   * @param {string} edge
   *   The name of the edge of the viewport that the element is associated
   *   with.
   *
   * @return {number}
   *   The viewport displacement distance for the requested edge.
   */
  function getRawOffset(el, edge) {
    const $el = $(el);
    const documentElement = document.documentElement;
    let displacement = 0;
    const horizontal = edge === 'left' || edge === 'right';
    // Get the offset of the element itself.
    let placement = $el.offset()[horizontal ? 'left' : 'top'];
    // Subtract scroll distance from placement to get the distance
    // to the edge of the viewport.
    placement -=
      window[`scroll${horizontal ? 'X' : 'Y'}`] ||
      document.documentElement[`scroll${horizontal ? 'Left' : 'Top'}`] ||
      0;
    // Find the displacement value according to the edge.
    switch (edge) {
      // Left and top elements displace as a sum of their own offset value
      // plus their size.
      case 'top':
        // Total displacement is the sum of the elements placement and size.
        displacement = placement + $el.outerHeight();
        break;

      case 'left':
        // Total displacement is the sum of the elements placement and size.
        displacement = placement + $el.outerWidth();
        break;

      // Right and bottom elements displace according to their left and
      // top offset. Their size isn't important.
      case 'bottom':
        displacement = documentElement.clientHeight - placement;
        break;

      case 'right':
        displacement = documentElement.clientWidth - placement;
        break;

      default:
        displacement = 0;
    }
    return displacement;
  }

  /**
   * Gets a specific edge's offset.
   *
   * Any element with the attribute data-offset-{edge} e.g. data-offset-top will
   * be considered in the viewport offset calculations. If the attribute has a
   * numeric value, that value will be used. If no value is provided, one will
   * be calculated using the element's dimensions and placement.
   *
   * @function Drupal.displace.calculateOffset
   *
   * @param {string} edge
   *   The name of the edge to calculate. Can be 'top', 'right',
   *   'bottom' or 'left'.
   *
   * @return {number}
   *   The viewport displacement distance for the requested edge.
   */
  function calculateOffset(edge) {
    let edgeOffset = 0;
    const displacingElements = document.querySelectorAll(
      `[data-offset-${edge}]`,
    );
    const n = displacingElements.length;
    for (let i = 0; i < n; i++) {
      const el = displacingElements[i];
      // If the element is not visible, do consider its dimensions.
      if (el.style.display === 'none') {
        continue;
      }
      // If the offset data attribute contains a displacing value, use it.
      let displacement = parseInt(el.getAttribute(`data-offset-${edge}`), 10);
      // If the element's offset data attribute exits
      // but is not a valid number then get the displacement
      // dimensions directly from the element.
      // eslint-disable-next-line no-restricted-globals
      if (isNaN(displacement)) {
        displacement = getRawOffset(el, edge);
      }
      // If the displacement value is larger than the current value for this
      // edge, use the displacement value.
      edgeOffset = Math.max(edgeOffset, displacement);
    }

    return edgeOffset;
  }

  /**
   * Informs listeners of the current offset dimensions.
   *
   * Corresponding CSS custom variables are also updated.
   * Corresponding CSS custom variables names are:
   *  - `--drupal-displace-offset-top`
   *  - `--drupal-displace-offset-right`
   *  - `--drupal-displace-offset-bottom`
   *  - `--drupal-displace-offset-left`
   *
   * @function Drupal.displace
   *
   * @prop {Drupal~displaceOffset} offsets
   *
   * @param {boolean} [broadcast=true]
   *   When true, causes the recalculated offsets values to be
   *   broadcast to listeners. If none is given, defaults to true.
   *
   * @return {Drupal~displaceOffset}
   *   An object whose keys are the for sides an element -- top, right, bottom
   *   and left. The value of each key is the viewport displacement distance for
   *   that edge.
   *
   * @fires event:drupalViewportOffsetChange
   */
  function displace(broadcast = true) {
    const newOffsets = {};
    // Getting the offset and setting the offset needs to be separated because
    // of performance concerns. Only do DOM/style reading happening here.
    offsetKeys.forEach((edge) => {
      newOffsets[edge] = calculateOffset(edge);
    });
    // Once we have all the values, write to the DOM/style.
    offsetKeys.forEach((edge) => {
      // Updating the value in place also update Drupal.displace.offsets.
      offsets[edge] = newOffsets[edge];
    });

    if (broadcast) {
      $(document).trigger('drupalViewportOffsetChange', offsets);
    }
    return offsets;
  }

  /**
   * Registers a resize handler on the window.
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.drupalDisplace = {
    attach() {
      // Mark this behavior as processed on the first pass.
      if (this.displaceProcessed) {
        return;
      }
      this.displaceProcessed = true;
      $(window).on('resize.drupalDisplace', debounce(displace, 200));
    },
  };

  /**
   * Assign the displace function to a property of the Drupal global object.
   *
   * @ignore
   */
  Drupal.displace = displace;

  /**
   * Expose offsets to other scripts to avoid having to recalculate offsets.
   *
   * @ignore
   */
  Object.defineProperty(Drupal.displace, 'offsets', {
    value: offsets,
    // Make sure other scripts don't replace this object.
    writable: false,
  });

  /**
   * Expose method to compute a single edge offsets.
   *
   * @ignore
   */
  Drupal.displace.calculateOffset = calculateOffset;
})(jQuery, Drupal, Drupal.debounce);
;
/**
 * @file
 * Defines a backwards-compatible shim for the jQuery UI :tabbable selector.
 */

(($, Drupal, { isTabbable }) => {
  $.extend($.expr[':'], {
    tabbable(element) {
      Drupal.deprecationError({
        message:
          'The :tabbable selector is deprecated in Drupal 9.2.0 and will be removed in Drupal 11.0.0. Use the core/tabbable library instead. See https://www.drupal.org/node/3183730',
      });

      // The tabbable library considers the summary element tabbable, and also
      // considers a details element without a summary tabbable. The jQuery UI
      // :tabbable selector does not. This is due to those element types being
      // inert in IE/Edge.
      // @see https://allyjs.io/data-tables/focusable.html
      if (element.tagName === 'SUMMARY' || element.tagName === 'DETAILS') {
        const tabIndex = element.getAttribute('tabIndex');
        if (tabIndex === null || tabIndex < 0) {
          return false;
        }
      }
      return isTabbable(element);
    },
  });
})(jQuery, Drupal, window.tabbable);
;
/**
 * @file
 * A modified version of jQuery UI position.
 *
 * Per jQuery UI's public domain license, it is permissible to run modified
 * versions of their code. This file offers the same functionality as what is
 * provided by jQuery UI position, but refactored to meet Drupal coding
 * standards, and restructured so it extends jQuery core instead of jQuery UI.
 *
 * This is provided to support pre-existing code that expects the jQuery
 * position API.
 *
 * @see https://github.com/jquery/jquery-ui/blob/1.12.1/LICENSE.txt
 * @see https://raw.githubusercontent.com/jquery/jquery-ui/1.12.1/ui/position.js
 */

/**
 * This provides ported version of jQuery UI position, refactored to not depend
 * on jQuery UI and to meet Drupal JavaScript coding standards. Functionality
 * and usage is identical. It positions an element relative to another. The
 * `position()` function can be called by any jQuery object. Additional details
 * on using `position()` are provided in this file in the docblock for
 * $.fn.position.
 */
(($) => {
  let cachedScrollbarWidth = null;
  const { max, abs } = Math;
  const regexHorizontal = /left|center|right/;
  const regexVertical = /top|center|bottom/;
  const regexOffset = /[+-]\d+(\.[\d]+)?%?/;
  const regexPosition = /^\w+/;
  const regexPercent = /%$/;
  const _position = $.fn.position;

  function getOffsets(offsets, width, height) {
    return [
      parseFloat(offsets[0]) *
        (regexPercent.test(offsets[0]) ? width / 100 : 1),
      parseFloat(offsets[1]) *
        (regexPercent.test(offsets[1]) ? height / 100 : 1),
    ];
  }

  function parseCss(element, property) {
    return parseInt($.css(element, property), 10) || 0;
  }

  function getDimensions(elem) {
    const raw = elem[0];
    if (raw.nodeType === 9) {
      return {
        width: elem.width(),
        height: elem.height(),
        offset: { top: 0, left: 0 },
      };
    }
    if ($.isWindow(raw)) {
      return {
        width: elem.width(),
        height: elem.height(),
        offset: { top: elem.scrollTop(), left: elem.scrollLeft() },
      };
    }
    if (raw.preventDefault) {
      return {
        width: 0,
        height: 0,
        offset: { top: raw.pageY, left: raw.pageX },
      };
    }
    return {
      width: elem.outerWidth(),
      height: elem.outerHeight(),
      offset: elem.offset(),
    };
  }

  const collisions = {
    fit: {
      left(position, data) {
        const { within } = data;
        const withinOffset = within.isWindow
          ? within.scrollLeft
          : within.offset.left;
        const outerWidth = within.width;
        const collisionPosLeft =
          position.left - data.collisionPosition.marginLeft;
        const overLeft = withinOffset - collisionPosLeft;
        const overRight =
          collisionPosLeft + data.collisionWidth - outerWidth - withinOffset;
        let newOverRight;

        // Element is wider than within
        if (data.collisionWidth > outerWidth) {
          // Element is initially over the left side of within
          if (overLeft > 0 && overRight <= 0) {
            newOverRight =
              position.left +
              overLeft +
              data.collisionWidth -
              outerWidth -
              withinOffset;
            position.left += overLeft - newOverRight;

            // Element is initially over right side of within
          } else if (overRight > 0 && overLeft <= 0) {
            position.left = withinOffset;

            // Element is initially over both left and right sides of within
          } else if (overLeft > overRight) {
            position.left = withinOffset + outerWidth - data.collisionWidth;
          } else {
            position.left = withinOffset;
          }

          // Too far left -> align with left edge
        } else if (overLeft > 0) {
          position.left += overLeft;

          // Too far right -> align with right edge
        } else if (overRight > 0) {
          position.left -= overRight;

          // Adjust based on position and margin
        } else {
          position.left = max(position.left - collisionPosLeft, position.left);
        }
      },
      top(position, data) {
        const { within } = data;
        const withinOffset = within.isWindow
          ? within.scrollTop
          : within.offset.top;
        const outerHeight = data.within.height;
        const collisionPosTop = position.top - data.collisionPosition.marginTop;
        const overTop = withinOffset - collisionPosTop;
        const overBottom =
          collisionPosTop + data.collisionHeight - outerHeight - withinOffset;
        let newOverBottom;

        // Element is taller than within
        if (data.collisionHeight > outerHeight) {
          // Element is initially over the top of within
          if (overTop > 0 && overBottom <= 0) {
            newOverBottom =
              position.top +
              overTop +
              data.collisionHeight -
              outerHeight -
              withinOffset;
            position.top += overTop - newOverBottom;

            // Element is initially over bottom of within
          } else if (overBottom > 0 && overTop <= 0) {
            position.top = withinOffset;

            // Element is initially over both top and bottom of within
          } else if (overTop > overBottom) {
            position.top = withinOffset + outerHeight - data.collisionHeight;
          } else {
            position.top = withinOffset;
          }

          // Too far up -> align with top
        } else if (overTop > 0) {
          position.top += overTop;

          // Too far down -> align with bottom edge
        } else if (overBottom > 0) {
          position.top -= overBottom;

          // Adjust based on position and margin
        } else {
          position.top = max(position.top - collisionPosTop, position.top);
        }
      },
    },
    flip: {
      left(position, data) {
        const { within } = data;
        const withinOffset = within.offset.left + within.scrollLeft;
        const outerWidth = within.width;
        const offsetLeft = within.isWindow
          ? within.scrollLeft
          : within.offset.left;
        const collisionPosLeft =
          position.left - data.collisionPosition.marginLeft;
        const overLeft = collisionPosLeft - offsetLeft;
        const overRight =
          collisionPosLeft + data.collisionWidth - outerWidth - offsetLeft;
        const myOffset =
          // eslint-disable-next-line no-nested-ternary
          data.my[0] === 'left'
            ? -data.elemWidth
            : data.my[0] === 'right'
            ? data.elemWidth
            : 0;
        const atOffset =
          // eslint-disable-next-line no-nested-ternary
          data.at[0] === 'left'
            ? data.targetWidth
            : data.at[0] === 'right'
            ? -data.targetWidth
            : 0;
        const offset = -2 * data.offset[0];
        let newOverRight;
        let newOverLeft;

        if (overLeft < 0) {
          newOverRight =
            position.left +
            myOffset +
            atOffset +
            offset +
            data.collisionWidth -
            outerWidth -
            withinOffset;
          if (newOverRight < 0 || newOverRight < abs(overLeft)) {
            position.left += myOffset + atOffset + offset;
          }
        } else if (overRight > 0) {
          newOverLeft =
            position.left -
            data.collisionPosition.marginLeft +
            myOffset +
            atOffset +
            offset -
            offsetLeft;
          if (newOverLeft > 0 || abs(newOverLeft) < overRight) {
            position.left += myOffset + atOffset + offset;
          }
        }
      },
      top(position, data) {
        const { within } = data;
        const withinOffset = within.offset.top + within.scrollTop;
        const outerHeight = within.height;
        const offsetTop = within.isWindow
          ? within.scrollTop
          : within.offset.top;
        const collisionPosTop = position.top - data.collisionPosition.marginTop;
        const overTop = collisionPosTop - offsetTop;
        const overBottom =
          collisionPosTop + data.collisionHeight - outerHeight - offsetTop;
        const top = data.my[1] === 'top';
        // eslint-disable-next-line no-nested-ternary
        const myOffset = top
          ? -data.elemHeight
          : data.my[1] === 'bottom'
          ? data.elemHeight
          : 0;
        const atOffset =
          // eslint-disable-next-line no-nested-ternary
          data.at[1] === 'top'
            ? data.targetHeight
            : data.at[1] === 'bottom'
            ? -data.targetHeight
            : 0;
        const offset = -2 * data.offset[1];
        let newOverTop;
        let newOverBottom;
        if (overTop < 0) {
          newOverBottom =
            position.top +
            myOffset +
            atOffset +
            offset +
            data.collisionHeight -
            outerHeight -
            withinOffset;
          if (newOverBottom < 0 || newOverBottom < abs(overTop)) {
            position.top += myOffset + atOffset + offset;
          }
        } else if (overBottom > 0) {
          newOverTop =
            position.top -
            data.collisionPosition.marginTop +
            myOffset +
            atOffset +
            offset -
            offsetTop;
          if (newOverTop > 0 || abs(newOverTop) < overBottom) {
            position.top += myOffset + atOffset + offset;
          }
        }
      },
    },
    flipfit: {
      left(...args) {
        collisions.flip.left.apply(this, args);
        collisions.fit.left.apply(this, args);
      },
      top(...args) {
        collisions.flip.top.apply(this, args);
        collisions.fit.top.apply(this, args);
      },
    },
  };

  $.position = {
    scrollbarWidth() {
      if (cachedScrollbarWidth !== undefined) {
        return cachedScrollbarWidth;
      }
      const div = $(
        '<div ' +
          "style='display:block;position:absolute;width:50px;height:50px;overflow:hidden;'>" +
          "<div style='height:100px;width:auto;'></div></div>",
      );
      const innerDiv = div.children()[0];

      $('body').append(div);
      const w1 = innerDiv.offsetWidth;
      div.css('overflow', 'scroll');

      let w2 = innerDiv.offsetWidth;

      if (w1 === w2) {
        w2 = div[0].clientWidth;
      }

      div.remove();
      cachedScrollbarWidth = w1 - w2;
      return cachedScrollbarWidth;
    },
    getScrollInfo(within) {
      const overflowX =
        within.isWindow || within.isDocument
          ? ''
          : within.element.css('overflow-x');
      const overflowY =
        within.isWindow || within.isDocument
          ? ''
          : within.element.css('overflow-y');
      const hasOverflowX =
        overflowX === 'scroll' ||
        (overflowX === 'auto' && within.width < within.element[0].scrollWidth);
      const hasOverflowY =
        overflowY === 'scroll' ||
        (overflowY === 'auto' &&
          within.height < within.element[0].scrollHeight);
      return {
        width: hasOverflowY ? $.position.scrollbarWidth() : 0,
        height: hasOverflowX ? $.position.scrollbarWidth() : 0,
      };
    },
    getWithinInfo(element) {
      const withinElement = $(element || window);
      const isWindow = $.isWindow(withinElement[0]);
      const isDocument = !!withinElement[0] && withinElement[0].nodeType === 9;
      const hasOffset = !isWindow && !isDocument;
      return {
        element: withinElement,
        isWindow,
        isDocument,
        offset: hasOffset ? $(element).offset() : { left: 0, top: 0 },
        scrollLeft: withinElement.scrollLeft(),
        scrollTop: withinElement.scrollTop(),
        width: withinElement.outerWidth(),
        height: withinElement.outerHeight(),
      };
    },
  };

  // eslint-disable-next-line func-names
  /**
   * Positions an element relative to another.
   *
   * The following documentation is originally from
   * {@link https://api.jqueryui.com/position/}.
   *
   * @param {Object} options - the options object.
   * @param {string} options.my - Defines which position on the element being
   *   positioned to align with the target element: "horizontal vertical"
   *   alignment. A single value such as "right" will be normalized to "right
   *   center", "top" will be normalized to "center top" (following CSS
   *   convention). Acceptable horizontal values: "left", "center", "right".
   *   Acceptable vertical values: "top", "center", "bottom". Example: "left
   *   top" or "center center". Each dimension can also contain offsets, in
   *   pixels or percent, e.g., "right+10 top-25%". Percentage offsets are
   *   relative to the element being positioned. Default value is "center".
   * @param {string} options.at - Defines which position on the target element
   *   to align the positioned element against: "horizontal vertical" alignment.
   *   See the `my` option for full details on possible values. Percentage
   *   offsets are relative to the target element. Default value is "center".
   * @param {string|Element|jQuery|Event|null} options.of - Which element to
   *   position against. If you provide a selector or jQuery object, the first
   *   matching element will be used. If you provide an event object, the pageX
   *   and pageY properties will be used. Example: "#top-menu". Default value is
   *   null.
   * @param {string} options.collision - When the positioned element overflows
   *   the window in some direction, move it to an alternative position. Similar
   *   to `my` and `at`, this accepts a single value or a pair for
   *   horizontal/vertical, e.g., "flip", "fit", "fit flip", "fit none". Default
   *   value is "flip". The options work as follows:
   *   - "flip": Flips the element to the opposite side of the target and the
   *     collision detection is run again to see if it will fit. Whichever side
   *     allows more of the element to be visible will be used.
   *   - "fit": Shift the element away from the edge of the window.
   *   - "flipfit": First applies the flip logic, placing the element on
   *     whichever side allows more of the element to be visible. Then the fit
   *     logic is applied to ensure as much of the element is visible as
   *     possible.
   *     "none": Does not apply any collision detection.
   * @param {function|null} options.using - When specified, the actual property
   *   setting is delegated to this callback. Receives two parameters: The first
   *   is a hash of top and left values for the position that should be set and
   *   can be forwarded to .css() or .animate().The second provides feedback
   *   about the position and dimensions of both elements, as well as
   *   calculations to their relative position. Both target and element have
   *   these properties: element, left, top, width, height. In addition, there's
   *   horizontal, vertical and important, providing twelve potential directions
   *   like { horizontal: "center", vertical: "left", important: "horizontal" }.
   *   Default value is null.
   * @param {string|Element|jQuery} options.within - Element to position within,
   *   affecting collision detection. If you provide a selector or jQuery
   *   object, the first matching element will be used. Default value is window.
   *
   * @return {jQuery}
   *  The jQuery object that called called this function.
   */
  $.fn.position = function (options) {
    if (!options || !options.of) {
      // eslint-disable-next-line prefer-rest-params
      return _position.apply(this, arguments);
    }

    // Make a copy, we don't want to modify arguments
    options = $.extend({}, options);

    const within = $.position.getWithinInfo(options.within);
    const scrollInfo = $.position.getScrollInfo(within);
    const collision = (options.collision || 'flip').split(' ');
    const offsets = {};

    // Make sure string options are treated as CSS selectors
    const target =
      typeof options.of === 'string'
        ? $(document).find(options.of)
        : $(options.of);
    const dimensions = getDimensions(target);
    const targetWidth = dimensions.width;
    const targetHeight = dimensions.height;
    const targetOffset = dimensions.offset;

    if (target[0].preventDefault) {
      // Force left top to allow flipping
      options.at = 'left top';
    }

    // Clone to reuse original targetOffset later
    const basePosition = $.extend({}, targetOffset);

    // Force my and at to have valid horizontal and vertical positions
    // if a value is missing or invalid, it will be converted to center
    // eslint-disable-next-line func-names
    $.each(['my', 'at'], function () {
      let pos = (options[this] || '').split(' ');

      if (pos.length === 1) {
        // eslint-disable-next-line no-nested-ternary
        pos = regexHorizontal.test(pos[0])
          ? pos.concat(['center'])
          : regexVertical.test(pos[0])
          ? ['center'].concat(pos)
          : ['center', 'center'];
      }
      pos[0] = regexHorizontal.test(pos[0]) ? pos[0] : 'center';
      pos[1] = regexVertical.test(pos[1]) ? pos[1] : 'center';

      // Calculate offsets
      const horizontalOffset = regexOffset.exec(pos[0]);
      const verticalOffset = regexOffset.exec(pos[1]);
      offsets[this] = [
        horizontalOffset ? horizontalOffset[0] : 0,
        verticalOffset ? verticalOffset[0] : 0,
      ];

      // Reduce to just the positions without the offsets
      options[this] = [
        regexPosition.exec(pos[0])[0],
        regexPosition.exec(pos[1])[0],
      ];
    });

    // Normalize collision option
    if (collision.length === 1) {
      // eslint-disable-next-line prefer-destructuring
      collision[1] = collision[0];
    }

    if (options.at[0] === 'right') {
      basePosition.left += targetWidth;
    } else if (options.at[0] === 'center') {
      basePosition.left += targetWidth / 2;
    }

    if (options.at[1] === 'bottom') {
      basePosition.top += targetHeight;
    } else if (options.at[1] === 'center') {
      basePosition.top += targetHeight / 2;
    }

    const atOffset = getOffsets(offsets.at, targetWidth, targetHeight);
    basePosition.left += atOffset[0];
    basePosition.top += atOffset[1];

    // eslint-disable-next-line func-names
    return this.each(function () {
      let using;
      const elem = $(this);
      const elemWidth = elem.outerWidth();
      const elemHeight = elem.outerHeight();
      const marginLeft = parseCss(this, 'marginLeft');
      const marginTop = parseCss(this, 'marginTop');
      const collisionWidth =
        elemWidth +
        marginLeft +
        parseCss(this, 'marginRight') +
        scrollInfo.width;
      const collisionHeight =
        elemHeight +
        marginTop +
        parseCss(this, 'marginBottom') +
        scrollInfo.height;
      const position = $.extend({}, basePosition);
      const myOffset = getOffsets(
        offsets.my,
        elem.outerWidth(),
        elem.outerHeight(),
      );

      if (options.my[0] === 'right') {
        position.left -= elemWidth;
      } else if (options.my[0] === 'center') {
        position.left -= elemWidth / 2;
      }

      if (options.my[1] === 'bottom') {
        position.top -= elemHeight;
      } else if (options.my[1] === 'center') {
        position.top -= elemHeight / 2;
      }

      position.left += myOffset[0];
      position.top += myOffset[1];

      const collisionPosition = {
        marginLeft,
        marginTop,
      };

      // eslint-disable-next-line func-names
      $.each(['left', 'top'], function (i, dir) {
        if (collisions[collision[i]]) {
          collisions[collision[i]][dir](position, {
            targetWidth,
            targetHeight,
            elemWidth,
            elemHeight,
            collisionPosition,
            collisionWidth,
            collisionHeight,
            offset: [atOffset[0] + myOffset[0], atOffset[1] + myOffset[1]],
            my: options.my,
            at: options.at,
            within,
            elem,
          });
        }
      });

      if (options.using) {
        // Adds feedback as second argument to using callback, if present
        // eslint-disable-next-line func-names
        using = function (props) {
          const left = targetOffset.left - position.left;
          const right = left + targetWidth - elemWidth;
          const top = targetOffset.top - position.top;
          const bottom = top + targetHeight - elemHeight;
          const feedback = {
            target: {
              element: target,
              left: targetOffset.left,
              top: targetOffset.top,
              width: targetWidth,
              height: targetHeight,
            },
            element: {
              element: elem,
              left: position.left,
              top: position.top,
              width: elemWidth,
              height: elemHeight,
            },
            // eslint-disable-next-line no-nested-ternary
            horizontal: right < 0 ? 'left' : left > 0 ? 'right' : 'center',
            // eslint-disable-next-line no-nested-ternary
            vertical: bottom < 0 ? 'top' : top > 0 ? 'bottom' : 'middle',
          };
          if (targetWidth < elemWidth && abs(left + right) < targetWidth) {
            feedback.horizontal = 'center';
          }
          if (targetHeight < elemHeight && abs(top + bottom) < targetHeight) {
            feedback.vertical = 'middle';
          }
          if (max(abs(left), abs(right)) > max(abs(top), abs(bottom))) {
            feedback.important = 'horizontal';
          } else {
            feedback.important = 'vertical';
          }
          options.using.call(this, props, feedback);
        };
      }

      elem.offset($.extend(position, { using }));
    });
  };

  // Although $.ui.position is not built to be called directly, some legacy code
  // may have checks for the presence of $.ui.position, which can be used to
  // confirm the presence of jQuery UI position's API, as opposed to the more
  // limited version provided by jQuery.
  if (!$.hasOwnProperty('ui')) {
    $.ui = {};
  }
  $.ui.position = collisions;
})(jQuery);
;
/**
 * @file
 * Dialog API inspired by HTML5 dialog element.
 *
 * @see http://www.whatwg.org/specs/web-apps/current-work/multipage/commands.html#the-dialog-element
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Default dialog options.
   *
   * @type {object}
   *
   * @prop {boolean} [autoOpen=true]
   * @prop {string} [dialogClass='']
   * @prop {string} [buttonClass='button']
   * @prop {string} [buttonPrimaryClass='button--primary']
   * @prop {function} close
   */
  drupalSettings.dialog = {
    autoOpen: true,
    dialogClass: '',
    // Drupal-specific extensions: see dialog.jquery-ui.js.
    buttonClass: 'button',
    buttonPrimaryClass: 'button--primary',
    // When using this API directly (when generating dialogs on the client
    // side), you may want to override this method and do
    // `jQuery(event.target).remove()` as well, to remove the dialog on
    // closing.
    close(event) {
      Drupal.dialog(event.target).close();
      Drupal.detachBehaviors(event.target, null, 'unload');
    },
  };

  /**
   * @typedef {object} Drupal.dialog~dialogDefinition
   *
   * @prop {boolean} open
   *   Is the dialog open or not.
   * @prop {*} returnValue
   *   Return value of the dialog.
   * @prop {function} show
   *   Method to display the dialog on the page.
   * @prop {function} showModal
   *   Method to display the dialog as a modal on the page.
   * @prop {function} close
   *   Method to hide the dialog from the page.
   */

  /**
   * Polyfill HTML5 dialog element with jQueryUI.
   *
   * @param {HTMLElement} element
   *   The element that holds the dialog.
   * @param {object} options
   *   jQuery UI options to be passed to the dialog.
   *
   * @return {Drupal.dialog~dialogDefinition}
   *   The dialog instance.
   */
  Drupal.dialog = function (element, options) {
    let undef;
    const $element = $(element);
    const dialog = {
      open: false,
      returnValue: undef,
    };

    function openDialog(settings) {
      settings = $.extend({}, drupalSettings.dialog, options, settings);
      // Trigger a global event to allow scripts to bind events to the dialog.
      $(window).trigger('dialog:beforecreate', [dialog, $element, settings]);
      $element.dialog(settings);
      dialog.open = true;
      $(window).trigger('dialog:aftercreate', [dialog, $element, settings]);
    }

    function closeDialog(value) {
      $(window).trigger('dialog:beforeclose', [dialog, $element]);
      $element.dialog('close');
      dialog.returnValue = value;
      dialog.open = false;
      $(window).trigger('dialog:afterclose', [dialog, $element]);
    }

    dialog.show = () => {
      openDialog({ modal: false });
    };
    dialog.showModal = () => {
      openDialog({ modal: true });
    };
    dialog.close = closeDialog;

    return dialog;
  };
})(jQuery, Drupal, drupalSettings);
;
/**
 * @file
 * Positioning extensions for dialogs.
 */

/**
 * Triggers when content inside a dialog changes.
 *
 * @event dialogContentResize
 */

(function ($, Drupal, drupalSettings, debounce, displace) {
  // autoResize option will turn off resizable and draggable.
  drupalSettings.dialog = $.extend(
    { autoResize: true, maxHeight: '95%' },
    drupalSettings.dialog,
  );

  /**
   * Position the dialog's center at the center of displace.offsets boundaries.
   *
   * @function Drupal.dialog~resetPosition
   *
   * @param {object} options
   *   Options object.
   *
   * @return {object}
   *   Altered options object.
   */
  function resetPosition(options) {
    const offsets = displace.offsets;
    const left = offsets.left - offsets.right;
    const top = offsets.top - offsets.bottom;

    const leftString = `${
      (left > 0 ? '+' : '-') + Math.abs(Math.round(left / 2))
    }px`;
    const topString = `${
      (top > 0 ? '+' : '-') + Math.abs(Math.round(top / 2))
    }px`;
    options.position = {
      my: `center${left !== 0 ? leftString : ''} center${
        top !== 0 ? topString : ''
      }`,
      of: window,
    };
    return options;
  }

  /**
   * Resets the current options for positioning.
   *
   * This is used as a window resize and scroll callback to reposition the
   * jQuery UI dialog. Although not a built-in jQuery UI option, this can
   * be disabled by setting autoResize: false in the options array when creating
   * a new {@link Drupal.dialog}.
   *
   * @function Drupal.dialog~resetSize
   *
   * @param {jQuery.Event} event
   *   The event triggered.
   *
   * @fires event:dialogContentResize
   */
  function resetSize(event) {
    const positionOptions = [
      'width',
      'height',
      'minWidth',
      'minHeight',
      'maxHeight',
      'maxWidth',
      'position',
    ];
    let adjustedOptions = {};
    let windowHeight = $(window).height();
    let option;
    let optionValue;
    let adjustedValue;
    for (let n = 0; n < positionOptions.length; n++) {
      option = positionOptions[n];
      optionValue = event.data.settings[option];
      if (optionValue) {
        // jQuery UI does not support percentages on heights, convert to pixels.
        if (
          typeof optionValue === 'string' &&
          /%$/.test(optionValue) &&
          /height/i.test(option)
        ) {
          // Take offsets in account.
          windowHeight -= displace.offsets.top + displace.offsets.bottom;
          adjustedValue = parseInt(
            0.01 * parseInt(optionValue, 10) * windowHeight,
            10,
          );
          // Don't force the dialog to be bigger vertically than needed.
          if (
            option === 'height' &&
            event.data.$element.parent().outerHeight() < adjustedValue
          ) {
            adjustedValue = 'auto';
          }
          adjustedOptions[option] = adjustedValue;
        }
      }
    }
    // Offset the dialog center to be at the center of Drupal.displace.offsets.
    if (!event.data.settings.modal) {
      adjustedOptions = resetPosition(adjustedOptions);
    }
    event.data.$element
      .dialog('option', adjustedOptions)
      .trigger('dialogContentResize');
  }

  $(window).on({
    'dialog:aftercreate': function (event, dialog, $element, settings) {
      const autoResize = debounce(resetSize, 20);
      const eventData = { settings, $element };
      if (settings.autoResize === true || settings.autoResize === 'true') {
        $element
          .dialog('option', { resizable: false, draggable: false })
          .dialog('widget')
          .css('position', 'fixed');
        $(window)
          .on('resize.dialogResize scroll.dialogResize', eventData, autoResize)
          .trigger('resize.dialogResize');
        $(document).on(
          'drupalViewportOffsetChange.dialogResize',
          eventData,
          autoResize,
        );
      }
    },
    'dialog:beforeclose': function (event, dialog, $element) {
      $(window).off('.dialogResize');
      $(document).off('.dialogResize');
    },
  });
})(jQuery, Drupal, drupalSettings, Drupal.debounce, Drupal.displace);
;
/**
 * @file
 * Adds default classes to buttons for styling purposes.
 */

(function ($, { tabbable, isTabbable }) {
  $.widget('ui.dialog', $.ui.dialog, {
    options: {
      buttonClass: 'button',
      buttonPrimaryClass: 'button--primary',
    },
    _createButtons() {
      const opts = this.options;
      let primaryIndex;
      let index;
      const il = opts.buttons.length;
      for (index = 0; index < il; index++) {
        if (
          opts.buttons[index].primary &&
          opts.buttons[index].primary === true
        ) {
          primaryIndex = index;
          delete opts.buttons[index].primary;
          break;
        }
      }
      this._super();
      const $buttons = this.uiButtonSet.children().addClass(opts.buttonClass);
      if (typeof primaryIndex !== 'undefined') {
        $buttons.eq(index).addClass(opts.buttonPrimaryClass);
      }
    },
    // Override jQuery UI's `_focusTabbable()` so finding tabbable elements uses
    // the core/tabbable library instead of jQuery UI's `:tabbable` selector.
    _focusTabbable() {
      // Set focus to the first match:

      // 1. An element that was focused previously.
      let hasFocus = this._focusedElement ? this._focusedElement.get(0) : null;

      // 2. First element inside the dialog matching [autofocus].
      if (!hasFocus) {
        hasFocus = this.element.find('[autofocus]').get(0);
      }

      // 3. Tabbable element inside the content element.
      // 4. Tabbable element inside the buttonpane.
      if (!hasFocus) {
        const $elements = [this.element, this.uiDialogButtonPane];
        for (let i = 0; i < $elements.length; i++) {
          const element = $elements[i].get(0);
          if (element) {
            const elementTabbable = tabbable(element);
            hasFocus = elementTabbable.length ? elementTabbable[0] : null;
          }
          if (hasFocus) {
            break;
          }
        }
      }

      // 5. The close button.
      if (!hasFocus) {
        const closeBtn = this.uiDialogTitlebarClose.get(0);
        hasFocus = closeBtn && isTabbable(closeBtn) ? closeBtn : null;
      }

      // 6. The dialog itself.
      if (!hasFocus) {
        hasFocus = this.uiDialog.get(0);
      }
      $(hasFocus).eq(0).trigger('focus');
    },
  });
})(jQuery, window.tabbable);
;
/**
 * @file
 * This file overrides the way jQuery UI focus trap works.
 *
 * When a focus event is fired while a CKEditor 5 instance is focused, do not
 * trap the focus and let CKEditor 5 manage that focus.
 */

(($) => {
  $.widget('ui.dialog', $.ui.dialog, {
    // Override core override of jQuery UI's `_allowInteraction()` so that
    // CKEditor 5 in modals can work as expected.
    // @see https://api.jqueryui.com/dialog/#method-_allowInteraction
    _allowInteraction(event) {
      return event.target.classList.contains('ck') || this._super(event);
    },
  });
})(jQuery);
;
/**
 * @file
 * Attaches behavior for the Editor module.
 */

(function ($, Drupal, drupalSettings) {
  /**
   * Finds the text area field associated with the given text format selector.
   *
   * @param {jQuery} $formatSelector
   *   A text format selector DOM element.
   *
   * @return {HTMLElement}
   *   The text area DOM element, if it was found.
   */
  function findFieldForFormatSelector($formatSelector) {
    const fieldId = $formatSelector.attr('data-editor-for');
    // This selector will only find text areas in the top-level document. We do
    // not support attaching editors on text areas within iframes.
    return $(`#${fieldId}`).get(0);
  }

  /**
   * Filter away XSS attack vectors when switching text formats.
   *
   * @param {HTMLElement} field
   *   The textarea DOM element.
   * @param {object} format
   *   The text format that's being activated, from
   *   drupalSettings.editor.formats.
   * @param {string} originalFormatID
   *   The text format ID of the original text format.
   * @param {function} callback
   *   A callback to be called (with no parameters) after the field's value has
   *   been XSS filtered.
   */
  function filterXssWhenSwitching(field, format, originalFormatID, callback) {
    // A text editor that already is XSS-safe needs no additional measures.
    if (format.editor.isXssSafe) {
      callback(field, format);
    }
    // Otherwise, ensure XSS safety: let the server XSS filter this value.
    else {
      $.ajax({
        url: Drupal.url(`editor/filter_xss/${format.format}`),
        type: 'POST',
        data: {
          value: field.value,
          original_format_id: originalFormatID,
        },
        dataType: 'json',
        success(xssFilteredValue) {
          // If the server returns false, then no XSS filtering is needed.
          if (xssFilteredValue !== false) {
            field.value = xssFilteredValue;
          }
          callback(field, format);
        },
      });
    }
  }

  /**
   * Changes the text editor on a text area.
   *
   * @param {HTMLElement} field
   *   The text area DOM element.
   * @param {string} newFormatID
   *   The text format we're changing to; the text editor for the currently
   *   active text format will be detached, and the text editor for the new text
   *   format will be attached.
   */
  function changeTextEditor(field, newFormatID) {
    const previousFormatID = field.getAttribute(
      'data-editor-active-text-format',
    );

    // Detach the current editor (if any) and attach a new editor.
    if (drupalSettings.editor.formats[previousFormatID]) {
      Drupal.editorDetach(
        field,
        drupalSettings.editor.formats[previousFormatID],
      );
    }
    // When no text editor is currently active, stop tracking changes.
    else {
      $(field).off('.editor');
    }

    // Attach the new text editor (if any).
    if (drupalSettings.editor.formats[newFormatID]) {
      const format = drupalSettings.editor.formats[newFormatID];
      filterXssWhenSwitching(
        field,
        format,
        previousFormatID,
        Drupal.editorAttach,
      );
    }

    // Store the new active format.
    field.setAttribute('data-editor-active-text-format', newFormatID);
  }

  /**
   * Handles changes in text format.
   *
   * @param {jQuery.Event} event
   *   The text format change event.
   */
  function onTextFormatChange(event) {
    const select = event.target;
    const field = event.data.field;
    const activeFormatID = field.getAttribute('data-editor-active-text-format');
    const newFormatID = select.value;

    // Prevent double-attaching if the change event is triggered manually.
    if (newFormatID === activeFormatID) {
      return;
    }

    // When changing to a text format that has a text editor associated
    // with it that supports content filtering, then first ask for
    // confirmation, because switching text formats might cause certain
    // markup to be stripped away.
    const supportContentFiltering =
      drupalSettings.editor.formats[newFormatID] &&
      drupalSettings.editor.formats[newFormatID].editorSupportsContentFiltering;
    // If there is no content yet, it's always safe to change the text format.
    const hasContent = field.value !== '';
    if (hasContent && supportContentFiltering) {
      const message = Drupal.t(
        'Changing the text format to %text_format will permanently remove content that is not allowed in that text format.<br><br>Save your changes before switching the text format to avoid losing data.',
        {
          '%text_format': $(select).find('option:selected')[0].textContent,
        },
      );
      const confirmationDialog = Drupal.dialog(`<div>${message}</div>`, {
        title: Drupal.t('Change text format?'),
        dialogClass: 'editor-change-text-format-modal',
        resizable: false,
        buttons: [
          {
            text: Drupal.t('Continue'),
            class: 'button button--primary',
            click() {
              changeTextEditor(field, newFormatID);
              confirmationDialog.close();
            },
          },
          {
            text: Drupal.t('Cancel'),
            class: 'button',
            click() {
              // Restore the active format ID: cancel changing text format. We
              // cannot simply call event.preventDefault() because jQuery's
              // change event is only triggered after the change has already
              // been accepted.
              select.value = activeFormatID;
              confirmationDialog.close();
            },
          },
        ],
        // Prevent this modal from being closed without the user making a choice
        // as per http://stackoverflow.com/a/5438771.
        closeOnEscape: false,
        create() {
          $(this).parent().find('.ui-dialog-titlebar-close').remove();
        },
        beforeClose: false,
        close(event) {
          // Automatically destroy the DOM element that was used for the dialog.
          $(event.target).remove();
        },
      });

      confirmationDialog.showModal();
    } else {
      changeTextEditor(field, newFormatID);
    }
  }

  /**
   * Initialize an empty object for editors to place their attachment code.
   *
   * @namespace
   */
  Drupal.editors = {};

  /**
   * Enables editors on text_format elements.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches an editor to an input element.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches an editor from an input element.
   */
  Drupal.behaviors.editor = {
    attach(context, settings) {
      // If there are no editor settings, there are no editors to enable.
      if (!settings.editor) {
        return;
      }

      once('editor', '[data-editor-for]', context).forEach((editor) => {
        const $this = $(editor);
        const field = findFieldForFormatSelector($this);

        // Opt-out if no supported text area was found.
        if (!field) {
          return;
        }

        // Store the current active format.
        const activeFormatID = editor.value;
        field.setAttribute('data-editor-active-text-format', activeFormatID);

        // Directly attach this text editor, if the text format is enabled.
        if (settings.editor.formats[activeFormatID]) {
          // XSS protection for the current text format/editor is performed on
          // the server side, so we don't need to do anything special here.
          Drupal.editorAttach(field, settings.editor.formats[activeFormatID]);
        }
        // When there is no text editor for this text format, still track
        // changes, because the user has the ability to switch to some text
        // editor, otherwise this code would not be executed.
        $(field).on('change.editor keypress.editor', () => {
          field.setAttribute('data-editor-value-is-changed', 'true');
          // Just knowing that the value was changed is enough, stop tracking.
          $(field).off('.editor');
        });

        // Attach onChange handler to text format selector element.
        if ($this.is('select')) {
          $this.on('change.editorAttach', { field }, onTextFormatChange);
        }
        // Detach any editor when the containing form is submitted.
        $this.parents('form').on('submit', (event) => {
          // Do not detach if the event was canceled.
          if (event.isDefaultPrevented()) {
            return;
          }
          // Detach the current editor (if any).
          if (settings.editor.formats[activeFormatID]) {
            Drupal.editorDetach(
              field,
              settings.editor.formats[activeFormatID],
              'serialize',
            );
          }
        });
      });
    },

    detach(context, settings, trigger) {
      let editors;
      // The 'serialize' trigger indicates that we should simply update the
      // underlying element with the new text, without destroying the editor.
      if (trigger === 'serialize') {
        // Removing the editor-processed class guarantees that the editor will
        // be reattached. Only do this if we're planning to destroy the editor.
        editors = once.filter('editor', '[data-editor-for]', context);
      } else {
        editors = once.remove('editor', '[data-editor-for]', context);
      }

      editors.forEach((editor) => {
        const $this = $(editor);
        const activeFormatID = editor.value;
        const field = findFieldForFormatSelector($this);
        if (field && activeFormatID in settings.editor.formats) {
          Drupal.editorDetach(
            field,
            settings.editor.formats[activeFormatID],
            trigger,
          );
        }
      });
    },
  };

  /**
   * Attaches editor behaviors to the field.
   *
   * @param {HTMLElement} field
   *   The textarea DOM element.
   * @param {object} format
   *   The text format that's being activated, from
   *   drupalSettings.editor.formats.
   *
   * @listens event:change
   *
   * @fires event:formUpdated
   */
  Drupal.editorAttach = function (field, format) {
    if (format.editor) {
      // Attach the text editor.
      Drupal.editors[format.editor].attach(field, format);

      // Ensures form.js' 'formUpdated' event is triggered even for changes that
      // happen within the text editor.
      Drupal.editors[format.editor].onChange(field, () => {
        $(field).trigger('formUpdated');

        // Keep track of changes, so we know what to do when switching text
        // formats and guaranteeing XSS protection.
        field.setAttribute('data-editor-value-is-changed', 'true');
      });
    }
  };

  /**
   * Detaches editor behaviors from the field.
   *
   * @param {HTMLElement} field
   *   The textarea DOM element.
   * @param {object} format
   *   The text format that's being activated, from
   *   drupalSettings.editor.formats.
   * @param {string} trigger
   *   Trigger value from the detach behavior.
   */
  Drupal.editorDetach = function (field, format, trigger) {
    if (format.editor) {
      Drupal.editors[format.editor].detach(field, format, trigger);

      // Restore the original value if the user didn't make any changes yet.
      if (field.getAttribute('data-editor-value-is-changed') === 'false') {
        field.value = field.getAttribute('data-editor-value-original');
      }
    }
  };
})(jQuery, Drupal, drupalSettings);
;
