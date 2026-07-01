@props([
    'megaItems' => [],
])

@php
    $linkLimit = (int) config('shop.mega_menu.mobile_link_limit', 8);
@endphp

<div class="space-y-2">
    @foreach ($megaItems as $item)
        @php
            $panels = $item['panels'] ?? [];
            $hasPanels = $panels !== [];
        @endphp

        @if ($hasPanels)
            <details class="group rounded-md border border-border-DEFAULT/60 bg-surface-muted/30" @if (count($megaItems) === 1) open @endif>
                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2.5 text-sm font-medium uppercase tracking-[0.12em] text-text-DEFAULT [&::-webkit-details-marker]:hidden">
                    <span>{{ $item['label'] }}</span>
                    <svg class="size-4 shrink-0 text-text-muted transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd" />
                    </svg>
                </summary>

                <div class="space-y-3 border-t border-border-DEFAULT/50 px-3 py-3">
                    @foreach ($panels as $panel)
                        @php
                            $links = $panel['links'] ?? [];
                            $brands = $panel['brands'] ?? [];
                            $visibleLinks = collect($links)->take($linkLimit);
                            $hiddenLinks = collect($links)->slice($linkLimit);
                            $viewAllUrl = $panel['view_all_url'] ?? null;
                            $viewAllInLinks = $viewAllUrl && collect($links)->contains(
                                fn (array $link) => rtrim((string) ($link['url'] ?? ''), '/') === rtrim((string) $viewAllUrl, '/')
                            );
                        @endphp

                        @if ($visibleLinks->isNotEmpty())
                            <div>
                                @if (! empty($panel['title']))
                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-muted">
                                        {{ $panel['title'] }}
                                    </p>
                                @endif

                                <ul class="space-y-0.5">
                                    @foreach ($visibleLinks as $link)
                                        <li>
                                            <a
                                                href="{{ $link['url'] }}"
                                                class="block rounded-sm py-2 text-sm uppercase tracking-[0.1em] text-text-DEFAULT transition-colors hover:text-primary-DEFAULT"
                                                data-mobile-nav-link
                                                @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                            >
                                                {{ $link['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                @if ($hiddenLinks->isNotEmpty())
                                    <details class="mt-1">
                                        <summary class="cursor-pointer py-1.5 text-xs font-medium uppercase tracking-[0.1em] text-primary-DEFAULT">
                                            {{ __('shop.nav.show_more') }}
                                        </summary>
                                        <ul class="mt-1 space-y-0.5 border-l border-border-DEFAULT/60 pl-3">
                                            @foreach ($hiddenLinks as $link)
                                                <li>
                                                    <a
                                                        href="{{ $link['url'] }}"
                                                        class="block py-1.5 text-sm uppercase tracking-[0.1em] text-text-muted"
                                                        data-mobile-nav-link
                                                        @if ($link['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                                                    >
                                                        {{ $link['label'] }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </details>
                                @endif
                            </div>
                        @endif

                        @if ($brands !== [])
                            <div>
                                <p class="mb-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-text-muted">
                                    {{ $panel['title'] ?: __('shop.nav.all_brands') }}
                                </p>

                                <x-shop.nav-mega-brands-mobile :brands="$brands" />

                                @if ($viewAllUrl && ! $viewAllInLinks)
                                    <a
                                        href="{{ $viewAllUrl }}"
                                        class="mt-2 inline-block text-xs font-medium uppercase tracking-[0.1em] text-primary-DEFAULT"
                                        data-mobile-nav-link
                                    >
                                        {{ $panel['view_all_label'] ?? __('shop.nav.all_brands') }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if ($viewAllUrl && $visibleLinks->isEmpty() && empty($brands))
                            <a
                                href="{{ $viewAllUrl }}"
                                class="block py-2 text-sm font-medium uppercase tracking-[0.12em] text-primary-DEFAULT"
                                data-mobile-nav-link
                            >
                                {{ $panel['view_all_label'] ?? __('shop.nav.view_all') }}
                            </a>
                        @endif
                    @endforeach

                    @if (! empty($item['url']))
                        <a
                            href="{{ $item['url'] }}"
                            class="block border-t border-border-DEFAULT/50 pt-3 text-sm font-medium uppercase tracking-[0.12em] text-primary-DEFAULT"
                            data-mobile-nav-link
                            @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
                        >
                            {{ __('shop.nav.view_all') }} — {{ $item['label'] }}
                        </a>
                    @endif
                </div>
            </details>
        @elseif (! empty($item['url']))
            <a
                href="{{ $item['url'] }}"
                class="flex items-center justify-between rounded-md border border-border-DEFAULT/60 px-3 py-2.5 text-sm font-medium uppercase tracking-[0.12em] text-text-DEFAULT"
                data-mobile-nav-link
                @if ($item['open_in_new_tab'] ?? false) target="_blank" rel="noopener noreferrer" @endif
            >
                {{ $item['label'] }}
            </a>
        @endif
    @endforeach
</div>
