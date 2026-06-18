@php
    $preview = $preview ?? null;
@endphp

@if ($preview)
    <style>
        .pi-preview { font-size: 0.8125rem; line-height: 1.5; color: #d4d4d8; }
        .pi-preview__lead { margin: 0 0 1rem; color: #a1a1aa; }
        .pi-preview__scroll { overflow-x: auto; border: 1px solid #52525b; border-radius: 0.5rem; background: #18181b; }
        .pi-preview__table { width: 100%; min-width: 56rem; border-collapse: collapse; }
        .pi-preview__table th,
        .pi-preview__table td { padding: 0.5rem 0.75rem; text-align: left; vertical-align: top; border-bottom: 1px solid #3f3f46; }
        .pi-preview__table th { font-size: 0.6875rem; text-transform: uppercase; letter-spacing: 0.06em; color: #a1a1aa; background: #27272a; }
        .pi-preview__table tr:last-child td { border-bottom: none; }
        .pi-preview__sku { font-family: ui-monospace, Consolas, monospace; color: #fde68a; }
        .pi-preview__slug { font-family: ui-monospace, Consolas, monospace; color: #86efac; font-size: 0.75rem; }
        .pi-preview__badge { display: inline-block; padding: 0.125rem 0.375rem; border-radius: 0.25rem; font-size: 0.6875rem; font-weight: 600; }
        .pi-preview__badge--create { background: #14532d; color: #86efac; }
        .pi-preview__badge--update { background: #1e3a5f; color: #93c5fd; }
        .pi-preview__badge--new { background: #3f3f26; color: #fde68a; }
        .pi-preview__badge--exists { background: #27272a; color: #d4d4d8; border: 1px solid #52525b; }
        .pi-preview__ref { display: block; margin-bottom: 0.2rem; }
        .pi-preview__err { margin-top: 0.75rem; padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid #991b1b; background: #2a1515; color: #fca5a5; }
    </style>

    <div class="pi-preview">
        @if (! empty($preview['errors']))
            <div class="pi-preview__err">
                @foreach ($preview['errors'] as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if (! empty($preview['warnings']))
            <div class="pi-preview__err" style="border-color:#854d0e;background:#292419;color:#fcd34d;margin-bottom:1rem;">
                @foreach ($preview['warnings'] as $warning)
                    <div>{{ $warning }}</div>
                @endforeach
            </div>
        @endif

        @if (empty($preview['errors']))
            <p class="pi-preview__lead">
                Показано {{ count($preview['products'] ?? []) }} из {{ $preview['total_products'] ?? 0 }} товаров.
                Slug и связи рассчитаны так же, как при импорте.
            </p>

            <div class="pi-preview__scroll">
                <table class="pi-preview__table">
                    <thead>
                        <tr>
                            <th>SKU / строки</th>
                            <th>Название и slug</th>
                            <th>Связи</th>
                            <th>Варианты</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['products'] ?? [] as $row)
                            <tr>
                                <td>
                                    <span class="pi-preview__sku">{{ $row['sku'] }}</span>
                                    @if (! empty($row['auto_sku']))
                                        <div style="color:#a1a1aa;font-size:0.75rem;">SKU будет сгенерирован при импорте</div>
                                    @endif
                                    <div style="color:#71717a;font-size:0.75rem;">стр. {{ $row['lines'] }}</div>
                                    <span class="pi-preview__badge pi-preview__badge--{{ $row['action'] === 'create' ? 'create' : 'update' }}">
                                        {{ $row['action'] === 'create' ? 'Создать' : 'Обновить' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $row['name_pl'] ?? '—' }}</strong>
                                    @if (! empty($row['name_en']))
                                        <div style="color:#a1a1aa;">{{ $row['name_en'] }}</div>
                                    @endif
                                    @if (! empty($row['slug_pl']))
                                        <div class="pi-preview__slug">PL: {{ $row['slug_pl'] }}</div>
                                    @endif
                                    @if (! empty($row['slug_en']))
                                        <div class="pi-preview__slug">EN: {{ $row['slug_en'] }}</div>
                                    @endif
                                    @if (! empty($row['model_slug']))
                                        <div class="pi-preview__slug" style="margin-top:0.35rem;">model_slug: {{ $row['model_slug'] }}</div>
                                    @endif
                                    @if (! empty($row['color_label']))
                                        <div style="margin-top:0.35rem;">Цвет: {{ $row['color_label'] }}
                                            @if (! empty($row['color_slug']))
                                                <span class="pi-preview__slug">({{ $row['color_slug'] }})</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if (! empty($row['errors']))
                                        <div style="color:#fca5a5;margin-top:0.35rem;">{{ implode('; ', $row['errors']) }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if (! empty($row['brand']))
                                        <span class="pi-preview__ref">
                                            Бренд:
                                            <span class="pi-preview__badge {{ ($row['brand']['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $row['brand']['name'] }} ({{ $row['brand']['code'] }})
                                            </span>
                                        </span>
                                    @endif
                                    @foreach ($row['categories'] ?? [] as $category)
                                        <span class="pi-preview__ref">
                                            Кат{{ ! empty($category['is_primary']) ? '.*' : '' }}:
                                            <span class="pi-preview__badge {{ ($category['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $category['name'] }} ({{ $category['code'] }})
                                            </span>
                                        </span>
                                    @endforeach
                                    @foreach ($row['catalogs'] ?? [] as $catalog)
                                        <span class="pi-preview__ref">
                                            Каталог:
                                            <span class="pi-preview__badge {{ ($catalog['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $catalog['name'] }} ({{ $catalog['code'] }})
                                            </span>
                                        </span>
                                    @endforeach
                                    @foreach ($row['tags'] ?? [] as $tag)
                                        <span class="pi-preview__ref">
                                            Тег:
                                            <span class="pi-preview__badge {{ ($tag['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $tag['name'] }} ({{ $tag['code'] }})
                                            </span>
                                        </span>
                                    @endforeach
                                    @if (! empty($row['size_grid']))
                                        <span class="pi-preview__ref">
                                            Размеры:
                                            <span class="pi-preview__badge {{ ($row['size_grid']['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $row['size_grid']['name'] }} ({{ $row['size_grid']['code'] }})
                                            </span>
                                        </span>
                                    @endif
                                    @if (! empty($row['size_chart_preset']))
                                        <span class="pi-preview__ref">
                                            Мерки:
                                            <span class="pi-preview__badge {{ ($row['size_chart_preset']['exists'] ?? false) ? 'pi-preview__badge--exists' : 'pi-preview__badge--new' }}">
                                                {{ $row['size_chart_preset']['name'] }} ({{ $row['size_chart_preset']['code'] }})
                                            </span>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @forelse ($row['variants'] ?? [] as $variant)
                                        <div>
                                            {{ $variant['size'] ?? '—' }}:
                                            {{ $variant['price'] ?? '—' }} /
                                            ост. {{ $variant['stock'] ?? 0 }}
                                        </div>
                                    @empty
                                        <span style="color:#71717a;">—</span>
                                    @endforelse
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="pi-preview__lead" style="margin-top:0.75rem;">
                <span class="pi-preview__badge pi-preview__badge--exists">серый</span> — уже в базе,
                <span class="pi-preview__badge pi-preview__badge--new">жёлтый</span> — будет создано при импорте.
            </p>
        @endif
    </div>
@endif
