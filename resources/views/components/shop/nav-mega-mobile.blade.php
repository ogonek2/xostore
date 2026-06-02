@props([
    'megaItems' => [],
])

<div class="space-y-5">
    @foreach ($megaItems as $item)
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-text-muted">
                {{ $item['label'] }}
            </p>

            <div class="mt-2 space-y-4">
                @foreach ($item['panels'] ?? [] as $panel)
                    <div>
                        @if (! empty($panel['title']))
                            <p class="text-xs font-medium text-text-DEFAULT/80">
                                {{ $panel['title'] }}
                            </p>
                        @endif

                        @if (! empty($panel['links']))
                            <ul @class(['mt-2 space-y-0.5', 'mt-0' => empty($panel['title'])])>
                                @foreach ($panel['links'] as $link)
                                    <li>
                                        <a
                                            href="{{ $link['url'] }}"
                                            class="block py-2 text-sm text-text-DEFAULT"
                                            data-mobile-nav-link
                                            @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                        >
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        @if (! empty($panel['view_all_url']))
                            <a
                                href="{{ $panel['view_all_url'] }}"
                                class="mt-2 block py-2 text-sm font-medium text-primary-DEFAULT"
                                data-mobile-nav-link
                            >
                                {{ $panel['view_all_label'] ?? __('shop.nav.catalog') }}
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
