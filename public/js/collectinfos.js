document.addEventListener('DOMContentLoaded', function () {
    // Sélecteur de langue
    const langSelect = document.getElementById('lang-select');
    const langForm = document.querySelector('.lang-switcher-form');

    if (langSelect && langForm) {
        langSelect.addEventListener('change', function () {
            const base = langForm.dataset.localeBase || '/locale';
            langForm.action = base.replace(/\/$/, '') + '/' + this.value;
            langForm.submit();
        });
    }

    // Menu thématique
    const thematiqueWrap = document.getElementById('thematique-wrap');
    const thematiqueBtn = document.getElementById('thematique-btn');

    if (thematiqueBtn) {
        thematiqueBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            thematiqueWrap.classList.toggle('open');
            thematiqueBtn.setAttribute('aria-expanded', thematiqueWrap.classList.contains('open'));
        });

        document.addEventListener('click', function () {
            thematiqueWrap.classList.remove('open');
            thematiqueBtn.setAttribute('aria-expanded', 'false');
        });
    }

    // Menu mobile
    const mobileToggle = document.getElementById('mobile-toggle');
    const mobileNav = document.getElementById('mobile-nav');

    if (mobileToggle && mobileNav) {
        mobileToggle.addEventListener('click', function () {
            const isOpen = mobileNav.classList.toggle('open');
            const icon = mobileToggle.querySelector('i');

            mobileToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

            if (icon) {
                icon.classList.toggle('fa-bars', !isOpen);
                icon.classList.toggle('fa-xmark', isOpen);
            }
        });
    }

    // Scroll to top
    const scrollTop = document.getElementById('scroll-top');

    if (scrollTop) {
        window.addEventListener('scroll', function () {
            scrollTop.classList.toggle('visible', window.scrollY > 400);
        });

        scrollTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Compteurs animés
    const counters = document.querySelectorAll('.counter[data-target]');

    if (counters.length && 'IntersectionObserver' in window) {
        const animateCounter = function (el) {
            const target = parseInt(el.dataset.target, 10);
            const duration = 1500;
            const start = performance.now();

            function update(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                el.textContent = Math.floor(eased * target);
                if (progress < 1) requestAnimationFrame(update);
                else el.textContent = target;
            }

            requestAnimationFrame(update);
        };

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(function (c) { observer.observe(c); });
    }

    // Aperçu contenus — popup globale
    const previewModal = document.getElementById('preview-modal');
    let activePreview = null;

    function stopPreview() {
        if (! activePreview || ! previewModal) return;

        clearInterval(activePreview.intervalId);
        clearTimeout(activePreview.timeoutId);

        if (activePreview.media) {
            if (activePreview.media.tagName === 'VIDEO' || activePreview.media.tagName === 'AUDIO') {
                activePreview.media.pause();
                activePreview.media.removeAttribute('src');
                activePreview.media.load();
            }
            activePreview.media.remove();
        }

        previewModal.querySelector('.preview-body').innerHTML = '';
        previewModal.querySelector('.preview-progress-bar').style.width = '100%';
        previewModal.hidden = true;
        document.body.classList.remove('preview-open');
        activePreview = null;
    }

    function openPreview(trigger) {
        if (! previewModal || ! trigger) return;

        stopPreview();

        const mode = trigger.dataset.previewMode || 'text';
        const seconds = parseInt(trigger.dataset.previewSeconds, 10) || 15;
        const title = trigger.dataset.previewTitle || 'Aperçu';
        const body = previewModal.querySelector('.preview-body');
        const countdownEl = previewModal.querySelector('.preview-countdown');
        const progressBar = previewModal.querySelector('.preview-progress-bar');
        const titleEl = previewModal.querySelector('.preview-modal-title');
        let media = null;

        titleEl.textContent = title;
        previewModal.hidden = false;
        document.body.classList.add('preview-open');

        if (mode === 'youtube' && trigger.dataset.previewEmbed) {
            media = document.createElement('iframe');
            media.src = trigger.dataset.previewEmbed;
            media.allow = 'autoplay; encrypted-media';
            media.title = title;
            body.appendChild(media);
        } else if (mode === 'video' && trigger.dataset.previewUrl) {
            media = document.createElement('video');
            media.src = trigger.dataset.previewUrl;
            media.muted = true;
            media.playsInline = true;
            media.autoplay = true;
            media.controls = true;
            body.appendChild(media);
            media.play().catch(function () {});

            media.addEventListener('timeupdate', function () {
                if (media.currentTime >= seconds) stopPreview();
            });
        } else if (mode === 'audio' && trigger.dataset.previewUrl) {
            media = document.createElement('audio');
            media.src = trigger.dataset.previewUrl;
            media.autoplay = true;
            media.controls = true;
            body.appendChild(media);
            media.play().catch(function () {});

            media.addEventListener('timeupdate', function () {
                if (media.currentTime >= seconds) stopPreview();
            });
        } else {
            const text = document.createElement('div');
            text.className = 'preview-text';
            text.textContent = trigger.dataset.previewText || 'Aperçu non disponible.';
            body.appendChild(text);
        }

        let remaining = seconds;
        countdownEl.textContent = remaining + ' s';
        progressBar.style.width = '100%';
        progressBar.style.transition = 'width ' + seconds + 's linear';
        requestAnimationFrame(function () {
            progressBar.style.width = '0%';
        });

        const intervalId = setInterval(function () {
            remaining -= 1;
            countdownEl.textContent = Math.max(0, remaining) + ' s';
        }, 1000);

        const timeoutId = setTimeout(stopPreview, seconds * 1000);

        activePreview = { intervalId, timeoutId, media };
    }

    if (previewModal) {
        document.querySelectorAll('.action-preview').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openPreview(btn);
            });
        });

        previewModal.querySelector('.preview-close').addEventListener('click', stopPreview);
        previewModal.querySelector('.preview-modal-backdrop').addEventListener('click', stopPreview);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') stopPreview();
        });
    }

    // Curseur expérience entreprise (Relations presse)
    document.querySelectorAll('[data-range-output]').forEach(function (input) {
        const outputId = input.getAttribute('data-range-output');
        const output = outputId ? document.getElementById(outputId) : null;

        function updateRangeLabel() {
            if (!output) return;
            const value = parseInt(input.value, 10) || 0;
            output.textContent = value >= 10 ? '10 ans et plus' : String(value);
        }

        input.addEventListener('input', updateRangeLabel);
        updateRangeLabel();
    });

    // Thématique « Autre » (Relations presse)
    document.querySelectorAll('[data-toggle-other]').forEach(function (checkbox) {
        const targetId = checkbox.getAttribute('data-toggle-other');
        const target = targetId ? document.getElementById(targetId) : null;

        if (!target) return;

        function syncOtherField() {
            target.hidden = !checkbox.checked;
        }

        checkbox.addEventListener('change', syncOtherField);
        syncOtherField();
    });
});
