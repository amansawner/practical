INTRODUCTION
------------

This module integrates the 'WOW.js' library - https://github.com/matthieua/WOW,
WOW is a Javascript library which works nicely with the Animate CSS library
to create great cross browser CSS3-based animations in your Drupal sites.


FEATURES
--------

'WOW.js' library is:

  - Cross-browser animations

  - Usage with Javascript

  - Easy to use

  - Responsive

  - Customizable


REQUIREMENTS
------------

'WOW.js' library:

  - https://github.com/matthieua/WOW/archive/master.zip


INSTALLATION
------------

1. Download 'WOW JS' module - https://www.drupal.org/project/wowjs

2. Extract and place it in the root of contributed modules directory i.e.
   /modules/contrib/wowjs or /modules/wowjs

3. Create a libraries directory in the root, if not already there i.e.
   /libraries

4. Create a 'wow' directory inside it i.e.
   /libraries/wow

5. Download 'WOW.js' library
   https://github.com/matthieua/WOW/archive/master.zip

6. Place it in the /libraries/wow directory i.e. Required files:
  - /libraries/wow/dist/wow.js
  - /libraries/wow/dist/wow.min.js

7. Now, enable 'WOW JS' module


USAGE
-----

The Animate CSS library provides a complete set of CSS3 animations you can
apply to any HTML element in your Drupal site using AnimateCSS UI module or
just using AnimateCSS to load Animate.css in your pages and providing some
CSS classes and  on your templates for the HTML elements you want to animate.

The wow.js library provides more control over those animations you use the
Animate CSS library only, and you set an animation for an HTML element which
is outside the fold, when you scroll down the page to see that HTML element,
the CSS animation will probably have finished. Using the wow.js library with
the Animate CSS library, the CSS animations of the HTML elements will start
when those elements become visible as you scroll down the page. You can see
a demo in the wow.js library's home page (https://www.delac.io/WOW/).

The wow.js library provides another nice feature: letting you set how many
times an animation will be repeated, how much time the animation will last,
how much time the animation will delay until it will start, etc.


BASIC USAGE
===========

NOTE: With WOW JS Module You don't need init WOW with js, module will handle.

Just for information:
After installing WOW JS Module, add the class "wow" to an element, along with
any of the Animate.css animation names (don't forget the animate__ prefix!):

HTML
++++

<section class="wow animate__slideInLeft"></section>
<section class="wow animate__slideInRight"></section>

JavaScript
++++++++++

new WOW().init();


ADVANCED USAGE
==============
You can do a bunch of other stuff with WOW.js with more options:

HTML
++++

<section class="wow animate__slideInLeft" data-wow-duration="2s"></section>
<section class="wow animate__slideInRight" data-wow-offset="10"></section>

JavaScript
++++++++++

var wow = new WOW(
  {
    // animated element css class (default is wow)
    boxClass:     'wow',
    // animation css class (default is animated)
    animateClass: 'animate__animated',
    // distance to the element when triggering the animation (default is 0)
    offset:       0,
    // trigger animations on mobile devices (default is true)
    mobile:       true,
    // act on asynchronously loaded content (default is true)
    live:         true,
    callback:     function(box) {
      // the callback is fired every time an animation is started
      // the argument that is passed in is the DOM node being animated
    },
    // optional scroll container selector, otherwise use window
    scrollContainer: null
  }
);
wow.init();


How does it Work?
-----------------

1. Enable "WOW JS" module, Follow INSTALLATION in above.

2. Add "wow" and Animate.css animation name classes to templates or add
   classed in your Theme/Module javascript file, Follow USAGE in above.

3. Enjoy that.

Animations can improve the UX of an interface, but keep in mind that they can
also get in the way of your users! Please read the best practices and gotchas
sections to bring your web-things to life in the best way possible.


MAINTAINERS
-----------

Current module maintainer:

 * Mahyar Sabeti - https://www.drupal.org/u/mahyarsbt


DEMO
----
https://www.delac.io/WOW/
