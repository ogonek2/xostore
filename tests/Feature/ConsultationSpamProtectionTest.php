<?php

namespace Tests\Feature;

use App\Models\ConsultationRequest;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultationSpamProtectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        config([
            'services.turnstile.site_key' => null,
            'services.turnstile.secret_key' => null,
            'services.turnstile.min_form_seconds' => 0,
        ]);
    }

    public function test_direct_post_without_opening_form_is_rejected(): void
    {
        $response = $this->from(route('consultation.show', ['locale' => 'pl']))
            ->post(route('consultation.store', ['locale' => 'pl']), [
                'name' => 'Bot',
                'email' => 'bot@example.com',
                'message' => 'Spam message',
                'form_token' => 'forged-token',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('consultation_requests', 0);
    }

    public function test_honeypot_filled_is_rejected(): void
    {
        $this->get(route('consultation.show', ['locale' => 'pl']));
        $token = session('consultation.form_token');

        $response = $this->from(route('consultation.show', ['locale' => 'pl']))
            ->post(route('consultation.store', ['locale' => 'pl']), [
                'name' => 'Bot',
                'email' => 'bot@example.com',
                'message' => 'Spam message',
                'form_token' => $token,
                'website' => 'https://spam.example',
            ]);

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('consultation_requests', 0);
    }

    public function test_valid_form_submission_is_accepted(): void
    {
        $this->get(route('consultation.show', ['locale' => 'pl']));
        $token = session('consultation.form_token');

        $response = $this->from(route('consultation.show', ['locale' => 'pl']))
            ->post(route('consultation.store', ['locale' => 'pl']), [
                'name' => 'Anna Kowalska',
                'email' => 'anna@example.com',
                'message' => 'Potrzebuję konsultacji',
                'form_token' => $token,
                'website' => '',
            ]);

        $response->assertRedirect(route('consultation.show', ['locale' => 'pl']));
        $response->assertSessionHas('consultation_sent');
        $this->assertDatabaseCount('consultation_requests', 1);

        $row = ConsultationRequest::query()->first();
        $this->assertNotNull($row->ip);
        $this->assertNotNull($row->submission_source);
    }
}
