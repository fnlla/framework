(function () {
  var cycleButton = document.querySelector('[data-badge-cycle]');
  if (cycleButton) {
    var statuses = [
      { label: 'Planned', className: 'badge-neutral' },
      { label: 'In progress', className: 'badge-warning' },
      { label: 'Review', className: 'badge-soft' },
      { label: 'Live', className: 'badge-success' },
    ];

    var items = Array.prototype.slice.call(document.querySelectorAll('[data-badge-status]'));
    var index = 0;

    function applyStatus() {
      var status = statuses[index % statuses.length];
      items.forEach(function (item) {
        item.textContent = status.label;
        item.classList.remove('badge-neutral', 'badge-warning', 'badge-soft', 'badge-success');
        item.classList.add(status.className);
      });
    }

    cycleButton.addEventListener('click', function () {
      index += 1;
      applyStatus();
    });

    applyStatus();
  }
})();

(function () {
  var density = document.querySelector('.tables-density');
  if (!density) return;
  var buttons = density.querySelectorAll('[data-toggle-item]');
  var tables = document.querySelectorAll('.tables-table');

  function apply(mode) {
    tables.forEach(function (table) {
      table.classList.toggle('is-compact', mode === 'compact');
    });
  }

  buttons.forEach(function (button) {
    button.addEventListener('click', function () {
      var label = button.textContent.toLowerCase();
      apply(label.indexOf('compact') !== -1 ? 'compact' : 'comfortable');
    });
  });
})();

(function () {
  var checks = Array.prototype.slice.call(document.querySelectorAll('.list-check'));
  checks.forEach(function (row) {
    var input = row.querySelector('input[type="checkbox"]');
    if (!input) return;
    function sync() {
      row.classList.toggle('is-checked', input.checked);
    }
    input.addEventListener('change', sync);
    sync();
  });
})();

(function () {
  var dropdowns = Array.prototype.slice.call(document.querySelectorAll('[data-dropdown]'));

  function closeAll(except) {
    dropdowns.forEach(function (dropdown) {
      if (dropdown !== except) {
        dropdown.classList.remove('is-open');
      }
    });
  }

  dropdowns.forEach(function (dropdown) {
    var toggle = dropdown.querySelector('[data-dropdown-toggle]');
    var items = dropdown.querySelectorAll('[data-dropdown-item]');
    if (!toggle) return;

    toggle.addEventListener('click', function (event) {
      event.stopPropagation();
      var isOpen = dropdown.classList.contains('is-open');
      closeAll();
      dropdown.classList.toggle('is-open', !isOpen);
    });

    items.forEach(function (item) {
      item.addEventListener('click', function () {
        toggle.textContent = item.textContent;
        dropdown.classList.remove('is-open');
      });
    });
  });

  document.addEventListener('click', function () {
    closeAll();
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeAll();
    }
  });
})();

(function () {
  function openModal(id) {
    var modal = document.querySelector('[data-modal="' + id + '"]');
    if (modal) modal.classList.add('is-open');
  }

  function closeModal(modal) {
    modal.classList.remove('is-open');
  }

  document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openModal(btn.getAttribute('data-modal-open'));
    });
  });

  document.querySelectorAll('[data-modal]').forEach(function (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) {
        closeModal(modal);
      }
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var modal = btn.closest('[data-modal]');
      if (modal) closeModal(modal);
    });
  });
})();
