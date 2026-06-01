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

    document.querySelectorAll('.new-arrivals-swiper, .trending-swiper, .categories-swiper, .product-similar-swiper').forEach((el) => {
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
                items: cart?.items ?? [],
            },
        })
    );
}

export function productCartLines(cart, productId) {
    return (cart?.items ?? [])
        .filter((item) => Number(item.product_id) === Number(productId))
        .map((item) => ({
            variant_id: item.variant_id,
            quantity: item.quantity,
            variant_label: item.variant_label,
        }));
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
            if (cart) dispatchCartState(cart);
        })
        .catch(() => {});

    window.addEventListener('cart:updated', (event) => {
        applyCartProductBadges(event.detail?.product_ids ?? []);
    });
}
