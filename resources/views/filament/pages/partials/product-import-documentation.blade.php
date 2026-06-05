<div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <h2 class="text-lg font-semibold text-gray-950 dark:text-white">Документация по импорту</h2>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
        Загрузите Excel-файл по шаблону. Импорт создаёт или обновляет товары по полю <strong>sku</strong> (артикул).
    </p>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Обязательные поля</h3>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-gray-200">
                <li><code>sku</code> — уникальный артикул товара</li>
                <li><code>name_pl</code> — название на польском (основной язык магазина)</li>
            </ul>
            <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">Все остальные колонки опциональны.</p>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Группировка строк</h3>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                Несколько строк с <strong>одинаковым sku</strong> — это один товар и несколько вариантов (размеров).
                Поля товара (название, категория, пресеты…) берутся из первой непустой строки группы.
                В каждой строке можно указать <code>variant_*</code> для размера и цены.
            </p>
        </div>
    </div>

    <div class="mt-6">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Справочники (коды из админки)</h3>
        <div class="mt-3 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-3 py-2">Колонка</th>
                        <th class="px-3 py-2">Описание</th>
                        <th class="px-3 py-2">Пример</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <tr><td class="px-3 py-2 font-mono">brand_code</td><td class="px-3 py-2">Бренд</td><td class="px-3 py-2">chanel</td></tr>
                    <tr><td class="px-3 py-2 font-mono">primary_category_code</td><td class="px-3 py-2">Основная категория</td><td class="px-3 py-2">women</td></tr>
                    <tr><td class="px-3 py-2 font-mono">category_codes</td><td class="px-3 py-2">Все категории, через запятую</td><td class="px-3 py-2">women, accessories</td></tr>
                    <tr><td class="px-3 py-2 font-mono">catalog_codes</td><td class="px-3 py-2">Каталоги</td><td class="px-3 py-2">main</td></tr>
                    <tr><td class="px-3 py-2 font-mono">size_grid_code</td><td class="px-3 py-2">Пресет кнопок размера (S/M/L)</td><td class="px-3 py-2">clothing_letter_women</td></tr>
                    <tr><td class="px-3 py-2 font-mono">size_chart_preset_code</td><td class="px-3 py-2">Пресет таблицы мерок (см)</td><td class="px-3 py-2">women_dresses_cm</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Тексты и SEO</h3>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                Для польского и английского: суффиксы <code>_pl</code> и <code>_en</code> —
                <code>name_pl</code>, <code>description_en</code>, <code>meta_title_pl</code> и т.д.
                Slug PL генерируется из названия, если не указан.
            </p>
        </div>
        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Варианты (размеры)</h3>
            <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-700 dark:text-gray-200">
                <li><code>variant_size</code> — код из пресета (s, m, 38…)</li>
                <li><code>variant_sku</code> — SKU варианта (необязательно)</li>
                <li><code>variant_price</code>, <code>variant_stock</code></li>
                <li><code>variant_is_default</code> — 1 для размера по умолчанию</li>
            </ul>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
        <p class="font-medium">Советы</p>
        <ul class="mt-2 list-disc space-y-1 pl-5">
            <li>Сначала скачайте шаблон — в нём все колонки и примеры на листе «Товары».</li>
            <li>Не удаляйте строку 1 с техническими именами колонок (sku, name_pl…).</li>
            <li>Строка 2 — подсказки на русском, импорт её пропускает.</li>
            <li>Фото, галерея и ручная таблица мерок — после импорта в карточке товара.</li>
            <li>Статус по умолчанию: <code>draft</code> (черновик).</li>
        </ul>
    </div>
</div>
