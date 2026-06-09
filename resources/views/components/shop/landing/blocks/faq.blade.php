@php
    $settings = $block['settings'] ?? [];
    $style = $settings['style'] ?? 'accordion';
    $items = $block['items'] ?? [];
@endphp

@if ($items !== [])
    <x-shop.landing.section :block="$block" data-landing-faq>
        <div class="landing-container landing-container--narrow">
            @if (filled($block['title'] ?? null))
                <h2 class="landing-block-title">{{ $block['title'] }}</h2>
            @endif
            @if (filled($block['subtitle'] ?? null))
                <p class="landing-block-subtitle">{{ $block['subtitle'] }}</p>
            @endif

            @if ($style === 'cards')
                <div class="landing-faq landing-faq--cards">
                    @foreach ($items as $item)
                        <article class="landing-faq__card">
                            @if (filled($item['title'] ?? null))
                                <h3 class="landing-faq__question">{{ $item['title'] }}</h3>
                            @endif
                            @if (filled($item['content_html'] ?? null))
                                <div class="landing-prose landing-faq__answer">{!! $item['content_html'] !!}</div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @else
                <div class="landing-faq landing-faq--accordion">
                    @foreach ($items as $index => $item)
                        <details class="landing-faq__item" @if ($index === 0) open @endif>
                            <summary class="landing-faq__summary">
                                <span>{{ $item['title'] ?? '' }}</span>
                                <span class="landing-faq__icon" aria-hidden="true"></span>
                            </summary>
                            @if (filled($item['content_html'] ?? null))
                                <div class="landing-prose landing-faq__answer">{!! $item['content_html'] !!}</div>
                            @endif
                        </details>
                    @endforeach
                </div>
            @endif
        </div>
    </x-shop.landing.section>
@endif
