let cachedProductIds = [];

export function extractProductIds(cart) {
    return cart?.product_ids?.length
        ? cart.product_ids
        : [...new Set((cart?.items ?? []).map((item) => item.product_id).filter(Boolean))];
}

export function applyCartProductBadges(productIds) {
    if (productIds !== undefined) {
        cachedProductIds = [...productIds];
    }

    const set = new Set(cachedProductIds.map(Number));

    document.querySelectorAll('[data-product-card]').forEach((card) => {
        const id = Number(card.dataset.productId);
        const inCart = set.has(id);
        card.classList.toggle('is-in-cart', inCart);
    });

    document.querySelectorAll('.new-arrivals-swiper, .trending-swiper, .categories-swiper').forEach((el) => {
        el.swiper?.update();
    });
}

export function refreshCartProductBadges() {
    applyCartProductBadges();
}

export function dispatchCartState(cart) {
    const productIds = extractProductIds(cart);

    applyCartProductBadges(productIds);

    window.dispatchEvent(
        new CustomEvent('cart:updated', {
            detail: {
                count: cart?.count ?? 0,
                product_ids: productIds,
            },
        })
    );
}

export function initCartBadges() {
    const root = document.getElementById('cart-drawer-root');
    if (!root?.dataset.routes) return;

    const routes = JSON.parse(root.dataset.routes);

    fetch(routes.show, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then((r) => (r.ok ? r.json() : null))
        .then((cart) => {
            if (cart) applyCartProductBadges(extractProductIds(cart));
        })
        .catch(() => {});

    window.addEventListener('cart:updated', (event) => {
        applyCartProductBadges(event.detail?.product_ids ?? []);
    });
}
