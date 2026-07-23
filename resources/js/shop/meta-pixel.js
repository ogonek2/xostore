function fbqReady() {
    return typeof window.fbq === 'function';
}

export function trackAddToCart({ productId, value, currency = 'PLN' }) {
    if (!fbqReady() || productId == null) {
        return;
    }

    window.fbq('track', 'AddToCart', {
        value: Number(value) || 0,
        currency,
        content_ids: [String(productId)],
        content_type: 'product',
    });
}

export function trackPurchase({ orderId, value, contentIds, currency = 'PLN' }) {
    if (!fbqReady() || !orderId) {
        return;
    }

    const key = `fb_purchase_${orderId}`;

    try {
        if (window.sessionStorage.getItem(key)) {
            return;
        }
        window.sessionStorage.setItem(key, '1');
    } catch {
        // Ignore storage errors and still send the event once per page load.
    }

    window.fbq('track', 'Purchase', {
        value: Number(value) || 0,
        currency,
        content_ids: (contentIds ?? []).map(String),
        content_type: 'product',
        order_id: String(orderId),
    });
}
