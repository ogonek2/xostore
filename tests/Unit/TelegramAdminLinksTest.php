<?php

namespace Tests\Unit;

use App\Support\Telegram\TelegramAdminLinks;
use Tests\TestCase;

class TelegramAdminLinksTest extends TestCase
{
    public function test_it_does_not_prepend_the_domain_to_an_absolute_url(): void
    {
        config([
            'app.url' => 'https://xostorebrand.com',
            'shop.telegram.admin_url' => null,
        ]);

        $url = 'https://xostorebrand.com/admin/orders/12/edit';

        $this->assertSame($url, TelegramAdminLinks::resolve($url));
    }

    public function test_configured_admin_domain_replaces_the_origin_without_duplicating_it(): void
    {
        config([
            'app.url' => 'http://localhost',
            'shop.telegram.admin_url' => 'https://xostorebrand.com',
        ]);

        $this->assertSame(
            'https://xostorebrand.com/admin/orders/12/edit?tab=payment',
            TelegramAdminLinks::resolve('http://localhost/admin/orders/12/edit?tab=payment'),
        );
    }

    public function test_it_joins_a_relative_admin_path(): void
    {
        config([
            'shop.telegram.admin_url' => 'https://xostorebrand.com/',
        ]);

        $this->assertSame(
            'https://xostorebrand.com/admin/orders/12/edit',
            TelegramAdminLinks::resolve('/admin/orders/12/edit'),
        );
    }
}
