/**
 * @file
 * Contains definition of the behaviour WOW.js.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const compat = drupalSettings.wowjs.compat;
  const wClass = drupalSettings.wowjs.wowClass;

  Drupal.behaviors.wowJS = {
    attach: function (context, settings) {

      const elements = drupalSettings.wowjs.elements;

      $.each(elements, function (index, element) {
        let options = {
          selector: element.selector,
          animation:  element.animation,
          delay: element.delay,
          time: element.time,
          speed: element.speed,
          duration: element.duration,
          repeat: element.repeat,
          classBox: wClass,
          offset: element.wow.offset,
          mobile: element.wow.mobile,
          live: element.wow.live,
          optionalContainer: element.wow.optionalContainer,
          scrollContainer: element.wow.scrollContainer,
        };

        if (once('wowjs', options.selector).length) {
          new Drupal.WOW(options);
        }

      });

    }
  };

  Drupal.WOW = function (options) {
    //console.log(options);
    // Build WOW.js classes from global AdminCSS settings.
    let Prefix  = compat ? '' : 'animate__';
    let Classes = `${Prefix}animated`;

    // Add WOW boxClass.
    $(options.selector).addClass(options.classBox);

    let container = null;
    if (options.optionalContainer && options.scrollContainer !== 'window') {
      container = options.scrollContainer;
    }

    let wow = new WOW(
      {
        boxClass:     options.classBox,   // animated element css class (default is wow)
        animateClass: Classes,            // animation css class (default is animated)
        offset:       options.offset,     // distance to the element when triggering the animation (default is 0)
        mobile:       options.mobile,     // trigger animations on mobile devices (default is true)
        live:         options.live,       // act on asynchronously loaded content (default is true)
        callback:     function (box) {
          // the callback is fired every time an animation is started
          // the argument that is passed in is the DOM node being animated
        },
        scrollContainer: container // optional scroll container selector, otherwise use window
      }
    );

    if (options.animation) {
      Classes = `${Prefix}${options.animation}`;

      if (options.delay && options.delay != 'custom') {
        Classes += ` ${Prefix}${options.delay}`;
      }
      if (options.speed && options.speed != 'custom' && options.speed != 'medium') {
        Classes += ` ${Prefix}${options.speed}`;
      }
      if (options.repeat && options.repeat != 'repeat-1') {
        Classes += ` ${Prefix}${options.repeat}`;
      }

      // Add WOW.js classes.
      $(options.selector).addClass(Classes);

      // Add WOW.js custom properties.
      if (options.delay == 'custom' && !compat) {
        $(options.selector).css('--animate-delay', options.time);
      }
      if (options.speed == 'custom' && !compat) {
        $(options.selector).css('--animate-duration', options.duration);
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

      // Initial WOW now.
      wow.init();
    }
  };

})(jQuery, Drupal, drupalSettings, once);
