@props([
    'item',
])

@if (($item['type'] ?? '') === 'mega' && array_key_exists('mega_index', $item))
    <button
        type="button"
        class="nav-mega-trigger flex items-center gap-1 text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted"
        data-mega-trigger="{{ $item['mega_index'] }}"
        aria-expanded="false"
        aria-haspopup="true"
    >
        <span>{{ $item['label'] }}</span>
        <svg class="nav-mega-chevron size-3.5 transition-transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
        </svg>
    </button>
@elseif (! empty($item['children']))
    <x-shop.nav-dropdown
        :label="$item['label']"
        :items="collect($item['children'])->map(fn ($child) => [
            'label' => $child['label'],
            'url' => $child['url'] ?? '#',
            'open_in_new_tab' => $child['open_in_new_tab'] ?? false,
        ])->all()"
        :href="$item['url']"
    />
@elseif (! empty($item['url']))
    <a
        href="{{ $item['url'] }}"
        class="text-sm font-medium text-text-DEFAULT transition-colors hover:text-text-muted"
        @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
    >
        {{ $item['label'] }}
    </a>
@endif
