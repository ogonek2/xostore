@props([
    'section',
    'positionMap',
    'textClass',
])

@php
    $layout = $section['layout'] ?? 'single';
    $items = collect($section['items'] ?? [])->filter(fn ($item) => ! empty($item['image']))->values();
    // Grid layouts need a shared cell height + cover. Full-width single uses a capped frame + contain.
    $gridHeight = 'h-[18rem] sm:h-[23rem] lg:h-[32rem]';
    $singleHeight = 'h-[min(72vw,22rem)] sm:h-[min(56vw,26rem)] lg:h-[min(42vw,32rem)]';
@endphp

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
        image-fit="contain"
        :class="$singleHeight"
    />
@endif
