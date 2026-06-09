@props(['blocks' => []])

@foreach ($blocks as $block)
    @php
        $type = $block['type'] ?? 'spacer';
        $view = 'components.shop.landing.blocks.'.$type;
    @endphp

    @if (view()->exists($view))
        @include($view, ['block' => $block])
    @endif
@endforeach
