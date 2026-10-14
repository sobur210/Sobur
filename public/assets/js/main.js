/* Sobur — progressive enhancement. No dependencies. */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---- Mobile navigation ---- */
    var toggle = document.querySelector('.nav-toggle');
    var navLinks = document.getElementById('primary-nav');

    function closeNav() {
        if (!toggle || !navLinks) return;
        toggle.setAttribute('aria-expanded', 'false');
        navLinks.classList.remove('is-open');
    }

    if (toggle && navLinks) {
        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
            navLinks.classList.toggle('is-open', !open);
        });

        navLinks.addEventListener('click', function (e) {
            if (e.target.closest('a')) closeNav();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeNav();
        });

        document.addEventListener('click', function (e) {
            if (!navLinks.contains(e.target) && !toggle.contains(e.target)) closeNav();
        });
    }

    /* ---- Header shadow on scroll ---- */
    var header = document.querySelector('.site-header');
    if (header) {
        var onScroll = function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    }

    /* ---- Reveal on scroll ---- */
    var reveals = document.querySelectorAll('.reveal');
    if (reveals.length && !reduceMotion && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(function (el) { io.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* ---- Contact form (fetch, graceful no-JS fallback) ---- */
    var form = document.querySelector('[data-contact-form]');
    if (form) {
        var status = form.querySelector('[data-form-status]');

        var setStatus = function (message, ok) {
            if (!status) return;
            status.textContent = message;
            status.className = 'form-status ' + (ok ? 'form-status--ok' : 'form-status--err');
        };

        form.addEventListener('submit', function (e) {
            if (!window.fetch) return; /* let the browser do a normal submit */
            e.preventDefault();

            var submitBtn = form.querySelector('[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;
            setStatus('Sending…', true);

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
                .then(function (res) { return res.json().catch(function () { return { ok: false, message: 'Unexpected server response.' }; }); })
                .then(function (data) {
                    setStatus(data.message || (data.ok ? 'Message sent.' : 'Something went wrong.'), !!data.ok);
                    if (data.ok) form.reset();
                })
                .catch(function () {
                    setStatus('Network error. Please try again.', false);
                })
                .finally(function () {
                    if (submitBtn) submitBtn.disabled = false;
                });
        });
    }
})();
