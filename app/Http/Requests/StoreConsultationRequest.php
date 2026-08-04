<?php

namespace App\Http\Requests;

use App\Services\Security\TurnstileVerifier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Validator;

class StoreConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'message' => ['required', 'string', 'max:5000'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'preferred_at' => ['nullable', 'date'],
            'form_token' => ['required', 'string', 'max:64'],
            // Honeypot — must stay empty.
            'website' => ['nullable', 'max:0'],
        ];

        if (app(TurnstileVerifier::class)->isConfigured()) {
            $rules['cf-turnstile-response'] = ['required', 'string'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.max' => __('shop.consultation.spam_rejected'),
            'form_token.required' => __('shop.consultation.spam_rejected'),
            'cf-turnstile-response.required' => __('shop.consultation.captcha_required'),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                $this->logRejected('validation_failed', $validator->errors()->keys());

                return;
            }

            if (filled($this->input('website'))) {
                $validator->errors()->add('website', __('shop.consultation.spam_rejected'));
                $this->logRejected('honeypot');

                return;
            }

            if (! $this->passesFormToken()) {
                $validator->errors()->add('form_token', __('shop.consultation.spam_rejected'));
                $this->logRejected('missing_or_invalid_form_token');

                return;
            }

            if (! $this->passesFormTiming()) {
                $validator->errors()->add('form_token', __('shop.consultation.spam_rejected'));
                $this->logRejected('form_timing');

                return;
            }

            $turnstile = app(TurnstileVerifier::class);

            if ($turnstile->isConfigured() && ! $turnstile->verify(
                $this->input('cf-turnstile-response'),
                $this->ip(),
            )) {
                $validator->errors()->add('cf-turnstile-response', __('shop.consultation.captcha_failed'));
                $this->logRejected('turnstile_failed');
            }
        });
    }

    public function submissionSource(): string
    {
        $hasReferer = filled($this->headers->get('referer'));
        $hasSessionForm = $this->session()->has('consultation.form_token');
        $hasTurnstile = filled($this->input('cf-turnstile-response'));

        if ($hasSessionForm && $hasReferer) {
            return $hasTurnstile ? 'ui_with_captcha' : 'ui_form';
        }

        if ($hasSessionForm) {
            return 'session_without_referer';
        }

        return 'direct_or_forged';
    }

    protected function passesFormToken(): bool
    {
        $sessionToken = (string) $this->session()->get('consultation.form_token', '');
        $requestToken = (string) $this->input('form_token', '');

        return $sessionToken !== ''
            && $requestToken !== ''
            && hash_equals($sessionToken, $requestToken);
    }

    protected function passesFormTiming(): bool
    {
        $issuedAt = (int) $this->session()->get('consultation.form_issued_at', 0);

        if ($issuedAt < 1) {
            return false;
        }

        $elapsed = now()->timestamp - $issuedAt;
        $minSeconds = (int) config('services.turnstile.min_form_seconds', 2);
        $maxSeconds = (int) config('services.turnstile.max_form_seconds', 7200);

        return $elapsed >= $minSeconds && $elapsed <= $maxSeconds;
    }

    /**
     * @param  list<string>|string  $detail
     */
    protected function logRejected(string $reason, array|string $detail = []): void
    {
        Log::info('Consultation spam rejected', [
            'reason' => $reason,
            'detail' => $detail,
            'ip' => $this->ip(),
            'user_agent' => substr((string) $this->userAgent(), 0, 512),
            'referer' => substr((string) $this->headers->get('referer'), 0, 512),
            'source' => $this->submissionSource(),
            'has_csrf_session' => $this->session()->isStarted(),
            'has_form_token_session' => $this->session()->has('consultation.form_token'),
            'email' => $this->input('email'),
        ]);
    }
}
