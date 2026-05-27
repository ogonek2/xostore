function trackEvent(event, payload = {}) {
    const endpoint = document.body?.dataset.analyticsEndpoint;

    if (!endpoint) {
        return;
    }

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf,
        },
        body: JSON.stringify({
            event,
            path: window.location.pathname,
            ...payload,
        }),
        keepalive: true,
    }).catch(() => {});
}

export function initShopAnalytics() {
    trackEvent('page_view');

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        try {
            const url = new URL(link.href, window.location.origin);

            if (url.origin !== window.location.origin) {
                return;
            }

            trackEvent('page_view', { path: url.pathname });
        } catch {
            //
        }
    });
}

export { trackEvent };
