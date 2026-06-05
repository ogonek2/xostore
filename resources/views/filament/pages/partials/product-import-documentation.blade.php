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
        margin-bottom: 1.25rem;
    }

    .pi-docs__table-scroll {
        overflow-x: auto;
        border-radius: 0.5rem;
        border: 1px solid #52525b;
        background: #18181b;
    }

    .pi-docs__table {
        width: 100%;
        min-width: 36rem;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .pi-docs__table col.col-key { width: 28%; }
    .pi-docs__table col.col-desc { width: 44%; }
    .pi-docs__table col.col-ex { width: 28%; }

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
        padding: 0.625rem 0.875rem;
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
        font-size: 0.8125rem;
        color: #fde68a;
        word-break: break-all;
    }

    .pi-docs__table .cell-ex {
        font-family: ui-monospace, Consolas, monospace;
        font-size: 0.8125rem;
        color: #a1a1aa;
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
</style>

<div class="pi-docs">
    <p class="pi-docs__lead">
        Загрузите Excel (.xlsx) или CSV (.csv) по шаблону. CSV из Excel (разделитель <span class="pi-docs__code">;</span>) тоже поддерживается.
        Импорт <strong>создаёт или обновляет</strong> товары по полю
        <span class="pi-docs__code">sku</span> (артикул). Обязательны только
        <span class="pi-docs__code">sku</span> и <span class="pi-docs__code">name_pl</span>.
    </p>

    <div class="pi-docs__grid pi-docs__grid--2">
        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Обязательные поля</h4>
            <ul class="pi-docs__list">
                <li><span class="pi-docs__code">sku</span> — уникальный артикул</li>
                <li><span class="pi-docs__code">name_pl</span> — название (польский)</li>
            </ul>
            <p class="pi-docs__note">Остальные колонки — по желанию. Справочники (категории, бренды, теги…) можно указывать названием — при отсутствии создаются автоматически.</p>
        </div>

        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Значения через запятую</h4>
            <p>
                Категории, каталоги, теги, размеры и цены можно перечислять в <strong>одной ячейке</strong>
                через запятую — не обязательно разбивать на много строк.
                Параллельные списки: одинаковый порядок элементов (1-й размер → 1-я цена → 1-й остаток).
            </p>
        </div>
    </div>

    <div class="pi-docs__table-block">
        <h4 class="pi-docs__box-title">Справочники — код или название</h4>
        <div class="pi-docs__table-scroll">
            <table class="pi-docs__table">
                <colgroup>
                    <col class="col-key">
                    <col class="col-desc">
                    <col class="col-ex">
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">Колонка</th>
                        <th scope="col">Описание</th>
                        <th scope="col">Пример</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cell-key">brand_code</td>
                        <td>Бренд: код или название. Нет в базе — создаётся автоматически</td>
                        <td class="cell-ex">Chanel</td>
                    </tr>
                    <tr>
                        <td class="cell-key">primary_category_code</td>
                        <td>Основная категория (код/название, автосоздание)</td>
                        <td class="cell-ex">Damskie</td>
                    </tr>
                    <tr>
                        <td class="cell-key">category_codes</td>
                        <td>Категории через запятую (код/название)</td>
                        <td class="cell-ex">Damskie, Akcesoria</td>
                    </tr>
                    <tr>
                        <td class="cell-key">catalog_codes</td>
                        <td>Каталоги через запятую (код/название)</td>
                        <td class="cell-ex">Wyprzedaż</td>
                    </tr>
                    <tr>
                        <td class="cell-key">tag_codes</td>
                        <td>Теги через запятую (код/название)</td>
                        <td class="cell-ex">nowość</td>
                    </tr>
                    <tr>
                        <td class="cell-key">size_grid_code</td>
                        <td>Пресет кнопок S/M/L (код/название)</td>
                        <td class="cell-ex">Odzież damska</td>
                    </tr>
                    <tr>
                        <td class="cell-key">size_chart_preset_code</td>
                        <td>Пресет таблицы мерок (код/название)</td>
                        <td class="cell-ex">Sukienki damskie</td>
                    </tr>
                    <tr>
                        <td class="cell-key">status</td>
                        <td>draft / published / archived</td>
                        <td class="cell-ex">draft</td>
                    </tr>
                    <tr>
                        <td class="cell-key">type</td>
                        <td>simple / variable</td>
                        <td class="cell-ex">variable</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="pi-docs__table-block">
        <h4 class="pi-docs__box-title">Варианты и размеры (через запятую или несколько строк)</h4>
        <div class="pi-docs__table-scroll">
            <table class="pi-docs__table">
                <colgroup>
                    <col class="col-key">
                    <col class="col-desc">
                    <col class="col-ex">
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">Колонка</th>
                        <th scope="col">Описание</th>
                        <th scope="col">Пример</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cell-key">variant_sizes</td>
                        <td>Размеры из пресета, через запятую</td>
                        <td class="cell-ex">s, m, l</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variant_prices</td>
                        <td>Цены (порядок как у размеров)</td>
                        <td class="cell-ex">1290, 1290, 1390</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variant_stocks</td>
                        <td>Остатки (порядок как у размеров)</td>
                        <td class="cell-ex">5, 3, 2</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variant_skus</td>
                        <td>SKU вариантов через запятую</td>
                        <td class="cell-ex">SKU-S, SKU-M, SKU-L</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variants</td>
                        <td>Компактно: размер:цена:остаток,…</td>
                        <td class="cell-ex">s:990:4, m:990:6, l:1090:2</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variant_size</td>
                        <td>Один размер или несколько: s,m,l</td>
                        <td class="cell-ex">m</td>
                    </tr>
                    <tr>
                        <td class="cell-key">variant_price / variant_stock</td>
                        <td>Один вариант в строке или списки через запятую</td>
                        <td class="cell-ex">1290 / 5</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="pi-docs__note">Альтернатива: несколько строк с одним <span class="pi-docs__code">sku</span> — по одному размеру в каждой строке.</p>
    </div>

    <div class="pi-docs__grid pi-docs__grid--2">
        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Тексты и SEO</h4>
            <p>
                Суффиксы <span class="pi-docs__code">_pl</span> и <span class="pi-docs__code">_en</span>:
                <span class="pi-docs__code">name_pl</span>,
                <span class="pi-docs__code">description_en</span>,
                <span class="pi-docs__code">meta_title_pl</span> и др.
                <span class="pi-docs__code">slug_pl</span> и <span class="pi-docs__code">slug_en</span> можно не заполнять:
                slug создаётся из полного названия и проверяется на уникальность.
                При совпадении добавляется артикул, цвет или суффикс <span class="pi-docs__code">-2</span>, <span class="pi-docs__code">-3</span>…
            </p>
        </div>

        <div class="pi-docs__box">
            <h4 class="pi-docs__box-title">Цвет и модель</h4>
            <ul class="pi-docs__list">
                <li><span class="pi-docs__code">model_slug</span> — общий slug цветовых вариантов</li>
                <li><span class="pi-docs__code">color_label</span>, <span class="pi-docs__code">color_hex</span></li>
                <li><span class="pi-docs__code">is_new</span>, <span class="pi-docs__code">is_ready_to_ship</span> — 1/0</li>
            </ul>
        </div>
    </div>

    <aside class="pi-docs__callout">
        <p class="pi-docs__callout-title">Перед импортом</p>
        <ul class="pi-docs__list">
            <li>Скачайте шаблон — в примерах: размеры через запятую и формат <span class="pi-docs__code">variants</span>.</li>
            <li>Строка 1 = имена колонок, строки 3+ = данные.</li>
            <li>Строка 2 с подсказками импорт пропускает.</li>
            <li>Фото и галерея добавляются вручную после импорта.</li>
        </ul>
    </aside>
</div>
