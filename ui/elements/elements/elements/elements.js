(function () {
  var nodes = document.querySelectorAll('[data-current-year]');
  var year = String(new Date().getFullYear());
  nodes.forEach(function (node) {
    node.textContent = year;
  });

  document.querySelectorAll('[data-nav-group]').forEach(function (nav) {
    var activeClass = nav.getAttribute('data-active-class') || 'is-active';
    nav.addEventListener('click', function (event) {
      var link = event.target.closest('a');
      if (!link || !nav.contains(link)) return;
      nav.querySelectorAll('a').forEach(function (item) {
        item.classList.remove(activeClass);
      });
      link.classList.add(activeClass);
    });
  });

  document.querySelectorAll('[data-toggle-group]').forEach(function (group) {
    group.addEventListener('click', function (event) {
      var button = event.target.closest('[data-toggle-item]');
      if (!button || !group.contains(button)) return;
      group.querySelectorAll('[data-toggle-item]').forEach(function (item) {
        item.classList.remove('is-active');
      });
      button.classList.add('is-active');
    });
  });

  var pricingToggle = document.querySelector('[data-pricing-toggle]');
  if (pricingToggle) {
    var pricingButtons = pricingToggle.querySelectorAll('[data-pricing-cycle]');
    var priceValues = document.querySelectorAll('[data-price-monthly]');
    var priceCycles = document.querySelectorAll('[data-cycle-monthly]');

    function applyPricing(mode) {
      pricingButtons.forEach(function (button) {
        var isActive = button.getAttribute('data-pricing-cycle') === mode;
        button.classList.toggle('is-active', isActive);
      });

      priceValues.forEach(function (node) {
        var value = node.getAttribute(mode === 'annual' ? 'data-price-annual' : 'data-price-monthly');
        if (value) {
          node.textContent = value;
        }
      });

      priceCycles.forEach(function (node) {
        var value = node.getAttribute(mode === 'annual' ? 'data-cycle-annual' : 'data-cycle-monthly');
        if (value) {
          node.textContent = value;
        }
      });
    }

    pricingButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var mode = button.getAttribute('data-pricing-cycle') || 'monthly';
        applyPricing(mode);
      });
    });

    applyPricing('monthly');
  }

  document.querySelectorAll('[data-radio-card]').forEach(function (row) {
    var input = row.querySelector('input[type="radio"]');
    if (!input) return;
    function sync() {
      row.classList.toggle('is-selected', input.checked);
    }
    input.addEventListener('change', sync);
    sync();
  });

  function formatCount(value, decimals) {
    var formatted = value.toFixed(decimals);
    var parts = formatted.split('.');
    parts[0] = Number(parts[0]).toLocaleString();
    return parts.join(decimals > 0 ? '.' : '');
  }

  document.querySelectorAll('[data-countup]').forEach(function (node) {
    var target = parseFloat(node.getAttribute('data-countup') || '0');
    var suffix = node.getAttribute('data-countup-suffix') || '';
    var prefix = node.getAttribute('data-countup-prefix') || '';
    var duration = parseInt(node.getAttribute('data-countup-duration') || '800', 10);
    var decimals = Number.isInteger(target) ? 0 : 1;
    var start = null;

    function step(timestamp) {
      if (!start) start = timestamp;
      var progress = Math.min((timestamp - start) / duration, 1);
      var value = target * progress;
      node.textContent = prefix + formatCount(value, decimals) + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      }
    }

    requestAnimationFrame(step);
  });

  document.querySelectorAll('[data-progress]').forEach(function (bar) {
    var value = parseFloat(bar.getAttribute('data-progress') || '0');
    var fill = bar.querySelector('.fx-progress-bar');
    if (fill) {
      fill.style.width = Math.max(0, Math.min(100, value)) + '%';
    }
  });

  document.querySelectorAll('[data-accordion]').forEach(function (accordion) {
    accordion.querySelectorAll('[data-accordion-toggle]').forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        var item = toggle.closest('.accordion-item');
        if (!item) return;
        item.classList.toggle('is-open');
      });
    });
  });

  document.querySelectorAll('.fx-code').forEach(function (block) {
    var header = block.querySelector('.fx-code-header');
    if (!header) return;
    if (!header.querySelector('[data-code-collapse]')) {
      var actions = header.querySelector('.fx-code-actions');
      if (!actions) {
        actions = document.createElement('div');
        actions.className = 'fx-code-actions';
        header.appendChild(actions);
        header.querySelectorAll('button').forEach(function (button) {
          actions.appendChild(button);
        });
      }
      var collapseButton = document.createElement('button');
      collapseButton.className = 'f-btn f-btn-outline f-btn-sm';
      collapseButton.setAttribute('type', 'button');
      collapseButton.setAttribute('data-code-collapse', '1');
      collapseButton.textContent = 'Hide code';
      actions.appendChild(collapseButton);
      collapseButton.addEventListener('click', function () {
        var collapsed = block.classList.toggle('is-collapsed');
        collapseButton.textContent = collapsed ? 'Show code' : 'Hide code';
      });
    }
  });\ndocument.querySelectorAll('[data-copy-target]').forEach(function (button) {
    button.addEventListener('click', function () {
      var targetId = button.getAttribute('data-copy-target');
      if (!targetId) return;
      var target = document.getElementById(targetId);
      if (!target) return;
      var text = target.textContent || '';
      navigator.clipboard.writeText(text).then(function () {
        var original = button.textContent;
        button.textContent = 'Copied';
        setTimeout(function () {
          button.textContent = original;
        }, 1200);
      }).catch(function () {
        // noop fallback
      });
    });
  });
})();







