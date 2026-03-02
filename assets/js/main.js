'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var toggleBtn = document.querySelector('[data-menu-toggle]');
    var menu = document.querySelector('[data-main-nav]');
    if (toggleBtn && menu) {
        toggleBtn.addEventListener('click', function () {
            menu.classList.toggle('open');
        });
    }

    var countdownRoot = document.querySelector('[data-countdown]');
    if (countdownRoot && window.GD2026_EVENT_START) {
        var targetDate = new Date(window.GD2026_EVENT_START).getTime();
        var daysNode = countdownRoot.querySelector('[data-days]');
        var hoursNode = countdownRoot.querySelector('[data-hours]');
        var minsNode = countdownRoot.querySelector('[data-minutes]');
        var secsNode = countdownRoot.querySelector('[data-seconds]');

        var tick = function () {
            var now = Date.now();
            var delta = Math.max(0, targetDate - now);

            var days = Math.floor(delta / (1000 * 60 * 60 * 24));
            var hours = Math.floor((delta / (1000 * 60 * 60)) % 24);
            var minutes = Math.floor((delta / (1000 * 60)) % 60);
            var seconds = Math.floor((delta / 1000) % 60);

            if (daysNode) daysNode.textContent = String(days);
            if (hoursNode) hoursNode.textContent = String(hours).padStart(2, '0');
            if (minsNode) minsNode.textContent = String(minutes).padStart(2, '0');
            if (secsNode) secsNode.textContent = String(seconds).padStart(2, '0');
        };

        tick();
        window.setInterval(tick, 1000);
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var errors = [];

            form.querySelectorAll('[data-required]').forEach(function (field) {
                if (String(field.value || '').trim() === '') {
                    errors.push(field.getAttribute('data-label') || field.name);
                    field.classList.add('invalid');
                } else {
                    field.classList.remove('invalid');
                }
            });

            form.querySelectorAll('[data-email]').forEach(function (field) {
                var value = String(field.value || '').trim();
                if (value !== '' && !/^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/.test(value)) {
                    errors.push(field.getAttribute('data-label') || field.name);
                    field.classList.add('invalid');
                } else {
                    field.classList.remove('invalid');
                }
            });

            var consent = form.querySelector('[data-gdpr]');
            if (consent && !consent.checked) {
                errors.push(consent.getAttribute('data-label') || 'RGPD');
            }

            if (errors.length > 0) {
                event.preventDefault();
                var prefix = window.GD2026_VALIDATE_PREFIX || 'Please check the form';
                alert(prefix + ': ' + errors.join(', '));
            }
        });
    });
});
