@props([
    'facets',
    'filters',
    'formAction',
])

<form method="get" action="{{ $formAction }}" class="space-y-8">
    @if (request('q'))
        <input type="hidden" name="q" value="{{ request('q') }}">
    @endif

    <div>
        <label for="sort" class="mb-3 block text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
            {{ __('shop.listing.sort') }}
        </label>
        <select
            id="sort"
            name="sort"
            class="w-full border border-border-DEFAULT bg-surface-DEFAULT px-3 py-2.5 text-sm outline-none focus:border-primary-DEFAULT"
            onchange="this.form.requestSubmit()"
        >
            @foreach (['newest', 'featured', 'price_asc', 'price_desc'] as $sort)
                <option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>
                    {{ __('shop.listing.sort_'.$sort) }}
                </option>
            @endforeach
        </select>
    </div>

    <fieldset>
        <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
            {{ __('shop.listing.filters') }}
        </legend>
        <div class="space-y-2">
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input type="checkbox" name="new" value="1" @checked($filters['new'] ?? false) class="size-4 border-border-DEFAULT" onchange="this.form.requestSubmit()">
                <span>{{ __('shop.listing.only_new') }}</span>
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm">
                <input type="checkbox" name="sale" value="1" @checked($filters['sale'] ?? false) class="size-4 border-border-DEFAULT" onchange="this.form.requestSubmit()">
                <span>{{ __('shop.listing.only_sale') }}</span>
            </label>
        </div>
    </fieldset>

    @if (! empty($facets['brands']))
        <fieldset>
            <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ __('shop.listing.brand') }}
            </legend>
            <ul class="max-h-48 space-y-2 overflow-y-auto">
                @foreach ($facets['brands'] as $brand)
                    <li>
                        <label class="flex cursor-pointer items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                name="brands[]"
                                value="{{ $brand['id'] }}"
                                @checked(in_array($brand['id'], $filters['brands'] ?? [], true))
                                class="size-4 border-border-DEFAULT"
                                onchange="this.form.requestSubmit()"
                            >
                            <span>{{ $brand['label'] }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </fieldset>
    @endif

    @if (! empty($facets['size_groups']))
        <fieldset>
            <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ __('shop.product.select_variant') }}
            </legend>

            @if (count($facets['size_groups']) > 1)
                <p class="mb-3 text-xs leading-relaxed text-text-muted">
                    {{ __('shop.listing.size_hint') }}
                </p>
            @endif

            <div class="space-y-4">
                @foreach ($facets['size_groups'] as $group)
                    <div>
                        @if (count($facets['size_groups']) > 1)
                            <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-muted">
                                {{ $group['label'] }}
                            </p>
                        @endif

                        <div class="flex flex-wrap gap-1.5">
                            @foreach ($group['sizes'] as $size)
                                @php
                                    $selected = in_array($size['key'], $filters['sizes'] ?? [], true);
                                @endphp
                                <label @class([
                                    'inline-flex cursor-pointer items-center justify-center border px-2.5 py-1.5 text-xs transition-colors',
                                    'border-primary-DEFAULT bg-primary-DEFAULT/5 font-semibold text-primary-DEFAULT' => $selected,
                                    'border-border-DEFAULT text-text-DEFAULT hover:border-primary-DEFAULT/40' => ! $selected,
                                ])>
                                    <input
                                        type="checkbox"
                                        name="sizes[]"
                                        value="{{ $size['key'] }}"
                                        @checked($selected)
                                        class="sr-only"
                                        onchange="this.form.requestSubmit()"
                                    >
                                    <span>{{ $size['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if (! empty($facets['colors']))
        <fieldset>
            <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ __('shop.listing.color') }}
            </legend>
            <ul class="max-h-48 space-y-2 overflow-y-auto">
                @foreach ($facets['colors'] as $color)
                    <li>
                        <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                            <input
                                type="checkbox"
                                name="colors[]"
                                value="{{ $color['id'] }}"
                                @checked(in_array($color['id'], $filters['colors'] ?? [], true))
                                class="size-4 border-border-DEFAULT"
                                onchange="this.form.requestSubmit()"
                            >
                            <span
                                class="size-5 shrink-0 rounded-full border border-border-DEFAULT ring-1 ring-inset ring-black/5"
                                style="background-color: {{ $color['hex'] ?? '#e8e6e2' }}"
                                title="{{ $color['label'] }}"
                            ></span>
                            <span>{{ $color['label'] }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </fieldset>
    @endif

    <fieldset>
        <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
            {{ __('shop.listing.price') }}
        </legend>
        <div class="grid grid-cols-2 gap-2">
            <input
                type="number"
                name="price_min"
                min="0"
                step="1"
                placeholder="{{ $facets['price_min'] ? number_format($facets['price_min'], 0, ',', ' ') : __('shop.listing.from') }}"
                value="{{ $filters['price_min'] ?? '' }}"
                class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
            >
            <input
                type="number"
                name="price_max"
                min="0"
                step="1"
                placeholder="{{ $facets['price_max'] ? number_format($facets['price_max'], 0, ',', ' ') : __('shop.listing.to') }}"
                value="{{ $filters['price_max'] ?? '' }}"
                class="border border-border-DEFAULT px-3 py-2 text-sm outline-none focus:border-primary-DEFAULT"
            >
        </div>
        <button type="submit" class="mt-3 w-full border border-primary-DEFAULT py-2 text-sm font-medium transition-colors hover:bg-primary-DEFAULT hover:text-text-inverse">
            {{ __('shop.listing.apply_price') }}
        </button>
    </fieldset>

    @if (collect($filters)->only(['brands', 'sizes', 'colors', 'price_min', 'price_max', 'sale', 'new'])->filter()->isNotEmpty())
        <a href="{{ $formAction }}" class="inline-block text-sm text-text-muted underline underline-offset-4 hover:text-primary-DEFAULT">
            {{ __('shop.listing.clear_filters') }}
        </a>
    @endif
</form>
