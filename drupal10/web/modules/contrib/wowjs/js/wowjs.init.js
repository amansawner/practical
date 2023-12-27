/**
 * @file
 * Contains definition of the behaviour WOW.js.
 */

(function ($, Drupal, drupalSettings, once) {
  "use strict";

  const compat = drupalSettings.wowjs.compat;

  Drupal.behaviors.wowInit = {
    attach: function (context, settings) {

      // Build Animate.css classes from global AdminCSS settings.
      let Prefix = compat ? '' : 'animate__';
      let animateClass = `${Prefix}animated`;
      let animatedElement = $('.' + animateClass);

      // Replace wow with animated to prepare.
      $(animatedElement).addClass('wow');
      $(animatedElement).removeClass(animateClass);

      // Make it WOW().init comment if you want to
      // call in your Theme/Module Javascript file.
      new WOW({ animateClass: animateClass }).init();

      // In IE 10+, Chrome 18+ and Firefox 14+, animations will be
      // automatically triggered for any DOM nodes you add after
      // calling wow.init(). If you do not like that, you can disable
      // this by setting live to false.

      // If you want to support older browsers (e.g. IE9+), as a fallback,
      // you can call the wow.sync() method after you have added new
      // DOM elements to animate (but live should still be set to true).
      // Calling wow.sync() has no side effects.
    }
  };

})(jQuery, Drupal, drupalSettings, once);
