// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
require('jquery');
require('bootstrap');
require('./install');

/* Header: transparent over the photo, dark bar once the page scrolls. */
const header = document.getElementById('site-header');
if (header) {
    const updateHeader = function () {
        header.classList.toggle('is-scrolled', window.scrollY > 40);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
}

/* Burger menu (below 1200px): full-screen overlay. */
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.getElementById('site-nav');
if (navToggle && navMenu) {
    const setMenuOpen = function (open) {
        navMenu.classList.toggle('show', open);
        document.body.classList.toggle('menu-open', open);
        navToggle.setAttribute('aria-expanded', open);
        navToggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
    };

    navToggle.addEventListener('click', function () {
        setMenuOpen(!navMenu.classList.contains('show'));
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && navMenu.classList.contains('show')) {
            setMenuOpen(false);
            navToggle.focus();
        }
    });

    // Back to the desktop bar: the overlay must not stay open behind it.
    window.matchMedia('(min-width: 1200px)').addEventListener('change', function (event) {
        if (event.matches) {
            setMenuOpen(false);
        }
    });
}

/* Category filter (blog, recipes): submitted as soon as a pill is picked. */
document.querySelectorAll('.category-filter-input').forEach(function (input) {
    input.addEventListener('change', function () {
        input.form.submit();
    });
});

/* Back to top button. */
const backToTopButton = document.getElementById('back-to-top');
if (backToTopButton) {
    window.addEventListener('scroll', function () {
        backToTopButton.classList.toggle('visible', window.scrollY > 400);
    }, { passive: true });

    backToTopButton.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

/* Photo viewer of /articles: previous/next, keyboard arrows, swipe. */
const lightbox = document.getElementById('image-lightbox');
if (lightbox) {
    const image = lightbox.querySelector('img');
    const title = lightbox.querySelector('.lightbox-title');
    const counter = lightbox.querySelector('.lightbox-counter');
    const closeButton = lightbox.querySelector('.lightbox-close');
    let triggers = [];
    let current = 0;
    let opener = null;

    const show = function (index) {
        // Cards whose photo failed to load have removed themselves meanwhile.
        triggers = Array.from(document.querySelectorAll('[data-lightbox]'));
        if (triggers.length === 0) {
            return;
        }
        current = (index + triggers.length) % triggers.length;
        const trigger = triggers[current];
        const thumbnail = trigger.querySelector('img');
        image.src = thumbnail.currentSrc || thumbnail.src;
        image.alt = thumbnail.alt;
        title.textContent = trigger.dataset.lightbox;
        counter.textContent = (current + 1) + ' / ' + triggers.length;
        lightbox.querySelectorAll('.lightbox-prev, .lightbox-next').forEach(function (button) {
            button.hidden = triggers.length < 2;
        });
    };

    const open = function (trigger) {
        opener = trigger;
        show(Array.from(document.querySelectorAll('[data-lightbox]')).indexOf(trigger));
        lightbox.hidden = false;
        document.body.classList.add('lightbox-open');
        closeButton.focus();
    };

    const close = function () {
        lightbox.hidden = true;
        document.body.classList.remove('lightbox-open');
        if (opener) {
            opener.focus();
        }
    };

    document.addEventListener('click', function (event) {
        const trigger = event.target.closest('[data-lightbox]');
        if (trigger) {
            open(trigger);
        }
    });

    lightbox.querySelector('.lightbox-prev').addEventListener('click', function () { show(current - 1); });
    lightbox.querySelector('.lightbox-next').addEventListener('click', function () { show(current + 1); });
    closeButton.addEventListener('click', close);

    // A click on the dark background (not on the photo or a button) closes it.
    lightbox.addEventListener('click', function (event) {
        if (event.target === lightbox) {
            close();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (lightbox.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'ArrowLeft') {
            show(current - 1);
        } else if (event.key === 'ArrowRight') {
            show(current + 1);
        }
    });

    let touchStartX = null;
    lightbox.addEventListener('touchstart', function (event) {
        touchStartX = event.touches[0].clientX;
    }, { passive: true });
    lightbox.addEventListener('touchend', function (event) {
        if (touchStartX === null) {
            return;
        }
        const distance = event.changedTouches[0].clientX - touchStartX;
        if (Math.abs(distance) > 50) {
            show(current + (distance < 0 ? 1 : -1));
        }
        touchStartX = null;
    });
}
