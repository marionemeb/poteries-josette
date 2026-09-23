// any CSS you require will output into a single css file (app.css in this case)
require('../scss/app.scss');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
require('jquery');
require('bootstrap');

const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.getElementById('navbarToggleExternalContent');
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
        if (event.key === 'Escape') {
            setMenuOpen(false);
        }
    });
}

document.querySelectorAll('.category-filter-input').forEach(function (input) {
    input.addEventListener('change', function () {
        input.form.submit();
    });
});

const backToTopButton = document.getElementById('back-to-top');
if (backToTopButton) {
    window.addEventListener('scroll', function () {
        backToTopButton.classList.toggle('visible', window.scrollY > 400);
    });

    backToTopButton.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}

