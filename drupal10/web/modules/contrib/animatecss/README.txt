INTRODUCTION
------------

This module integrates the 'Animate.css' library - https://animate.style/,
Animate.css is a library of ready-to-use, cross-browser animations
for use in your web projects. Great for emphasis, home pages, sliders,
and attention-guiding hints.


FEATURES
--------

'Animate.css' library is:

  - Cross-browser animations

  - Usage with Javascript

  - Easy to use

  - Responsive

  - Customizable


REQUIREMENTS
------------

'Animate.css' library:

  - https://github.com/animate-css/animate.css/archive/main.zip


INSTALLATION
------------

1. Download 'AnimateCSS' module - https://www.drupal.org/project/animatecss

2. Extract and place it in the root of contributed modules directory i.e.
   /modules/contrib/animatecss

3. Create a libraries directory in the root, if not already there i.e.
   /libraries

4. Create a 'animate.css' directory inside it i.e.
   /libraries/animate.css

5. Download 'Animate.css' library
   https://github.com/animate-css/animate.css/archive/main.zip

6. Place it in the /libraries/animate.css directory i.e. Required files:

  - /libraries/animate.css/dist/animate.compat.css
  - /libraries/animate.css/dist/animate.css
  - /libraries/animate.css/dist/animate.min.css

7. Now, enable 'AnimateCSS' module


USAGE
-----

It’s very simple to use a library, which can be downloaded as a one CSS file
and added to your project to use one of many predefined animations by adding
a class to an element.

You can customize selected animations by setting the delay and speed
of the effect. It’s possible to use animations with pure HTML and CSS projects,
but you can also implement Javascript as well.

BASIC USAGE
===========

After installing Animate.css, add the class animate__animated to an element,
along with any of the animation names (don't forget the animate__ prefix!):

<h1 class="animate__animated animate__bounce">An animated element</h1>

USAGE WITH JAVASCRIPT
=====================

You can do a bunch of other stuff with animate.css when you combine
it with Javascript. A simple example:

const element = document.querySelector('.my-element');
element.classList.add('animate__animated', 'animate__bounceOutLeft');


How does it Work?
-----------------

1. Enable "AnimateCSS" module, Follow INSTALLATION in above.

2. Add animate.css classes to templates or add classed with javascript file
   in your theme, Follow USAGE in above.

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
https://animate.style/
