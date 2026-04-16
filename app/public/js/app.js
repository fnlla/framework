(function () {
    var storageKey = 'finella-theme';
    var body = document.body;
    var toggle = document.getElementById('themeToggle');
    if (!toggle) {
        return;
    }

    var apply = function (mode) {
        if (mode === 'dark') {
            body.classList.add('theme-dark');
            toggle.textContent = 'Light mode';
            toggle.setAttribute('aria-pressed', 'true');
        } else {
            body.classList.remove('theme-dark');
            toggle.textContent = 'Dark mode';
            toggle.setAttribute('aria-pressed', 'false');
        }
    };

    var stored = localStorage.getItem(storageKey);
    if (stored) {
        apply(stored);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        apply('dark');
    }

    toggle.addEventListener('click', function () {
        var next = body.classList.contains('theme-dark') ? 'light' : 'dark';
        apply(next);
        localStorage.setItem(storageKey, next);
    });
})();

(function () {
    var filter = document.querySelector('[data-docs-filter]');
    var list = document.querySelector('[data-docs-list]');
    if (!filter || !list) {
        return;
    }

    var links = Array.prototype.slice.call(list.querySelectorAll('[data-doc-title]'));
    var normalize = function (value) {
        return String(value || '').toLowerCase();
    };

    filter.addEventListener('input', function () {
        var query = normalize(filter.value);
        links.forEach(function (link) {
            var title = normalize(link.getAttribute('data-doc-title'));
            var match = title.indexOf(query) !== -1;
            var item = link.closest('li');
            if (item) {
                item.style.display = match ? '' : 'none';
            }
        });
    });
})();

(function () {
    if (window.hljs && document.querySelector('.doc-markdown pre code')) {
        window.hljs.highlightAll();
    }
})();

(function () {
    var root = document.querySelector('[data-tabs]');
    if (!root) {
        return;
    }
    var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-tab]'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('[data-panel]'));
    if (buttons.length === 0 || panels.length === 0) {
        return;
    }

    var activate = function (name) {
        buttons.forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-tab') === name);
        });
        panels.forEach(function (panel) {
            panel.classList.toggle('is-active', panel.getAttribute('data-panel') === name);
        });
    };

    var query = '';
    try {
        query = new URLSearchParams(window.location.search).get('tab') || '';
    } catch (e) {
        query = '';
    }
    if (query) {
        activate(query);
    }

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            activate(btn.getAttribute('data-tab'));
        });
    });
})();
