@extends('layouts.shop')

@section('title', $product['display_name'].' — '.config('shop.name'))

@section('content')
    <x-shop.header
        :menu-roots="$menuRoots"
        :languages="$languages"
        :cart-count="$cartCount"
    />

    <main class="flex-1">
        <div class="mx-auto max-w-[90rem] px-5 py-8 lg:px-8 lg:py-10">
            <x-shop.breadcrumbs :items="$breadcrumbs" />

            <div class="mt-8 grid gap-10 lg:grid-cols-2 lg:gap-14">
                <div class="space-y-3">
                    @php
                        $images = count($product['images']) > 0
                            ? $product['images']
                            : [['url' => asset('images/products/placeholder.jpg'), 'alt' => $product['name']]];
                    @endphp
                    <div class="aspect-[4/5] overflow-hidden rounded-2xl bg-[#eceae6]">
                        <img
                            id="product-main-image"
                            src="{{ $images[0]['url'] }}"
                            alt="{{ $images[0]['alt'] }}"
                            class="size-full object-cover object-center"
                        >
                    </div>
                    @if (count($images) > 1)
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @foreach ($images as $index => $image)
                                <button
                                    type="button"
                                    class="h-20 w-16 shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-[#eceae6] transition hover:border-primary-DEFAULT data-[active=true]:border-primary-DEFAULT"
                                    data-product-thumb
                                    data-url="{{ $image['url'] }}"
                                    data-active="{{ $index === 0 ? 'true' : 'false' }}"
                                    aria-label="{{ $image['alt'] }}"
                                >
                                    <img src="{{ $image['url'] }}" alt="" class="size-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="lg:py-4">
                    @if ($product['is_new'])
                        <span class="inline-block bg-primary-DEFAULT px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-text-inverse">
                            {{ __('shop.new_arrivals.badge') }}
                        </span>
                    @endif

                    @if ($product['brand'])
                        <p class="mt-4 text-sm uppercase tracking-[0.16em] text-text-muted">{{ $product['brand'] }}</p>
                    @endif

                    <h1 class="mt-2 text-2xl font-semibold tracking-tight text-primary-DEFAULT lg:text-3xl">
                        {{ $product['name'] }}
                    </h1>

                    @if ($product['short_description'])
                        <p class="mt-4 text-sm leading-relaxed text-text-muted">
                            {{ $product['short_description'] }}
                        </p>
                    @endif

                    <div class="mt-6 flex items-baseline gap-3">
                        <p id="product-price" class="text-xl font-semibold text-primary-DEFAULT">
                            {{ $product['default_variant']['price_formatted'] ?? '' }}
                        </p>
                        @if ($product['default_variant']['compare_at_formatted'] ?? null)
                            <p id="product-compare-price" class="text-sm text-text-muted line-through">
                                {{ $product['default_variant']['compare_at_formatted'] }}
                            </p>
                        @endif
                    </div>

                    @if (count($product['variants']) > 1)
                        <div class="mt-8">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                                {{ __('shop.product.select_variant') }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($product['variants'] as $variant)
                                    <button
                                        type="button"
                                        data-variant-id="{{ $variant['id'] }}"
                                        data-variant-price="{{ $variant['price_formatted'] }}"
                                        data-variant-compare="{{ $variant['compare_at_formatted'] ?? '' }}"
                                        @class([
                                            'border px-4 py-2 text-sm transition',
                                            'border-primary-DEFAULT bg-primary-DEFAULT text-text-inverse' => $variant['is_default'],
                                            'border-border-DEFAULT hover:border-primary-DEFAULT' => ! $variant['is_default'],
                                        ])
                                    >
                                        {{ $variant['size'] ?? $variant['sku'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @php
                        $colors = collect($product['variants'])->flatMap(fn ($v) => $v['colors'])->unique('id');
                    @endphp
                    @if ($colors->isNotEmpty())
                        <div class="mt-8">
                            <p class="text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                                {{ __('shop.product.colors') }}
                            </p>
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($colors as $color)
                                    <span
                                        class="size-8 rounded-full border border-border-DEFAULT"
                                        style="background-color: {{ $color['hex'] }}"
                                        title="{{ $color['label'] }}"
                                    ></span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-10 flex flex-col gap-3 sm:flex-row">
                        <button
                            type="button"
                            class="min-h-[3rem] flex-1 bg-primary-DEFAULT px-8 text-sm font-medium text-text-inverse transition-colors hover:bg-primary-hover"
                            onclick="window.dispatchEvent(new Event('cart:open'))"
                        >
                            {{ __('shop.product.add_to_cart') }}
                        </button>
                        <a
                            href="{{ route('products.index', ['locale' => app()->getLocale()]) }}"
                            class="inline-flex min-h-[3rem] flex-1 items-center justify-center border border-border-DEFAULT px-8 text-sm font-medium transition-colors hover:border-primary-DEFAULT"
                        >
                            {{ __('shop.listing.continue_shopping') }}
                        </a>
                    </div>

                    @if ($product['description'])
                        <div class="prose prose-sm mt-12 max-w-none text-text-muted">
                            {!! $product['description'] !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-product-thumb]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const main = document.getElementById('product-main-image');
                    if (main) main.src = btn.dataset.url;
                    document.querySelectorAll('[data-product-thumb]').forEach((b) => b.dataset.active = 'false');
                    btn.dataset.active = 'true';
                });
            });

            document.querySelectorAll('[data-variant-id]').forEach((btn) => {
                btn.addEventListener('click', () => {
                    const price = document.getElementById('product-price');
                    const compare = document.getElementById('product-compare-price');
                    if (price) price.textContent = btn.dataset.variantPrice;
                    if (compare) {
                        compare.textContent = btn.dataset.variantCompare || '';
                        compare.classList.toggle('hidden', !btn.dataset.variantCompare);
                    }
                    document.querySelectorAll('[data-variant-id]').forEach((b) => {
                        b.classList.remove('border-primary-DEFAULT', 'bg-primary-DEFAULT', 'text-text-inverse');
                        b.classList.add('border-border-DEFAULT');
                    });
                    btn.classList.add('border-primary-DEFAULT', 'bg-primary-DEFAULT', 'text-text-inverse');
                    btn.classList.remove('border-border-DEFAULT');
                });
            });
        </script>
    @endpush
@endsection
