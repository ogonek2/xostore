@props([
    'type',
    'props' => [],
])

@switch($type)
    @case('hero')
        <x-shop.hero-builder :sections="$props['sections'] ?? []" />
        @break

    @case('banners')
        <x-shop.banners-section :items="$props['items'] ?? collect()" />
        @break

    @case('category_showcase')
        <x-shop.categories-showcase
            :cards="$props['cards'] ?? collect()"
            :title="$props['title'] ?? null"
        />
        @break

    @case('trending')
        <div id="trending">
            <x-shop.trending-section
                :products="$props['products'] ?? collect()"
                :title="$props['title'] ?? null"
                :view-more-url="$props['viewMoreUrl'] ?? '#'"
            />
        </div>
        @break

    @case('promotions')
        <x-shop.promotions-section
            :featured="$props['featured'] ?? null"
            :compact="$props['compact'] ?? collect()"
            :view-all-url="$props['viewAllUrl'] ?? '#'"
            :view-more-url="$props['viewMoreUrl'] ?? '#'"
            :title="$props['title'] ?? null"
        />
        @break

    @case('new_arrivals')
        <x-shop.new-arrivals-section
            :products="$props['products'] ?? collect()"
            :view-all-url="$props['viewAllUrl'] ?? '#'"
            :title="$props['title'] ?? null"
        />
        @break

    @case('catalog')
        @php
            $layout = $props['layout'] ?? 'trending';
        @endphp

        @if ($layout === 'new_arrivals')
            <x-shop.new-arrivals-section
                :products="$props['products'] ?? collect()"
                :view-all-url="$props['viewMoreUrl'] ?? '#'"
                :title="$props['title'] ?? null"
            />
        @else
            <x-shop.trending-section
                :products="$props['products'] ?? collect()"
                :title="$props['title'] ?? null"
                :view-more-url="$props['viewMoreUrl'] ?? '#'"
            />
        @endif
        @break

    @case('spacer')
        <div
            @class([
                'w-full',
                'h-6 lg:h-8' => ($props['size'] ?? 'md') === 'sm',
                'h-10 lg:h-14' => ($props['size'] ?? 'md') === 'md',
                'h-16 lg:h-24' => ($props['size'] ?? 'md') === 'lg',
                'h-24 lg:h-32' => ($props['size'] ?? 'md') === 'xl',
            ])
            aria-hidden="true"
        ></div>
        @break
@endswitch
