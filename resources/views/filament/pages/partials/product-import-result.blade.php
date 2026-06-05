@php
    $result = $result ?? null;
@endphp

@if ($result)
    <style>
        .pi-result { margin-top: 0.5rem; }
        .pi-result__stats {
            display: grid;
            gap: 0.75rem;
            grid-template-columns: repeat(2, 1fr);
            margin: 0 0 1rem;
        }
        @media (min-width: 768px) {
            .pi-result__stats { grid-template-columns: repeat(4, 1fr); }
        }
        .pi-result__stat {
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #3f3f46;
            background: #27272a;
        }
        .pi-result__stat dt {
            margin: 0;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #a1a1aa;
        }
        .pi-result__stat dd {
            margin: 0.35rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            color: #fafafa;
        }
        .pi-result__msg {
            margin-top: 0.75rem;
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            max-height: 11rem;
            overflow-y: auto;
        }
        .pi-result__msg--warn {
            border: 1px solid #854d0e;
            background: #292419;
        }
        .pi-result__msg--err {
            border: 1px solid #991b1b;
            background: #2a1515;
        }
        .pi-result__msg h4 {
            margin: 0 0 0.5rem;
            font-size: 0.8125rem;
            font-weight: 700;
        }
        .pi-result__msg--warn h4 { color: #fcd34d; }
        .pi-result__msg--info h4 { color: #86efac; }
        .pi-result__msg--err h4 { color: #fca5a5; }
        .pi-result__msg--info {
            border: 1px solid #166534;
            background: #14251a;
        }
        .pi-result__msg ul {
            margin: 0;
            padding-left: 1.125rem;
            font-size: 0.8125rem;
            color: #d4d4d8;
        }
    </style>

    <div class="pi-result">
        <dl class="pi-result__stats">
            <div class="pi-result__stat">
                <dt>Создано</dt>
                <dd>{{ $result['created'] ?? 0 }}</dd>
            </div>
            <div class="pi-result__stat">
                <dt>Обновлено</dt>
                <dd>{{ $result['updated'] ?? 0 }}</dd>
            </div>
            <div class="pi-result__stat">
                <dt>Вариантов</dt>
                <dd>+{{ $result['variants_created'] ?? 0 }}</dd>
            </div>
            <div class="pi-result__stat">
                <dt>Пропущено</dt>
                <dd>{{ $result['skipped'] ?? 0 }}</dd>
            </div>
        </dl>

        @if (! empty($result['created_references']))
            <div class="pi-result__msg pi-result__msg--info">
                <h4>Автосоздано при импорте</h4>
                <ul>
                    @foreach ($result['created_references'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($result['warnings']))
            <div class="pi-result__msg pi-result__msg--warn">
                <h4>Предупреждения</h4>
                <ul>
                    @foreach ($result['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (! empty($result['errors']))
            <div class="pi-result__msg pi-result__msg--err">
                <h4>Ошибки</h4>
                <ul>
                    @foreach ($result['errors'] as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
