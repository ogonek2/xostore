/**
 * Desktop: single full-width mega layer, content swaps on hover.
 * Mobile: burger drawer with utilities + catalog accordion.
 */
export function initHeaderNav() {
    const root = document.querySelector('[data-shop-nav]');

    if (!root) {
        return;
    }

    initDesktopMega(root);
    initMobileNav(root);
}

function initDesktopMega(root) {
    const layer = root.querySelector('[data-mega-layer]');
    const triggers = root.querySelectorAll('[data-mega-trigger]');
    const panels = root.querySelectorAll('[data-mega-panel]');

    if (!layer || triggers.length === 0 || panels.length === 0) {
        return;
    }

    let activeIndex = null;
    let closeTimer = null;

    const open = (index) => {
        clearTimeout(closeTimer);
        activeIndex = String(index);

        panels.forEach((panel) => {
            const isActive = panel.dataset.megaPanel === activeIndex;
            panel.classList.toggle('hidden', !isActive);
        });

        triggers.forEach((trigger) => {
            const isActive = trigger.dataset.megaTrigger === activeIndex;
            trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
            trigger.classList.toggle('text-text-muted', isActive);
            trigger.querySelector('.nav-mega-chevron')?.classList.toggle('rotate-180', isActive);
        });

        layer.classList.remove('hidden');
        layer.dataset.open = 'true';
    };

    const close = () => {
        closeTimer = setTimeout(() => {
            activeIndex = null;
            layer.classList.add('hidden');
            layer.dataset.open = 'false';

            triggers.forEach((trigger) => {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.classList.remove('text-text-muted');
                trigger.querySelector('.nav-mega-chevron')?.classList.remove('rotate-180');
            });
        }, 120);
    };

    const cancelClose = () => clearTimeout(closeTimer);

    triggers.forEach((trigger) => {
        trigger.addEventListener('mouseenter', () => open(trigger.dataset.megaTrigger));
        trigger.addEventListener('focus', () => open(trigger.dataset.megaTrigger));
    });

    root.addEventListener('mouseenter', cancelClose);
    root.addEventListener('mouseleave', close);

    layer.addEventListener('mouseenter', cancelClose);
    layer.addEventListener('mouseleave', close);
}

function initMobileNav(root) {
    const toggle = root.querySelector('[data-mobile-nav-toggle]');
    const drawer = root.querySelector('[data-mobile-nav-drawer]');

    if (!toggle || !drawer) {
        return;
    }

    if (drawer.parentElement !== document.body) {
        document.body.appendChild(drawer);
    }

    const catalogToggle = drawer.querySelector('[data-mobile-catalog-toggle]');
    const catalogPanel = drawer.querySelector('[data-mobile-catalog-panel]');
    const catalogIcon = drawer.querySelector('[data-mobile-catalog-icon]');

    const resetCatalog = () => {
        catalogPanel?.classList.add('hidden');
        catalogToggle?.setAttribute('aria-expanded', 'false');
        catalogIcon?.classList.remove('rotate-180');
    };

    const closeDrawer = () => {
        drawer.classList.add('hidden');
        drawer.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'false');
        resetCatalog();
    };

    const openDrawer = () => {
        drawer.classList.remove('hidden');
        drawer.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        toggle.setAttribute('aria-expanded', 'true');
    };

    toggle.addEventListener('click', (event) => {
        event.stopPropagation();
        if (drawer.classList.contains('hidden')) {
            openDrawer();
        } else {
            closeDrawer();
        }
    });

    drawer.addEventListener('click', (event) => {
        if (event.target.closest('[data-mobile-nav-close]')) {
            event.preventDefault();
            closeDrawer();
            return;
        }

        const actionButton = event.target.closest('[data-mobile-nav-action]');

        if (actionButton) {
            event.preventDefault();
            const action = actionButton.dataset.mobileNavAction;
            closeDrawer();

            if (action === 'cart') {
                window.dispatchEvent(new Event('cart:open'));
            } else if (action === 'search') {
                root.querySelector('[data-search-toggle]')?.click();
            }

            return;
        }

        if (event.target.closest('[data-mobile-nav-link]')) {
            closeDrawer();
        }
    });

    catalogToggle?.addEventListener('click', (event) => {
        event.stopPropagation();

        if (!catalogPanel) {
            return;
        }

        const willOpen = catalogPanel.classList.contains('hidden');
        catalogPanel.classList.toggle('hidden', !willOpen);
        catalogToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        catalogIcon?.classList.toggle('rotate-180', willOpen);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !drawer.classList.contains('hidden')) {
            closeDrawer();
        }
    });
}
