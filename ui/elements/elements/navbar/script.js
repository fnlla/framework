(function () {
  var megaMenus = Array.prototype.slice.call(document.querySelectorAll('[data-mega-menu]'));
  if (megaMenus.length === 0) {
    return;
  }

  function setState(menu, isOpen) {
    menu.classList.toggle('is-open', isOpen);
    var trigger = menu.querySelector('[data-mega-trigger]');
    var panel = menu.querySelector('[data-mega-panel]');
    if (trigger) {
      trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }
    if (panel) {
      panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
    }
  }

  function closeAll(except) {
    megaMenus.forEach(function (menu) {
      if (menu !== except) {
        setState(menu, false);
      }
    });
  }

  megaMenus.forEach(function (menu) {
    var trigger = menu.querySelector('[data-mega-trigger]');
    var panel = menu.querySelector('[data-mega-panel]');
    if (!trigger || !panel) {
      return;
    }

    var closeTimeout;

    setState(menu, false);

    function open() {
      clearTimeout(closeTimeout);
      closeAll(menu);
      setState(menu, true);
    }

    function close() {
      clearTimeout(closeTimeout);
      setState(menu, false);
    }

    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      var isOpen = menu.classList.contains('is-open');
      if (isOpen) {
        close();
      } else {
        open();
      }
    });

    menu.addEventListener('focusout', function (event) {
      if (!menu.contains(event.relatedTarget)) {
        close();
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') {
        close();
        trigger.blur();
      }
    });

    document.addEventListener('click', function (event) {
      if (!menu.contains(event.target)) {
        close();
      }
    });
  });
})();
