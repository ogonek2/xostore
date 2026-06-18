import { dispatchCartState } from './cart-badges';

export function getCartStoreUrl() {
    const root = document.getElementById('cart-drawer-root');

    if (!root?.dataset.routes) {
        return null;
    }

    const routes = JSON.parse(root.dataset.routes);

    return routes.store ?? routes.cartStore ?? null;
}

export async function addVariantToCart(variantId, { quantity = 1, openDrawer = false } = {}) {
    const cartStore = getCartStoreUrl();

    if (!cartStore || !variantId) {
        throw new Error('Cart unavailable');
    }

    const response = await fetch(cartStore, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        body: JSON.stringify({
            variant_id: variantId,
            quantity,
        }),
    });

    const data = await response.json();

    if (!response.ok) {
        const message = Object.values(data.errors ?? {}).flat()[0] ?? data.message;

        throw new Error(message || 'Cart error');
    }

    dispatchCartState(data);

    if (openDrawer) {
        window.dispatchEvent(new Event('cart:open'));
    }

    return data;
}

export function initProductCardAddButtons() {
    document.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-add-to-cart]');

        if (!button || button.dataset.adding === '1') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const variantId = Number(button.dataset.variantId);
        const productUrl = button.dataset.productUrl;

        if (!variantId) {
            if (productUrl) {
                window.location.href = productUrl;
            }

            return;
        }

        button.dataset.adding = '1';
        button.setAttribute('aria-busy', 'true');

        try {
            await addVariantToCart(variantId);
        } catch {
            if (productUrl) {
                window.location.href = productUrl;
            }
        } finally {
            delete button.dataset.adding;
            button.removeAttribute('aria-busy');
        }
    });
}
