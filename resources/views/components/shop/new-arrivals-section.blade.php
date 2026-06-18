@props([
    'products',
    'viewAllUrl' => '#',
    'title' => null,
    'viewAllLabel' => null,
])

<section class="overflow-x-clip bg-surface-DEFAULT py-14 lg:py-20">
    <div class="mx-auto max-w-[90rem] px-5 lg:px-8">
        <div class="mb-10 flex flex-wrap items-end justify-between gap-4 lg:mb-12">
            <h2 class="text-2xl font-semibold tracking-tight text-primary-DEFAULT lg:text-[1.75rem]">
                {{ $title ?? __('shop.new_arrivals.title') }}
            </h2>

            <a
                href="{{ $viewAllUrl }}"
                class="text-xs font-medium uppercase tracking-[0.2em] text-text-muted underline decoration-border-strong underline-offset-[6px] transition-colors hover:text-primary-DEFAULT hover:decoration-primary-DEFAULT"
            >
                {{ $viewAllLabel ?? __('shop.new_arrivals.view_all') }}
            </a>
        </div>

        @if ($products->isNotEmpty())
            <div class="new-arrivals-swiper swiper overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($products as $product)
                        <div class="swiper-slide !h-auto !w-[min(68vw,200px)] shrink-0 sm:!w-[220px] lg:!w-[240px]">
                            <x-shop.product-card
                                variant="minimal"
                                :product-id="$product['product_id']"
                                :default-variant-id="$product['default_variant_id'] ?? null"
                                :show-new-badge="$product['is_new'] ?? true"
                                :url="$product['url']"
                                :name="$product['name']"
                                :price-formatted="$product['price_formatted']"
                                :image="$product['image']"
                                :alt="$product['alt']"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
