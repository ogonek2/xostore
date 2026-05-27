@props([
    'products',
    'title' => null,
    'viewMoreUrl' => '#',
    'viewMoreLabel' => null,
])

<section class="bg-surface-DEFAULT py-14 lg:py-20">
    <div class="mx-auto max-w-[90rem] px-5 lg:px-8">
        <h2 class="mb-10 text-2xl font-semibold tracking-tight text-primary-DEFAULT lg:mb-12 lg:text-[1.75rem]">
            {{ $title ?? __('shop.trending.title') }}
        </h2>

        @if ($products->isNotEmpty())
            <div class="trending-swiper swiper overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($products as $product)
                        <div class="swiper-slide !h-auto !w-[min(72vw,220px)] shrink-0 sm:!w-[240px] lg:!w-[260px]">
                            <x-shop.product-card
                                :product-id="$product['product_id']"
                                :url="$product['url']"
                                :name="$product['name']"
                                :category="$product['category']"
                                :price-formatted="$product['price_formatted']"
                                :image="$product['image']"
                                :colors="$product['colors']"
                                :alt="$product['alt']"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-10 flex justify-end lg:mt-12">
            <a
                href="{{ $viewMoreUrl }}"
                class="inline-flex min-w-[10rem] items-center justify-center bg-primary-DEFAULT px-8 py-3.5 text-sm font-medium text-text-inverse transition-colors hover:bg-primary-hover"
            >
                {{ $viewMoreLabel ?? __('shop.trending.view_more') }}
            </a>
        </div>
    </div>
</section>
