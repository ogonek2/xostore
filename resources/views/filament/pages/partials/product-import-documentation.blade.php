@php
    use App\Support\Import\ProductImportColumns;

    $sections = ProductImportColumns::documentationSections();
@endphp

<style>
    .pi-docs {
        font-size: 0.875rem;
        line-height: 1.6;
        color: #d4d4d8;
    }

    .pi-docs__lead {
        margin: 0 0 1.25rem;
        padding: 0.875rem 1rem;
        border-radius: 0.5rem;
        border: 1px solid #3f3f46;
        background: #27272a;
        color: #e4e4e7;
    }

    .pi-docs__grid {
        display: grid;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (min-width: 768px) {
        .pi-docs__grid--2 {
            grid-template-columns: 1fr 1fr;
        }
    }

    .pi-docs__box {
        padding: 1rem 1.125rem;
        border-radius: 0.5rem;
        border: 1px solid #3f3f46;
        background: #1f1f23;
    }

    .pi-docs__box-title {
        margin: 0 0 0.75rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #a1a1aa;
    }

    .pi-docs__section-title {
        margin: 0 0 0.75rem;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #e4e4e7;
    }

    .pi-docs__box p {
        margin: 0;
        color: #d4d4d8;
    }

    .pi-docs__list {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .pi-docs__list li {
        position: relative;
        padding: 0.35rem 0 0.35rem 1.125rem;
        color: #d4d4d8;
    }

    .pi-docs__list li::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0.75rem;
        width: 0.375rem;
        height: 0.375rem;
        border-radius: 50%;
        background: #c9a962;
    }

    .pi-docs__note {
        margin: 0.75rem 0 0;
        font-size: 0.8125rem;
        color: #a1a1aa;
    }

    .pi-docs__code {
        display: inline-block;
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-family: ui-monospace, Consolas, monospace;
        font-size: 0.8125em;
        color: #fde68a;
        background: #3f3f26;
        border: 1px solid #57534e;
    }

    .pi-docs__table-block {
        margin-bottom: 1.5rem;
    }

    .pi-docs__table-scroll {
        overflow-x: auto;
        border-radius: 0.5rem;
        border: 1px solid #52525b;
        background: #18181b;
    }

    .pi-docs__table {
        width: 100%;
        min-width: 52rem;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pi-docs__table col.col-key { width: 14%; }
    .pi-docs__table col.col-title { width: 16%; }
    .pi-docs__table col.col-desc { width: 50%; }
    .pi-docs__table col.col-ex { width: 20%; }

    .pi-docs__table thead th {
        padding: 0.625rem 0.875rem;
        text-align: left;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #a1a1aa;
        background: #27272a;
        border-bottom: 2px solid #52525b;
    }

    .pi-docs__table tbody td {
        padding: 0.75rem 0.875rem;
        vertical-align: top;
        border-bottom: 1px solid #3f3f46;
        color: #e4e4e7;
    }

    .pi-docs__table tbody tr:last-child td {
        border-bottom: none;
    }

    .pi-docs__table tbody tr:nth-child(even) td {
        background: #1c1c1f;
    }

    .pi-docs__table tbody tr:hover td {
        background: #27272a;
    }

    .pi-docs__table .cell-key {
        font-family: ui-monospace, Consolas, monospace;
        font-size: 0.75rem;
        color: #fde68a;
        word-break: break-all;
    }

    .pi-docs__table .cell-title {
        font-weight: 600;
        font-size: 0.8125rem;
        color: #f4f4f5;
    }

    .pi-docs__table .cell-desc {
        font-size: 0.8125rem;
        color: #d4d4d8;
        line-height: 1.55;
    }

    .pi-docs__table .cell-ex {
        font-family: ui-monospace, Consolas, monospace;
        font-size: 0.75rem;
        color: #86efac;
        word-break: break-word;
    }

    .pi-docs__callout {
        padding: 1rem 1.125rem;
        border-radius: 0.5rem;
        border: 1px solid #854d0e;
        border-left-width: 4px;
        border-left-color: #c9a962;
        background: #292419;
    }

    .pi-docs__callout-title {
        margin: 0 0 0.625rem;
        font-size: 0.8125rem;
        font-weight: 700;
        color: #fcd34d;
    }

    .pi-docs__required {
        display: inline-block;
        margin-left: 0.25rem;
        padding: 0.0625rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.625rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #fecaca;
        background: #450a0a;
        border: 1px solid #991b1b;
        vertical-align: middle;
    }
</style>

<div class="pi-docs">
    <p class="pi-docs__lead">
        Справочник по <strong>всем переменным</strong> (колонкам) файла импорта. В Excel-шаблоне в первой строке — техническое имя переменной (как в таблице ниже),
        во второй — краткая подпись, в третьей — краткая подсказка. Поддерживаются файлы <strong>.xlsx</strong> и <strong>.csv</strong>.
        Импорт создаёт новые товары или обновляет существующие. Обязательна только колонка
        <span class="pi-docs__code">name_pl</span>.
        <span class="pi-docs__code">sku</span> можно не заполнять — артикул сгенерируется из названия.
    </p>

    <div class="pi-docs__grid pi-docs__grid--2">
        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Общие правила</h4>
            <ul class="pi-docs__list">
                <li>Одна строка = один размер товара <em>или</em> все размеры в одной строке (через запятую / колонку <span class="pi-docs__code">variants</span>).</li>
                <li>Несколько строк с одним <span class="pi-docs__code">sku</span> (или одним <span class="pi-docs__code">name_pl</span> без sku) = один товар, разные размеры.</li>
                <li>Справочники (бренд, категории, теги…) — код <strong>или</strong> название; если записи нет, она создаётся при импорте.</li>
                <li>Флаги (<span class="pi-docs__code">is_new</span>, <span class="pi-docs__code">is_featured</span>…) — <span class="pi-docs__code">1</span> = да, <span class="pi-docs__code">0</span> или пусто = нет.</li>
            </ul>
        </div>

        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Суффиксы _pl и _en</h4>
            <p>
                <span class="pi-docs__code">_pl</span> — польская версия сайта (основной язык магазина).
                <span class="pi-docs__code">_en</span> — английская. Поля с <span class="pi-docs__code">_pl</span> важнее:
                без польского названия строка не импортируется. Английские поля можно добавить позже в админке.
            </p>
        </div>
    </div>

    @foreach ($sections as $section)
        <div class="pi-docs__table-block">
            <h4 class="pi-docs__section-title">{{ $section['label'] }}</h4>
            <div class="pi-docs__table-scroll">
                <table class="pi-docs__table">
                    <colgroup>
                        <col class="col-key">
                        <col class="col-title">
                        <col class="col-desc">
                        <col class="col-ex">
                    </colgroup>
                    <thead>
                        <tr>
                            <th scope="col">Переменная</th>
                            <th scope="col">Название</th>
                            <th scope="col">Что это и как работает</th>
                            <th scope="col">Пример</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($section['rows'] as $row)
                            <tr>
                                <td class="cell-key">
                                    {{ $row['key'] }}
                                    @if ($row['key'] === 'name_pl')
                                        <span class="pi-docs__required">обяз.</span>
                                    @endif
                                </td>
                                <td class="cell-title">{{ $row['title'] }}</td>
                                <td class="cell-desc">{{ $row['description'] }}</td>
                                <td class="cell-ex">{{ $row['example'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

    <aside class="pi-docs__callout">
        <p class="pi-docs__callout-title">Перед импортом</p>
        <ul class="pi-docs__list">
            <li>Нажмите «Скачать шаблон Excel» — выберите нужные колонки. В файле есть лист «Справка» с краткими подсказками.</li>
            <li>Строка 1 = имена переменных, строка 2 = подписи, строка 3 = подсказки, с 4-й — ваши данные (примеры в шаблоне помечены <span class="pi-docs__code">status=example</span> и не импортируются).</li>
            <li>Для размеров выберите <strong>один</strong> способ: колонка <span class="pi-docs__code">variants</span> <em>или</em> связка <span class="pi-docs__code">variant_sizes</span> + <span class="pi-docs__code">variant_prices</span> + <span class="pi-docs__code">variant_stocks</span> <em>или</em> несколько строк с одним товаром.</li>
            <li>Фотографии и галерея в Excel не импортируются — добавьте их в карточке товара после импорта.</li>
        </ul>
    </aside>
</div>
