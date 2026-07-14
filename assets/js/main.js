'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var siteHeader = document.querySelector('.site-header');
    var scrollTopBtn = document.querySelector('[data-scroll-top]');

    if (window.AOS && document.querySelector('[data-aos]')) {
        window.AOS.init({
            duration: 700,
            easing: 'ease-out-cubic',
            once: true,
            offset: 26
        });
    }

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

        var updateCountdownNode = function (node, nextValue) {
            if (!node || node.textContent === nextValue) return;

            node.classList.remove('is-changing');
            void node.offsetWidth;
            node.textContent = nextValue;
            node.classList.add('is-changing');

            window.setTimeout(function () {
                node.classList.remove('is-changing');
            }, 420);
        };

        var tick = function () {
            var now = Date.now();
            var delta = Math.max(0, targetDate - now);

            var days = Math.floor(delta / (1000 * 60 * 60 * 24));
            var hours = Math.floor((delta / (1000 * 60 * 60)) % 24);
            var minutes = Math.floor((delta / (1000 * 60)) % 60);
            var seconds = Math.floor((delta / 1000) % 60);

            updateCountdownNode(daysNode, String(days));
            updateCountdownNode(hoursNode, String(hours).padStart(2, '0'));
            updateCountdownNode(minsNode, String(minutes).padStart(2, '0'));
            updateCountdownNode(secsNode, String(seconds).padStart(2, '0'));
        };

        tick();
        window.setInterval(tick, 1000);
    }

    var counterNodes = Array.prototype.slice.call(document.querySelectorAll('[data-counter-end]'));
    if (counterNodes.length > 0) {
        var counterFormatValue = function (value, decimals) {
            if (decimals > 0) return String(value.toFixed(decimals));
            return String(Math.round(value));
        };

        var animateCounter = function (node) {
            if (!node || node.getAttribute('data-counter-complete') === '1') return;
            node.setAttribute('data-counter-complete', '1');

            var startValue = parseFloat(node.getAttribute('data-counter-start') || '0');
            var endValue = parseFloat(node.getAttribute('data-counter-end') || '0');
            var duration = parseInt(node.getAttribute('data-counter-duration') || '1400', 10);
            var decimals = parseInt(node.getAttribute('data-counter-decimals') || '0', 10);
            var prefix = node.getAttribute('data-counter-prefix') || '';
            var suffix = node.getAttribute('data-counter-suffix') || '';
            var animationStart = 0;

            if (!isFinite(startValue)) startValue = 0;
            if (!isFinite(endValue)) endValue = 0;
            if (!isFinite(duration) || duration < 200) duration = 1400;
            if (!isFinite(decimals) || decimals < 0) decimals = 0;

            var stepCounter = function (timestamp) {
                if (animationStart === 0) animationStart = timestamp;
                var progress = Math.min(1, (timestamp - animationStart) / duration);
                var easedProgress = 1 - Math.pow(1 - progress, 3);
                var currentValue = startValue + ((endValue - startValue) * easedProgress);

                node.textContent = prefix + counterFormatValue(currentValue, decimals) + suffix;

                if (progress < 1) {
                    window.requestAnimationFrame(stepCounter);
                    return;
                }

                node.textContent = prefix + counterFormatValue(endValue, decimals) + suffix;
            };

            window.requestAnimationFrame(stepCounter);
        };

        if ('IntersectionObserver' in window) {
            var counterObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                });
            }, {
                threshold: 0.45
            });

            counterNodes.forEach(function (node) {
                counterObserver.observe(node);
            });
        } else {
            counterNodes.forEach(function (node) {
                animateCounter(node);
            });
        }
    }

    /* ── Toast notification system ───────────────────────────────────────── */
    function showToast(message, type, duration) {
        type = type || 'error';
        duration = (duration !== undefined) ? duration : (type === 'success' ? 5000 : 8000);

        var container = document.getElementById('gd-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'gd-toast-container';
            document.body.appendChild(container);
        }

        var icons = {
            success: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M6.5 10.5l2.5 2.5 4.5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            error:   '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M7 7l6 6M13 7l-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>',
            warning: '<svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M10 2.5L18.5 17H1.5L10 2.5z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M10 8.5v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="10" cy="14.5" r="1" fill="currentColor"/></svg>'
        };

        var toast = document.createElement('div');
        toast.className = 'gd-toast gd-toast--' + type;
        toast.setAttribute('role', 'alert');
        toast.innerHTML =
            '<span class="gd-toast-icon">' + (icons[type] || icons.error) + '</span>' +
            '<span class="gd-toast-msg">' + message + '</span>' +
            '<button class="gd-toast-close" type="button" aria-label="Fermer">' +
            '<svg viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M2 2l10 10M12 2L2 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>' +
            '</button>' +
            (duration > 0 ? '<div class="gd-toast-bar"><div class="gd-toast-bar-fill" style="animation-duration:' + duration + 'ms"></div></div>' : '');

        container.appendChild(toast);

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                toast.classList.add('gd-toast--in');
            });
        });

        var dismissed = false;
        function dismiss() {
            if (dismissed) return;
            dismissed = true;
            clearTimeout(timer);
            toast.classList.remove('gd-toast--in');
            toast.classList.add('gd-toast--out');
            setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 400);
        }

        toast.querySelector('.gd-toast-close').addEventListener('click', dismiss);

        var timer = duration > 0 ? setTimeout(dismiss, duration) : null;
        if (timer) {
            toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
            toast.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 2000); });
        }
    }

    /* ── Convert server flash alerts to toasts ────────────────────────────── */
    document.querySelectorAll('.alert').forEach(function (el) {
        var type = el.classList.contains('alert-success') ? 'success'
                 : el.classList.contains('alert-warning') ? 'warning'
                 : 'error';
        var msg = (el.textContent || '').trim();
        if (msg) {
            showToast(msg, type);
            el.style.display = 'none';
        }
    });

    /* ── Client-side form validation ──────────────────────────────────────── */
    function setFieldError(field, message) {
        field.classList.add('gd-invalid');
        var wrap = field.closest('.form-group') || field.parentNode;
        var existing = wrap.querySelector('.gd-field-error');
        if (!existing) {
            var err = document.createElement('span');
            err.className = 'gd-field-error';
            err.setAttribute('aria-live', 'polite');
            field.parentNode.insertBefore(err, field.nextSibling);
            existing = err;
        }
        existing.textContent = message;
    }

    function clearFieldError(field) {
        field.classList.remove('gd-invalid');
        var wrap = field.closest('.form-group') || field.parentNode;
        var existing = wrap.querySelector('.gd-field-error');
        if (existing) existing.textContent = '';
    }

    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        /* Clear error on input */
        form.addEventListener('input', function (e) {
            if (e.target.classList.contains('gd-invalid')) {
                clearFieldError(e.target);
            }
        });
        form.addEventListener('change', function (e) {
            if (e.target.classList.contains('gd-invalid')) {
                clearFieldError(e.target);
            }
        });

        form.addEventListener('submit', function (event) {
            var errorCount = 0;
            var firstBad = null;

            form.querySelectorAll('[data-required]').forEach(function (field) {
                if (String(field.value || '').trim() === '') {
                    var label = field.getAttribute('data-label') || field.name;
                    setFieldError(field, label + ' est obligatoire.');
                    errorCount++;
                    if (!firstBad) firstBad = field;
                } else {
                    clearFieldError(field);
                }
            });

            form.querySelectorAll('[data-email]').forEach(function (field) {
                var value = String(field.value || '').trim();
                if (value !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    setFieldError(field, 'Adresse e-mail invalide.');
                    errorCount++;
                    if (!firstBad) firstBad = field;
                } else if (!field.classList.contains('gd-invalid')) {
                    clearFieldError(field);
                }
            });

            var consent = form.querySelector('[data-gdpr]');
            if (consent && !consent.checked) {
                setFieldError(consent, 'Vous devez accepter les conditions.');
                errorCount++;
                if (!firstBad) firstBad = consent;
            } else if (consent) {
                clearFieldError(consent);
            }

            if (errorCount > 0) {
                event.preventDefault();
                var msg = errorCount === 1
                    ? 'Un champ obligatoire est manquant ou invalide.'
                    : errorCount + ' champs obligatoires sont manquants ou invalides.';
                showToast(msg, 'error');
                if (firstBad) {
                    firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(function () { firstBad.focus(); }, 400);
                }
            }
        });
    });

    /* ── Sponsors drag-to-scroll ─────────────────────────────────────────── */
    var spTrack = document.querySelector('.home-sponsors-track-wrap');
    if (spTrack) {
        var spDown = false, spStartX = 0, spScrollLeft = 0;
        spTrack.addEventListener('mousedown', function (e) {
            spDown = true;
            spStartX = e.pageX - spTrack.offsetLeft;
            spScrollLeft = spTrack.scrollLeft;
            spTrack.classList.add('is-dragging');
        });
        spTrack.addEventListener('mouseleave', function () { spDown = false; spTrack.classList.remove('is-dragging'); });
        spTrack.addEventListener('mouseup',    function () { spDown = false; spTrack.classList.remove('is-dragging'); });
        spTrack.addEventListener('mousemove',  function (e) {
            if (!spDown) return;
            e.preventDefault();
            var x = e.pageX - spTrack.offsetLeft;
            spTrack.scrollLeft = spScrollLeft - (x - spStartX) * 1.4;
        });
    }
});
