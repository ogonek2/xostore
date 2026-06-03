<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = route('sitemap');
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /*/api/',
            'Disallow: /*/zamowienie',
            'Disallow: /*/koszyk',
            'Disallow: /newsletter/unsubscribe',
            '',
            "Sitemap: {$sitemap}",
        ];

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
