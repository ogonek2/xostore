@props([
    'label',
    'items' => [],
    'href' => null,
])

@if (count($items) > 0)
    <details class="group relative">
        <summary class="flex cursor-pointer list-none items-center gap-1 text-sm font-medium uppercase tracking-[0.12em] text-text-DEFAULT transition-colors hover:text-text-muted [&::-webkit-details-marker]:hidden">
            <span>{{ $label }}</span>
            <svg class="size-3.5 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
            </svg>
        </summary>
        <div class="absolute left-1/2 top-full z-40 mt-3 min-w-[12rem] -translate-x-1/2 rounded-lg border border-border-DEFAULT bg-surface-DEFAULT py-2 shadow-lg">
            @foreach ($items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="block px-4 py-2 text-sm uppercase tracking-[0.1em] text-text-DEFAULT transition-colors hover:bg-surface-muted"
                    @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
    </details>
@else
    <a href="{{ $href ?? '#' }}" class="text-sm font-medium uppercase tracking-[0.12em] text-text-DEFAULT transition-colors hover:text-text-muted">
        {{ $label }}
    </a>
@endif
