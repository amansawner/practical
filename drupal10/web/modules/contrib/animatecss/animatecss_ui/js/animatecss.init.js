/**
 * @file
 * Contains definition of the behaviour Animate.css.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const compat = drupalSettings.animatecss.compat;

  Drupal.behaviors.animateCSS = {
    attach: function (context, settings) {

      const elements = drupalSettings.animatecss.elements;

      $.each(elements, function (index, element) {
        let options = {
          selector: element.selector,
          animation:  element.animation,
          delay: element.delay,
          time: element.time,
          speed: element.speed,
          duration: element.duration,
          repeat: element.repeat,
        };

        if (Array.isArray(options.selector)) {
          $.each(options.selector, function (index, selector) {
            if (once('animatecss', selector).length) {
              options.selector = selector;
              let animateCSS = new Drupal.animateCSS(options);
            }
          });
        }
        else {
          if (once('animatecss', options.selector).length) {
            let animateCSS = new Drupal.animateCSS(options);
          }
        }
      });

    }
  };

  Drupal.animateCSS = function (options) {
    // Build Animate.css classes from global AdminCSS settings.
    let Prefix  = compat ? '' : 'animate__';
    let Classes = `${Prefix}animated`;

    if (options.animation) {
      Classes += ` ${Prefix}${options.animation}`;

      if (options.delay && options.delay != 'custom') {
        Classes += ` ${Prefix}${options.delay}`;
      }
      if (options.speed && options.speed != 'custom' && options.speed != 'medium') {
        Classes += ` ${Prefix}${options.speed}`;
      }
      if (options.repeat && options.repeat != 'repeat-1') {
        Classes += ` ${Prefix}${options.repeat}`;
      }

      // Add Animate.css custom properties.
      if (options.delay == 'custom') {
        $(options.selector).css({
          '-webkit-animation-delay': options.time + 'ms',
          '-moz-animation-delay': options.time + 'ms',
          '-ms-animation-delay': options.time + 'ms',
          '-o-animation-delay': options.time + 'ms',
          'animation-delay': options.time + 'ms',
          '--animate-delay': options.time + 'ms',
        });
      }
      if (options.speed == 'custom') {
        $(options.selector).css({
          '-webkit-animation-duration': options.duration + 'ms',
          '-moz-animation-duration': options.duration + 'ms',
          '-ms-animation-duration': options.duration + 'ms',
          '-o-animation-duration': options.duration + 'ms',
          'animation-duration': options.duration + 'ms',
          '--animate-duration': options.duration + 'ms',
        });
      }

      // Add Animate.css classes.
      $(options.selector).addClass(Classes);
    }
  };

})(jQuery, Drupal, drupalSettings, once);
