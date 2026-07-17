<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_root_redirects_to_the_default_locale(): void
    {
        $this->seed(LanguageSeeder::class);

        $response = $this->get('/');

        $response->assertRedirect(route('home', [
            'locale' => config('shop.default_language'),
        ]));
    }
}
