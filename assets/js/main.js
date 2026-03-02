'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var siteHeader = document.querySelector('.site-header');
    var scrollTopBtn = document.querySelector('[data-scroll-top]');

    if (siteHeader) {
        var lastY = window.scrollY || window.pageYOffset || 0;
        var navHidden = false;
        var tickScheduled = false;
        var scrollDelta = 6;
        var hideStartY = 120;

        var applyNavVisibility = function (y) {
            if (y <= 10) {
                navHidden = false;
            } else if (y > lastY + scrollDelta && y > hideStartY) {
                navHidden = true;
            } else if (y < lastY - scrollDelta) {
                navHidden = false;
            }

            siteHeader.classList.toggle('is-hidden', navHidden);
            if (scrollTopBtn) {
                scrollTopBtn.classList.toggle('is-visible', navHidden);
            }

            lastY = y;
        };

        applyNavVisibility(lastY);

        window.addEventListener('scroll', function () {
            if (tickScheduled) return;
            tickScheduled = true;

            window.requestAnimationFrame(function () {
                applyNavVisibility(window.scrollY || window.pageYOffset || 0);
                tickScheduled = false;
            });
        }, { passive: true });
    }

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }

    var tubelightNav = document.querySelector('[data-tubelight-nav]');
    if (tubelightNav) {
        var tubeIndicator = tubelightNav.querySelector('[data-tube-indicator]');
        var tubeLinks = Array.prototype.slice.call(tubelightNav.querySelectorAll('[data-tube-link]'));
        var activeLink = tubelightNav.querySelector('.tube-link.active') || tubeLinks[0] || null;

        var moveIndicator = function (target) {
            if (!tubeIndicator || !target) return;
            var left = target.offsetLeft;
            var top = target.offsetTop;

            tubeIndicator.style.width = target.offsetWidth + 'px';
            tubeIndicator.style.height = target.offsetHeight + 'px';
            tubeIndicator.style.transform = 'translate3d(' + left + 'px,' + top + 'px,0)';
            tubeIndicator.style.opacity = '1';
        };

        var restoreActive = function () {
            if (!activeLink) return;
            moveIndicator(activeLink);
        };

        var restoreAfterLayout = function () {
            window.requestAnimationFrame(function () {
                window.requestAnimationFrame(restoreActive);
            });
        };

        tubeLinks.forEach(function (link) {
            link.addEventListener('mouseenter', function () {
                moveIndicator(link);
            });

            link.addEventListener('focus', function () {
                moveIndicator(link);
            });

            link.addEventListener('click', function () {
                activeLink = link;
            });
        });

        tubelightNav.addEventListener('mouseleave', restoreActive);
        tubelightNav.addEventListener('scroll', restoreActive, { passive: true });

        window.addEventListener('resize', restoreAfterLayout);
        window.addEventListener('load', restoreAfterLayout);
        window.addEventListener('pageshow', restoreAfterLayout);

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(restoreAfterLayout);
        }

        restoreAfterLayout();
        window.setTimeout(function () {
            restoreActive();
            tubelightNav.classList.add('is-ready');
        }, 80);
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
