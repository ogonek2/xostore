<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Cart\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart,
    ) {}

    public function show(string $locale): JsonResponse
    {
        return response()->json($this->cart->present($locale));
    }

    public function store(Request $request, string $locale): JsonResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
        ]);

        return response()->json(
            $this->cart->add($data['variant_id'], $data['quantity'] ?? 1)
        );
    }

    public function update(Request $request, string $locale, int $item): JsonResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        return response()->json(
            $this->cart->update($item, $data['quantity'])
        );
    }

    public function destroy(string $locale, int $item): JsonResponse
    {
        return response()->json($this->cart->remove($item));
    }
}
