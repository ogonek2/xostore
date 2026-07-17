<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Cart\CartService;
use App\Support\Seo\SeoBuilder;
use App\Support\Shop\ShopLayoutData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function show(string $locale): View
    {
        return view('shop.order-track', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.order_track.title')),
            'cartCount' => app(CartService::class)->count(),
            'order' => null,
            'breadcrumbs' => [
                ['label' => __('shop.order_track.title'), 'url' => null],
            ],
        ]);
    }

    public function lookup(Request $request, string $locale): View
    {
        $data = $request->validate([
            'number' => ['required', 'string', 'size:8'],
            'email' => ['required', 'email', 'max:255'],
        ]);

        $order = Order::query()
            ->with(['items', 'paymentMethod', 'orderStatus', 'latestPayment'])
            ->where('number', strtoupper(trim($data['number'])))
            ->where('email', $data['email'])
            ->first();

        return view('shop.order-track', [
            ...ShopLayoutData::shared(),
            'seo' => SeoBuilder::privatePage(__('shop.order_track.title')),
            'cartCount' => app(CartService::class)->count(),
            'order' => $order,
            'notFound' => $order === null,
            'breadcrumbs' => [
                ['label' => __('shop.order_track.title'), 'url' => null],
            ],
        ]);
    }
}
