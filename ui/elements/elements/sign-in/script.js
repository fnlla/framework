(function () {
  var email = document.getElementById('signin-email');
  if (email) {
    email.focus();
  }

  var prefersReducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function initCarousel(panel, slides) {
    if (!panel || !slides || !slides.length) {
      return;
    }

    var isStatic = panel.getAttribute('data-auth-static') === 'true';
    var title = panel.querySelector('h2');
    var copy = panel.querySelector('.auth-panel-copy');
    var illustration = panel.querySelector('.auth-illustration');
    var illustrationImg = illustration ? illustration.querySelector('img') : null;
    var dots = Array.prototype.slice.call(panel.querySelectorAll('.auth-dot'));
    var nextBtn = panel.querySelector('.auth-next');
    var skipBtn = panel.querySelector('.auth-skip');
    var skipFocus = panel.getAttribute('data-auth-skip-focus');

    var current = 0;
    var intervalId = null;

    function setActiveDot(index) {
      if (!dots.length) {
        return;
      }

      dots.forEach(function (dot, dotIndex) {
        if (dotIndex === index) {
          dot.classList.add('is-active');
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.classList.remove('is-active');
          dot.removeAttribute('aria-current');
        }
      });
    }

    function applySlide(nextIndex) {
      var nextSlide = slides[nextIndex];
      if (title) {
        title.textContent = nextSlide.title;
      }
      if (copy) {
        copy.textContent = nextSlide.copy;
      }
      if (illustrationImg && nextSlide.image) {
        illustrationImg.src = nextSlide.image;
        illustrationImg.alt = nextSlide.alt || '';
      }
      setActiveDot(nextIndex);
      current = nextIndex;
    }

    function setSlide(index, withTransition) {
      var nextIndex = index % slides.length;
      if (nextIndex < 0) {
        nextIndex = slides.length - 1;
      }

      if (withTransition) {
        panel.classList.add('is-transitioning');
        window.setTimeout(function () {
          applySlide(nextIndex);
          panel.classList.remove('is-transitioning');
        }, 220);
      } else {
        applySlide(nextIndex);
      }
    }

    function stopAuto() {
      if (intervalId) {
        window.clearInterval(intervalId);
        intervalId = null;
      }
    }

    function startAuto() {
      if (prefersReducedMotion || slides.length < 2 || isStatic) {
        return;
      }
      stopAuto();
      intervalId = window.setInterval(function () {
        setSlide(current + 1, true);
      }, 6000);
    }

    if (isStatic) {
      panel.classList.add('is-static');
    }

    setSlide(0, false);

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        if (isStatic) {
          return;
        }
        setSlide(current + 1, true);
        startAuto();
      });
    }

    if (skipBtn) {
      skipBtn.addEventListener('click', function () {
        if (skipFocus) {
          var target = document.getElementById(skipFocus);
          if (target) {
            target.focus();
          }
        }
        stopAuto();
      });
    }

    dots.forEach(function (dot, index) {
      dot.addEventListener('click', function () {
        if (isStatic) {
          return;
        }
        setSlide(index, true);
        startAuto();
      });
    });

    if (!isStatic) {
      startAuto();
    }
  }

  var slideSets = {
    signin: [
      {
        title: 'Welcome to Finella',
        copy: 'Ship approvals, track execution, and keep every delivery signal in one place.',
        image: 'assets/signin-slide-1.svg',
        alt: 'Approvals snapshot illustration'
      },
      {
        title: 'Stay in control',
        copy: 'Align teams across projects with live timelines, alerts, and outcomes.',
        image: 'assets/signin-slide-2.svg',
        alt: 'Delivery timeline illustration'
      },
      {
        title: 'Launch with confidence',
        copy: 'Move from planning to delivery faster with a single, trusted hub.',
        image: 'assets/signin-slide-3.svg',
        alt: 'Release readiness illustration'
      }
    ],
    signup: [
      {
        title: 'Built for faster decisions',
        copy: 'A smarter way to manage approvals and documentation from one hub.',
        image: 'assets/signup-slide-1.svg',
        alt: 'Team collaboration illustration'
      },
      {
        title: 'Start in minutes',
        copy: 'Invite your team, assign roles, and begin shipping without extra setup.',
        image: 'assets/signup-slide-2.svg',
        alt: 'Quick onboarding illustration'
      },
      {
        title: 'Stay aligned',
        copy: 'Keep specs, approvals, and assets connected so every launch is smooth.',
        image: 'assets/signup-slide-3.svg',
        alt: 'Aligned workflows illustration'
      }
    ]
  };

  var panels = Array.prototype.slice.call(document.querySelectorAll('[data-auth-carousel]'));
  panels.forEach(function (panel) {
    var key = panel.getAttribute('data-auth-carousel');
    initCarousel(panel, slideSets[key]);
  });

  var toggles = Array.prototype.slice.call(document.querySelectorAll('[data-toggle-password]'));
  toggles.forEach(function (toggle) {
    var targetId = toggle.getAttribute('data-toggle-password');
    var input = document.getElementById(targetId);
    if (!input) {
      return;
    }

    toggle.addEventListener('click', function () {
      var isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      toggle.textContent = isPassword ? 'Hide' : 'Show';
      toggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
    });
  });

  var authCard = document.querySelector('[data-auth-card="signin"]');
  if (authCard) {
    var forgotBtn = authCard.querySelector('[data-auth-forgot]');
    var backBtn = authCard.querySelector('[data-auth-back]');
    var front = authCard.querySelector('.auth-card-front');
    var back = authCard.querySelector('.auth-card-back');

    function setFlip(isFlipped) {
      authCard.classList.toggle('is-flipped', isFlipped);
      if (front && back) {
        front.setAttribute('aria-hidden', isFlipped ? 'true' : 'false');
        back.setAttribute('aria-hidden', isFlipped ? 'false' : 'true');
      }
    }

    if (forgotBtn) {
      forgotBtn.addEventListener('click', function () {
        setFlip(true);
      });
    }

    if (backBtn) {
      backBtn.addEventListener('click', function () {
        setFlip(false);
      });
    }

    setFlip(false);
  }

  var providerButtons = Array.prototype.slice.call(document.querySelectorAll('[data-auth-provider]'));
  if (providerButtons.length) {
    var base = document.documentElement.getAttribute('data-auth-base') || '/auth';
    var allowAttr = document.documentElement.getAttribute('data-auth-providers');
    var iconsEnabled = document.documentElement.getAttribute('data-auth-icons');
    var allowList = null;
    if (allowAttr) {
      allowList = allowAttr
        .split(',')
        .map(function (item) {
          return item.trim().toLowerCase();
        })
        .filter(Boolean);
    }

    if (iconsEnabled === 'false') {
      Array.prototype.slice.call(document.querySelectorAll('.auth-provider-row--icons')).forEach(function (row) {
        row.style.display = 'none';
      });
    }

    providerButtons.forEach(function (button) {
      var provider = button.getAttribute('data-auth-provider');
      if (allowList && provider && allowList.indexOf(provider) === -1) {
        button.style.display = 'none';
        return;
      }

      button.addEventListener('click', function () {
        if (!provider) {
          return;
        }
        window.location.href = base.replace(/\/$/, '') + '/' + provider;
      });
    });

    Array.prototype.slice.call(document.querySelectorAll('.auth-provider-grid, .auth-provider-row')).forEach(function (row) {
      var hasVisible = Array.prototype.slice
        .call(row.querySelectorAll('[data-auth-provider]'))
        .some(function (btn) {
          return btn.style.display !== 'none';
        });
      if (!hasVisible) {
        row.style.display = 'none';
      }
    });
  }
})();
