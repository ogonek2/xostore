document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-checkout-form]');

    if (!form) {
        return;
    }

    const quoteUrl = form.dataset.quoteUrl;
    const shippingEl = form.querySelector('[data-checkout-shipping]');
    const totalEl = form.querySelector('[data-checkout-total]');
    const radios = form.querySelectorAll('[data-payment-method]');

    const updateQuote = async () => {
        const selected = form.querySelector('[data-payment-method]:checked');

        if (!selected || !quoteUrl) {
            return;
        }

        shippingEl.textContent = '…';

        try {
            const response = await fetch(`${quoteUrl}?payment_method_id=${selected.value}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error('quote failed');
            }

            const data = await response.json();
            shippingEl.textContent = data.shipping_formatted;
            totalEl.textContent = data.total_formatted;
        } catch {
            shippingEl.textContent = '—';
            totalEl.textContent = '—';
        }
    };

    radios.forEach((radio) => radio.addEventListener('change', updateQuote));
    updateQuote();
});
