@props(['languages'])

@php
    $current = current_language();
@endphp

<details class="group relative">
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full bg-primary-muted px-3 py-1.5 text-sm font-medium text-text-inverse transition-colors hover:bg-primary-hover [&::-webkit-details-marker]:hidden">
        <span class="size-5 shrink-0 overflow-hidden rounded-full ring-1 ring-white/20" aria-hidden="true">
            @if ($current->flag === 'pl')
                <svg viewBox="0 0 20 20" class="size-full">
                    <rect width="20" height="20" fill="#fff"/>
                    <rect y="10" width="20" height="10" fill="#dc143c"/>
                </svg>
            @else
                <svg viewBox="0 0 20 20" class="size-full">
                    <rect width="20" height="20" fill="#012169"/>
                    <path d="M0 0 L20 12 L0 20 Z" fill="#fff" opacity=".9"/>
                    <path d="M20 0 L0 8 L20 20 Z" fill="#fff" opacity=".7"/>
                    <path d="M8 0 H12 V20 H8 Z" fill="#C8102E"/>
                    <path d="M0 8 H20 V12 H0 Z" fill="#C8102E"/>
                </svg>
            @endif
        </span>
        <span class="uppercase tracking-wide">{{ $current->code }}</span>
        <svg class="size-3.5 opacity-80 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
        </svg>
    </summary>
    <div class="absolute right-0 top-full z-40 mt-2 min-w-[8rem] overflow-hidden rounded-lg border border-border-DEFAULT bg-surface-DEFAULT py-1 shadow-lg">
        @foreach ($languages as $language)
            <a
                href="{{ route('home', ['locale' => $language->code]) }}"
                class="flex items-center gap-2 px-4 py-2 text-sm text-text-DEFAULT transition-colors hover:bg-surface-muted {{ $language->id === $current->id ? 'font-semibold' : '' }}"
                @if ($language->id === $current->id) aria-current="true" @endif
            >
                <span class="uppercase">{{ $language->code }}</span>
                <span class="text-text-muted">{{ $language->name }}</span>
            </a>
        @endforeach
    </div>
</details>
