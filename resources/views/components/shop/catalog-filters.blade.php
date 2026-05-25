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

    @if (! empty($facets['colors']))
        <fieldset>
            <legend class="mb-3 text-xs font-medium uppercase tracking-[0.18em] text-text-muted">
                {{ __('shop.listing.color') }}
            </legend>
            <ul class="flex flex-wrap gap-2">
                @foreach ($facets['colors'] as $color)
                    <li>
                        <label class="group relative flex cursor-pointer items-center">
                            <input
                                type="checkbox"
                                name="colors[]"
                                value="{{ $color['id'] }}"
                                @checked(in_array($color['id'], $filters['colors'] ?? [], true))
                                class="peer sr-only"
                                onchange="this.form.requestSubmit()"
                            >
                            <span
                                class="size-8 rounded-full border-2 border-transparent ring-1 ring-border-DEFAULT transition group-hover:scale-105 peer-checked:ring-primary-DEFAULT peer-checked:ring-2"
                                style="background-color: {{ $color['hex'] ?? '#ccc' }}"
                                title="{{ $color['label'] }}"
                            ></span>
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

    @if (collect($filters)->only(['brands', 'colors', 'price_min', 'price_max', 'sale', 'new'])->filter()->isNotEmpty())
        <a href="{{ $formAction }}" class="inline-block text-sm text-text-muted underline underline-offset-4 hover:text-primary-DEFAULT">
            {{ __('shop.listing.clear_filters') }}
        </a>
    @endif
</form>
