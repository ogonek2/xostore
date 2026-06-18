@php
    $products = collect($block['products'] ?? []);
    $settings = $block['settings'] ?? [];
    $theme = ($settings['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
    $buttonVariant = $theme === 'dark' ? 'inverse' : 'outline';
@endphp

@if ($products->isNotEmpty())
    <x-shop.landing.section :block="$block" class="landing-products">
        <div class="landing-container">
            @if (filled($block['title'] ?? null))
                <h2 class="landing-block-title">{{ $block['title'] }}</h2>
            @endif
            @if (filled($block['subtitle'] ?? null))
                <p class="landing-block-subtitle">{{ $block['subtitle'] }}</p>
            @endif

            <div class="landing-products-swiper swiper">
                <div class="swiper-wrapper">
                    @foreach ($products as $product)
                        <div class="swiper-slide !h-auto !w-[min(72vw,220px)] shrink-0 sm:!w-[240px] lg:!w-[260px]">
                            <x-shop.product-card
                                :product-id="$product['product_id'] ?? null"
                                :default-variant-id="$product['default_variant_id'] ?? null"
                                :url="$product['url']"
                                :name="$product['name']"
                                :category="$product['category'] ?? null"
                                :price-formatted="$product['price_formatted']"
                                :image="$product['image']"
                                :colors="$product['colors'] ?? []"
                                :alt="$product['alt'] ?? $product['name']"
                                variant="minimal"
                                :show-new-badge="$product['is_new'] ?? false"
                            />
                        </div>
                    @endforeach
                </div>
            </div>

            @if (filled($block['button_label'] ?? null))
                <div class="landing-products__footer">
                    <x-shop.landing.button
                        :label="$block['button_label']"
                        :url="$block['link_url'] ?? null"
                        :variant="$buttonVariant"
                    />
                </div>
            @endif
        </div>
    </x-shop.landing.section>
@endif
