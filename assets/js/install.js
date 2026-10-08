// "Add the site to your home screen", on phones and tablets.
//
// - Android (Chrome, Edge, Samsung Internet…): the browser fires
//   beforeinstallprompt once the site is installable; we keep it and show our
//   own banner with an "Installer" button instead of waiting for the browser's
//   own, rarely shown, mini-infobar.
// - iPhone / iPad: Safari never offers installation by itself, so the banner
//   explains the two taps (Share, then "Sur l'écran d'accueil").
// Dismissing the banner hides it for 30 days on this device.

const DISMISS_KEY = 'install-banner-dismissed-at';
const DISMISS_DAYS = 30;

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('/sw.js').catch(function () {
            // No service worker (private mode, old browser): the site works as before.
        });
    });
}

const isInstalled = function () {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.matchMedia('(display-mode: minimal-ui)').matches
        || window.navigator.standalone === true;
};

const isIos = function () {
    return /iphone|ipad|ipod/i.test(navigator.userAgent)
        // iPadOS presents itself as a Mac.
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
};

const isSmallTouchScreen = function () {
    return window.matchMedia('(max-width: 1024px) and (pointer: coarse)').matches;
};

const wasDismissedRecently = function () {
    try {
        const dismissedAt = parseInt(window.localStorage.getItem(DISMISS_KEY), 10);
        return dismissedAt && Date.now() - dismissedAt < DISMISS_DAYS * 24 * 3600 * 1000;
    } catch (e) {
        return false;
    }
};

const rememberDismissal = function () {
    try {
        window.localStorage.setItem(DISMISS_KEY, String(Date.now()));
    } catch (e) {
        // Storage blocked: the banner will simply come back next visit.
    }
};

// Not on Josette's back office or login pages.
const isPublicPage = function () {
    return !/^\/(admin|login|reset-password)/.test(window.location.pathname);
};

const shareIcon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M8 7l4-4 4 4"/><path d="M6 11H5a1 1 0 0 0-1 1v8a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-8a1 1 0 0 0-1-1h-1"/></svg>';

const showBanner = function (onInstall) {
    if (document.querySelector('.install-banner')) {
        return;
    }

    const banner = document.createElement('div');
    banner.className = 'install-banner';
    banner.setAttribute('role', 'dialog');
    banner.setAttribute('aria-label', 'Ajouter le site à l’écran d’accueil');
    banner.innerHTML =
        '<img class="install-banner-icon" src="/apple-touch-icon.png" alt="" width="48" height="48">' +
        '<div class="install-banner-text">' +
            '<strong>Les Poteries de Josette</strong>' +
            (onInstall
                ? '<span>Ajoutez le site à votre écran d’accueil.</span>'
                : '<span>Pour l’ajouter à l’écran d’accueil : touchez ' + shareIcon + ' puis « Sur l’écran d’accueil ».</span>') +
        '</div>' +
        '<div class="install-banner-actions">' +
            (onInstall ? '<button type="button" class="install-banner-install">Installer</button>' : '') +
            '<button type="button" class="install-banner-close" aria-label="Fermer">&times;</button>' +
        '</div>';

    const hide = function () {
        banner.classList.remove('visible');
        setTimeout(function () { banner.remove(); }, 300);
    };

    banner.querySelector('.install-banner-close').addEventListener('click', function () {
        rememberDismissal();
        hide();
    });

    if (onInstall) {
        banner.querySelector('.install-banner-install').addEventListener('click', function () {
            hide();
            onInstall();
        });
    }

    document.body.appendChild(banner);
    // Next frame, so the slide-in transition runs.
    requestAnimationFrame(function () { banner.classList.add('visible'); });
};

if (!isInstalled() && isPublicPage() && !wasDismissedRecently()) {
    window.addEventListener('beforeinstallprompt', function (event) {
        // Keep the browser's prompt for our "Installer" button.
        event.preventDefault();
        if (!isSmallTouchScreen()) {
            return;
        }
        const promptEvent = event;
        setTimeout(function () {
            showBanner(function () {
                promptEvent.prompt();
                promptEvent.userChoice.then(function (choice) {
                    if (choice.outcome === 'dismissed') {
                        rememberDismissal();
                    }
                });
            });
        }, 3000);
    });

    if (isIos() && isSmallTouchScreen()) {
        setTimeout(function () { showBanner(null); }, 3000);
    }
}

window.addEventListener('appinstalled', function () {
    rememberDismissal();
    const banner = document.querySelector('.install-banner');
    if (banner) {
        banner.remove();
    }
});
