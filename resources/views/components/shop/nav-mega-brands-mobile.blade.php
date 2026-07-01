@props([
    'brands' => [],
])

@if ($brands !== [])
    <div class="flex gap-2 overflow-x-auto pb-1 [-ms-overflow-style:none] [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($brands as $brand)
            <a
                href="{{ $brand['url'] }}"
                class="inline-flex shrink-0 items-center gap-2 rounded-full border border-border-DEFAULT/70 bg-surface-muted/60 px-3 py-1.5 text-xs font-medium uppercase tracking-[0.1em] text-text-DEFAULT transition-colors hover:border-primary-DEFAULT/30 hover:bg-surface-DEFAULT"
                data-mobile-nav-link
            >
                @if (! empty($brand['logo']))
                    <img
                        src="{{ $brand['logo'] }}"
                        alt=""
                        class="h-4 max-w-[3.5rem] object-contain"
                        loading="lazy"
                    >
                @endif
                <span>{{ $brand['name'] }}</span>
            </a>
        @endforeach
    </div>
@endif
