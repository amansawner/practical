/**
 * @file
 * Contains definition of the behaviour Animate.css.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  Drupal.behaviors.animateCSS = {
    attach: function (context, settings) {

      const example = drupalSettings.animatecss.sample;
      const Selector = example.selector;

      if (once('animate__sample', Selector).length) {
        let options = {
          selector: Selector,
          animation: example.animation,
          delay: example.delay,
          time: example.time,
          speed: example.speed,
          duration: example.duration,
          repeat: example.repeat,
        };
        let demoAnimateCSS = new Drupal.animateCSSdemo(options);

        // Animate.css preview replay.
        $(once('animate__replay', '.animate__replay', context)).on(
          'click',
          function (event) {
            $(Selector).attr('class', 'animate__sample');

            let options = {
              selector: Selector,
              animation: $('#edit-animation').val(),
              delay: $('#edit-delay').val(),
              time: $('#edit-time').val(),
              speed: $('#edit-speed').val(),
              duration: $('#edit-duration').val(),
              repeat: $('#edit-repeat').val(),
            };

            setTimeout(function () {
              let demoAnimateCSS = new Drupal.animateCSSdemo(options);
            }, 10);

            event.preventDefault();
          }
        );

        // Animate.css preview replay.
        $(once('animate__animation', '.animate__animation', context)).on(
          'change',
          function (event) {
            setTimeout(function () {
              $('.animate__replay').trigger('click');
            }, 10);
          }
        );

        if ( once('animate__scroll', '.animate__scroll').length ) {
          let scrollLibraries = $('.animate__scroll');

          scrollLibraries.bind({
            click: function (e) {
              let currentLibrary = $(this)
                , closestWrapper = currentLibrary.closest('.form-wrapper');

              if ( currentLibrary.is(':checked') ) {
                scrollLibraries.not(this).prop({ disabled: true, checked: false });
                scrollLibraries.not(this).closest('.form-wrapper').removeAttr('open');
              } else {
                scrollLibraries.not(this).prop({ disabled: false, checked: false });
                scrollLibraries.not(this).closest('.form-wrapper').attr('open', 'open');
              }
            },
          });
        }

      }

      once('ginEditForm', '.region-content form', context).forEach(form => {
        const sticky = context.querySelector('.gin-sticky');
        const newParent = context.querySelector('.region-sticky__items__inner');

        if (newParent && newParent.querySelectorAll('.gin-sticky').length === 0) {
          newParent.appendChild(sticky);

          // Attach form elements to main form
          const actionButtons = newParent.querySelectorAll('button, input, select, textarea');

          if (actionButtons.length > 0) {
            actionButtons.forEach((el) => {
              el.setAttribute('form', form.getAttribute('id'));
              el.setAttribute('id', el.getAttribute('id') + '--gin-edit-form');
            });
          }
        }
      });

    }
  };

  Drupal.animateCSSdemo = function (options) {
    // Build Animate.css classes from global AdminCSS settings.
    let Classes = `animate__animated`;

    if (options.animation) {
      Classes += ` animate__${options.animation}`;

      if (options.delay && options.delay != 'custom') {
        Classes += ` animate__${options.delay}`;
      }
      if (options.speed && options.speed != 'custom' && options.speed != 'medium') {
        Classes += ` animate__${options.speed}`;
      }
      if (options.repeat && options.repeat != 'repeat-1') {
        Classes += ` animate__${options.repeat}`;
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
