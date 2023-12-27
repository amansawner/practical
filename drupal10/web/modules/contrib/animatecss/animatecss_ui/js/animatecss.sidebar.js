/* eslint-disable func-names, no-mutable-exports, comma-dangle, strict */

((Drupal, once) => {
  const breakpoint = 1024;
  const storageMobile = 'Drupal.animateCSS.sidebarExpanded.mobile';
  const storageDesktop = 'Drupal.animateCSS.sidebarExpanded.desktop';

  Drupal.behaviors.animatecssSidebar = {
    attach: function attach(context) {
      Drupal.animatecssSidebar.init(context);
    },
  };

  Drupal.animatecssSidebar = {
    init: function (context) {
      once('animatecssSidebarInit', '#animatecss_sidebar', context).forEach(() => {
        // If variable does not exist, create it, default being to show sidebar.
        console.log(localStorage.getItem(storageDesktop));
        if (!localStorage.getItem(storageDesktop)) {
          localStorage.setItem(storageDesktop, 'true');
        }

        // Set mobile initial to false.
        if (window.innerWidth >= breakpoint) {
          if (localStorage.getItem(storageDesktop) === 'true') {
            this.showSidebar();
          }
          else {
            this.collapseSidebar();
          }
        }

        // Show navigation with shortcut:
        // OPTION + S (Mac) / ALT + S (Windows)
        document.addEventListener('keydown', e => {
          if (e.altKey === true && e.code === 'KeyS') {
            this.toggleSidebar();
          }
        });

        window.onresize = Drupal.debounce(this.handleResize, 150);
      });

      // Toolbar toggle
      once('animatecssSidebarToggle', '.toggle-sidebar__trigger', context).forEach(el => el.addEventListener('click', e => {
        e.preventDefault();
        this.toggleSidebar();
      }));

      // Toolbar close
      once('animatecssSidebarClose', '.toggle-sidebar__close, .toggle-sidebar__overlay', context).forEach(el => el.addEventListener('click', e => {
        e.preventDefault();
        this.collapseSidebar();
      }));
    },

    toggleSidebar: () => {
      // Set active state.
      if (document.querySelector('.toggle-sidebar__trigger').classList.contains('is-active')) {
        Drupal.animatecssSidebar.collapseSidebar();
      }
      else {
        Drupal.animatecssSidebar.showSidebar();
      }
    },

    showSidebar: () => {
      const chooseStorage = window.innerWidth < breakpoint ? storageMobile : storageDesktop;
      const showLabel = Drupal.t('Hide sidebar panel');
      const sidebarTrigger = document.querySelector('.toggle-sidebar__trigger');

      sidebarTrigger.setAttribute('title', showLabel);
      sidebarTrigger.querySelector('span').innerHTML = showLabel;
      sidebarTrigger.setAttribute('aria-expanded', 'true');
      sidebarTrigger.classList.add('is-active');

      document.body.classList.add('toggle-sidebar-open');
      document.body.setAttribute('data-toggle-sidebar', 'open');

      // Expose to localStorage.
      localStorage.setItem(chooseStorage, 'true');
    },

    collapseSidebar: () => {
      const chooseStorage = window.innerWidth < breakpoint ? storageMobile : storageDesktop;
      const hideLabel = Drupal.t('Show sidebar panel');
      const sidebarTrigger = document.querySelector('.toggle-sidebar__trigger');

      sidebarTrigger.setAttribute('title', hideLabel);
      sidebarTrigger.querySelector('span').innerHTML = hideLabel;
      sidebarTrigger.setAttribute('aria-expanded', 'false');
      sidebarTrigger.classList.remove('is-active');

      document.body.classList.remove('toggle-sidebar-open');
      document.body.setAttribute('data-toggle-sidebar', 'closed');

      // Expose to localStorage.
      localStorage.setItem(chooseStorage, 'false');
    },

    handleResize: () => {
      // If small viewport, always collapse sidebar.
      if (window.innerWidth < breakpoint) {
        Drupal.animatecssSidebar.collapseSidebar();
      } else {
        // If large viewport, show sidebar if it was open before.
        if (localStorage.getItem(storageDesktop) === 'true') {
          Drupal.animatecssSidebar.showSidebar();
        } else {
          Drupal.animatecssSidebar.collapseSidebar();
        }
      }
    },

  };
})(Drupal, once);
