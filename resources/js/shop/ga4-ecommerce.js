/**
 * GA4 Enhanced Ecommerce via dataLayer (GTM).
 * Always clear ecommerce before each push per Google's guidance.
 */

function currencyCode() {
    return document.body?.dataset?.ga4Currency
        || window.__SHOP_CURRENCY__
        || 'PLN';
}

export function ensureDataLayer() {
    window.dataLayer = window.dataLayer || [];

    return window.dataLayer;
}

export function pushEcommerce(event, ecommerce = {}) {
    const dataLayer = ensureDataLayer();
    dataLayer.push({ ecommerce: null });
    dataLayer.push({
        event,
        ecommerce: {
            currency: currencyCode(),
            ...ecommerce,
        },
    });
}

export function toGaItem(source = {}, extras = {}) {
    const item = {
        item_id: String(source.item_id ?? source.product_id ?? source.id ?? ''),
        item_name: source.item_name ?? source.name ?? '',
        price: Number(source.price ?? source.unit_price ?? 0),
        quantity: Number(source.quantity ?? 1),
    };

    const brand = source.item_brand ?? source.brand;
    const category = source.item_category ?? source.category;
    const variant = source.item_variant ?? source.variant_label;

    if (brand) item.item_brand = brand;
    if (category) item.item_category = category;
    if (variant) item.item_variant = variant;

    if (extras.item_list_name) item.item_list_name = extras.item_list_name;
    if (extras.item_list_id) item.item_list_id = extras.item_list_id;
    if (extras.index != null) item.index = Number(extras.index);

    return item;
}

function cardToSource(card) {
    return {
        item_id: card.dataset.ga4ItemId || card.dataset.productId,
        item_name: card.dataset.ga4ItemName || card.querySelector('h3')?.textContent?.trim(),
        price: card.dataset.ga4Price,
        brand: card.dataset.ga4Brand || undefined,
        category: card.dataset.ga4Category || undefined,
    };
}

export function viewItemList(items, listName, listId) {
    if (!items?.length) {
        return;
    }

    pushEcommerce('view_item_list', {
        item_list_name: listName,
        item_list_id: listId || undefined,
        items: items.map((item, index) => toGaItem(item, {
            item_list_name: listName,
            item_list_id: listId || undefined,
            index: item.index ?? index + 1,
        })),
    });
}

export function selectItem(item, listName, listId) {
    if (!item) {
        return;
    }

    pushEcommerce('select_item', {
        item_list_name: listName,
        item_list_id: listId || undefined,
        items: [toGaItem(item, {
            item_list_name: listName,
            item_list_id: listId || undefined,
            index: item.index,
        })],
    });
}

export function viewItem(item) {
    if (!item) {
        return;
    }

    const gaItem = toGaItem(item);
    pushEcommerce('view_item', {
        value: gaItem.price * gaItem.quantity,
        items: [gaItem],
    });
}

export function addToCart(item) {
    if (!item) {
        return;
    }

    const gaItem = toGaItem(item);
    pushEcommerce('add_to_cart', {
        value: gaItem.price * gaItem.quantity,
        items: [gaItem],
    });
}

export function removeFromCart(item) {
    if (!item) {
        return;
    }

    const gaItem = toGaItem(item);
    pushEcommerce('remove_from_cart', {
        value: gaItem.price * gaItem.quantity,
        items: [gaItem],
    });
}

export function beginCheckout(items, value) {
    if (!items?.length) {
        return;
    }

    const gaItems = items.map((item) => toGaItem(item));
    const total = value != null
        ? Number(value)
        : gaItems.reduce((sum, item) => sum + item.price * item.quantity, 0);

    pushEcommerce('begin_checkout', {
        value: total,
        items: gaItems,
    });
}

export function purchase(payload) {
    if (!payload?.transaction_id || !payload?.items?.length) {
        return;
    }

    pushEcommerce('purchase', {
        transaction_id: String(payload.transaction_id),
        affiliation: payload.affiliation || document.body?.dataset?.ga4Affiliation || undefined,
        value: Number(payload.value ?? 0),
        tax: payload.tax != null ? Number(payload.tax) : undefined,
        shipping: payload.shipping != null ? Number(payload.shipping) : undefined,
        coupon: payload.coupon || undefined,
        items: payload.items.map((item) => toGaItem(item)),
    });
}

/**
 * Blade product grids / carousels marked with [data-ga4-item-list].
 */
export function initGa4ProductLists() {
    document.querySelectorAll('[data-ga4-item-list]').forEach((listEl) => {
        if (listEl.dataset.ga4Tracked === '1') {
            return;
        }

        listEl.dataset.ga4Tracked = '1';

        const listName = listEl.dataset.ga4ListName || 'Product List';
        const listId = listEl.dataset.ga4ListId || '';
        const cards = [...listEl.querySelectorAll('[data-product-card][data-product-id]')];

        const items = cards.map((card, index) => ({
            ...cardToSource(card),
            index: index + 1,
            quantity: 1,
        })).filter((item) => item.item_id && item.item_name);

        if (items.length) {
            viewItemList(items, listName, listId);
        }

        listEl.addEventListener('click', (event) => {
            if (event.target.closest('[data-add-to-cart]')) {
                return;
            }

            const link = event.target.closest('a[href]');
            if (!link) {
                return;
            }

            const card = link.closest('[data-product-card][data-product-id]');
            if (!card || !listEl.contains(card)) {
                return;
            }

            const index = cards.indexOf(card) + 1;
            selectItem({
                ...cardToSource(card),
                index,
                quantity: 1,
            }, listName, listId);
        });
    });
}
