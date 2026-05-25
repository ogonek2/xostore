@props(['items'])

<nav aria-label="Breadcrumb" class="text-sm text-text-muted">
    <ol class="flex flex-wrap items-center gap-2">
        <li>
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" class="transition-colors hover:text-primary-DEFAULT">
                {{ config('shop.name') }}
            </a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-2" aria-hidden="true">
                <span class="text-border-strong">/</span>
            </li>
            <li @class(['text-primary-DEFAULT' => ! ($item['url'] ?? null)])>
                @if ($item['url'] ?? null)
                    <a href="{{ $item['url'] }}" class="transition-colors hover:text-primary-DEFAULT">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
