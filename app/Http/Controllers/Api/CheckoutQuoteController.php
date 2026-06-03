<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckoutQuoteController extends Controller
{
    public function __invoke(
        Request $request,
        string $locale,
        CartService $cart,
        CheckoutService $checkout,
    ): JsonResponse {
        $data = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_methods,id'],
        ]);

        $cartData = $cart->present($locale);

        return response()->json(
            $checkout->quote((int) $data['payment_method_id'], (float) $cartData['subtotal'], $locale)
        );
    }
}
