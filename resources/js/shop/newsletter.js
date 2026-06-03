/**
 * Footer newsletter subscription form.
 */
export function initNewsletterForms() {
    document.querySelectorAll('[data-newsletter-form]').forEach((form) => {
        if (form.dataset.newsletterBound === '1') {
            return;
        }

        form.dataset.newsletterBound = '1';

        const endpoint = form.dataset.newsletterEndpoint;
        const fields = form.querySelector('[data-newsletter-fields]');
        const success = form.querySelector('[data-newsletter-success]');
        const error = form.querySelector('[data-newsletter-error]');
        const submit = form.querySelector('[data-newsletter-submit]');
        const input = form.querySelector('[data-newsletter-email]');

        if (!endpoint || !input) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            error?.classList.add('hidden');
            success?.classList.add('hidden');
            submit?.setAttribute('disabled', 'disabled');

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ email: input.value.trim() }),
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const message = payload?.message
                        ?? (payload?.errors?.email?.[0])
                        ?? form.dataset.newsletterError
                        ?? 'Error';
                    throw new Error(message);
                }

                fields?.classList.add('hidden');
                if (success) {
                    success.textContent = payload?.message ?? success.textContent;
                    success.classList.remove('hidden');
                }
            } catch (e) {
                if (error) {
                    error.textContent = e?.message ?? form.dataset.newsletterError ?? 'Error';
                    error.classList.remove('hidden');
                }
            } finally {
                submit?.removeAttribute('disabled');
            }
        });
    });
}
