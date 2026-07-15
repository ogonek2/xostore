@props([
    'section',
    'positionMap',
    'textClass',
])

@php
    use App\Support\Shop\HeroBannerFrame;

    $layout = $section['layout'] ?? 'single';
    $items = collect($section['items'] ?? [])->filter(fn ($item) => ! empty($item['image']))->values();
    $heightPreset = $section['height_preset'] ?? 'auto';
    $imageFit = HeroBannerFrame::normalizeFit($section['image_fit'] ?? 'contain');
    $widthClass = HeroBannerFrame::widthClass($section['width_preset'] ?? 'full');
    $isAuto = HeroBannerFrame::isAutoHeight($heightPreset);
    $gridHeight = HeroBannerFrame::heightClass($isAuto ? 'lg' : $heightPreset);
    $singleHeight = HeroBannerFrame::heightClass($heightPreset);
@endphp

<div class="{{ $widthClass }}">
@if ($layout === 'two_columns')
    <div class="grid {{ $gridHeight }} gap-4 lg:grid-cols-2 lg:gap-6">
        @foreach ($items->take(2) as $item)
            <x-shop.hero-builder-tile
                :item="$item"
                :position-map="$positionMap"
                :text-class="$textClass"
                image-fit="cover"
                class="h-full"
            />
        @endforeach
    </div>
@elseif ($layout === 'three_columns')
    <div class="grid {{ $gridHeight }} gap-1 lg:grid-cols-3 lg:gap-1">
        @foreach ($items->take(3) as $item)
            <x-shop.hero-builder-tile
                :item="$item"
                :position-map="$positionMap"
                :text-class="$textClass"
                image-fit="cover"
                class="h-full"
            />
        @endforeach
    </div>
@elseif ($layout === 'feature_stack')
    @php
        $first = $items->get(0);
        $right = $items->slice(1, 2);
    @endphp
    <div class="grid {{ $gridHeight }} gap-1 lg:grid-cols-3 lg:gap-1">
        @if ($first)
            <div class="lg:col-span-2">
                <x-shop.hero-builder-tile
                    :item="$first"
                    :position-map="$positionMap"
                    :text-class="$textClass"
                    image-fit="cover"
                    class="h-full"
                />
            </div>
        @endif
        <div class="grid h-full gap-1 lg:grid-rows-2">
            @foreach ($right as $item)
                <x-shop.hero-builder-tile
                    :item="$item"
                    :position-map="$positionMap"
                    :text-class="$textClass"
                    image-fit="cover"
                    class="h-full"
                />
            @endforeach
        </div>
    </div>
@else
    <x-shop.hero-builder-tile
        :item="$items->first()"
        :position-map="$positionMap"
        :text-class="$textClass"
        :image-fit="$imageFit"
        :auto-height="$isAuto"
        :class="$isAuto ? 'w-full' : $singleHeight"
    />
@endif
</div>
