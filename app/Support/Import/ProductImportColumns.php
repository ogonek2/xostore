<?php

namespace App\Support\Import;

final class ProductImportColumns
{
    /** @var list<string> */
    public const REQUIRED = ['name_pl'];

    /**
     * Колонки шаблона по умолчанию (без дублей и редко используемых полей).
     *
     * @return list<string>
     */
    public static function defaultTemplateKeys(): array
    {
        return [
            'sku',
            'name_pl',
            'name_en',
            'status',
            'type',
            'base_price',
            'compare_at_price',
            'brand_code',
            'primary_category_code',
            'category_codes',
            'catalog_codes',
            'size_grid_code',
            'size_chart_preset_code',
            'model_slug',
            'color_code',
            'color_label',
            'color_hex',
            'short_description_pl',
            'description_pl',
            'is_new',
            'is_featured',
            'is_ready_to_ship',
            'variant_sizes',
            'variant_prices',
            'variant_stocks',
            'variants',
        ];
    }

    /**
     * Все поддерживаемые колонки импорта.
     *
     * @return list<string>
     */
    public static function keys(): array
    {
        return [
            'sku',
            'name_pl',
            'name_en',
            'slug_pl',
            'slug_en',
            'subtitle_pl',
            'subtitle_en',
            'short_description_pl',
            'short_description_en',
            'description_pl',
            'description_en',
            'fit_description_pl',
            'fit_description_en',
            'fabric_description_pl',
            'fabric_description_en',
            'meta_title_pl',
            'meta_title_en',
            'meta_description_pl',
            'meta_description_en',
            'status',
            'type',
            'brand_code',
            'primary_category_code',
            'category_codes',
            'catalog_codes',
            'tag_codes',
            'size_grid_code',
            'size_chart_preset_code',
            'base_price',
            'compare_at_price',
            'model_slug',
            'color_code',
            'color_label',
            'color_slug',
            'color_hex',
            'is_featured',
            'is_new',
            'is_ready_to_ship',
            'custom_tailoring_enabled',
            'weight_grams',
            'sort_order',
            'published_at',
            'variants',
            'variant_sizes',
            'variant_prices',
            'variant_stocks',
            'variant_skus',
            'variant_compare_at_prices',
            'variant_barcodes',
            'variant_defaults',
            'variant_size',
            'variant_sku',
            'variant_price',
            'variant_compare_at_price',
            'variant_stock',
            'variant_barcode',
            'variant_is_default',
        ];
    }

    /**
     * Группы для выбора колонок в шаблоне.
     *
     * @return array<string, array{label: string, columns: list<string>}>
     */
    public static function groups(): array
    {
        return [
            'essential' => [
                'label' => 'Основное',
                'columns' => ['sku', 'name_pl', 'name_en', 'status', 'type', 'base_price', 'compare_at_price'],
            ],
            'catalog' => [
                'label' => 'Каталог и бренд',
                'columns' => ['brand_code', 'primary_category_code', 'category_codes', 'catalog_codes', 'tag_codes'],
            ],
            'sizes' => [
                'label' => 'Размеры',
                'columns' => ['size_grid_code', 'size_chart_preset_code', 'variants', 'variant_sizes', 'variant_prices', 'variant_stocks', 'variant_skus', 'variant_compare_at_prices', 'variant_barcodes', 'variant_defaults'],
            ],
            'colors' => [
                'label' => 'Модель и цвет',
                'columns' => ['model_slug', 'color_code', 'color_label', 'color_hex'],
            ],
            'descriptions' => [
                'label' => 'Описания',
                'columns' => ['subtitle_pl', 'subtitle_en', 'short_description_pl', 'short_description_en', 'description_pl', 'description_en', 'fit_description_pl', 'fit_description_en', 'fabric_description_pl', 'fabric_description_en'],
            ],
            'seo' => [
                'label' => 'SEO',
                'columns' => ['slug_pl', 'slug_en', 'meta_title_pl', 'meta_title_en', 'meta_description_pl', 'meta_description_en'],
            ],
            'flags' => [
                'label' => 'Флаги и прочее',
                'columns' => ['is_featured', 'is_new', 'is_ready_to_ship', 'custom_tailoring_enabled', 'weight_grams', 'sort_order', 'published_at'],
            ],
            'variants_legacy' => [
                'label' => 'Варианты (устаревшие одиночные колонки)',
                'columns' => ['variant_size', 'variant_sku', 'variant_price', 'variant_compare_at_price', 'variant_stock', 'variant_barcode', 'variant_is_default'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function checkboxOptions(): array
    {
        $options = [];

        foreach (static::groups() as $group) {
            foreach ($group['columns'] as $key) {
                $options[$key] = static::labelsRu()[$key] ?? $key;
            }
        }

        return $options;
    }

    /**
     * Подробные подсказки для листа «Справка» в шаблоне и страницы документации.
     *
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return [
            'sku' => 'Уникальный артикул (SKU) товара в вашей системе учёта. По нему импорт понимает: создать новый товар или обновить существующий. Если оставить пустым — артикул сгенерируется автоматически из польского названия (name_pl). Несколько строк с одним и тем же SKU (или с одним name_pl без SKU) объединяются в один товар — каждая строка может описывать отдельный размер.',
            'name_pl' => 'Обязательное поле. Полное название товара на польском языке — именно оно показывается покупателям на сайте по умолчанию. Используется для автогенерации артикула (если SKU пустой), URL-адреса страницы (slug) и поиска в каталоге. Без этого поля строка не будет импортирована.',
            'name_en' => 'Название товара на английском языке. Отображается на сайте при переключении языка на EN. Если не заполнено — можно добавить позже в админке или через автоперевод с польского.',
            'slug_pl' => 'Часть URL-адреса страницы товара на польской версии сайта (например: /produkt/sukienka-wieczorowa). Заполнять необязательно: если ячейка пустая, адрес создаётся автоматически из name_pl. При совпадении с другим товаром к адресу добавляется суффикс (-2, -3…) или артикул.',
            'slug_en' => 'URL-адрес английской версии страницы товара. Необязательно. Если пусто — генерируется из name_en, а при его отсутствии — из name_pl.',
            'subtitle_pl' => 'Короткая дополнительная строка под основным названием на польском (подзаголовок). Например: «Kolekcja wiosenna 2026» или «Limitowana edycja». Необязательное поле.',
            'subtitle_en' => 'Подзаголовок на английском — аналог subtitle_pl для EN-версии сайта. Необязательно.',
            'short_description_pl' => 'Краткое описание на польском: 1–3 предложения для карточки товара и превью в каталоге. Не дублирует полное описание — только самое важное о товаре.',
            'short_description_en' => 'Краткое описание на английском. Показывается в каталоге и на карточке при выборе языка EN.',
            'description_pl' => 'Полное описание товара на польском: состав, особенности, уход, детали кроя и т.д. Отображается на странице товара в блоке описания.',
            'description_en' => 'Полное описание на английском — развёрнутый текст на странице товара для EN-версии.',
            'fit_description_pl' => 'Текст о посадке изделия на польском: «dopasowana», «luźna», ростовка, рекомендации по размеру. Показывается в отдельном блоке на карточке товара (вкладка «Посадка»).',
            'fit_description_en' => 'Описание посадки на английском — для EN-версии карточки товара.',
            'fabric_description_pl' => 'Описание ткани или материала на польском: состав в процентах, свойства, уход. Для сумок — тип кожи, фурнитура. Блок «Ткань / материал» на сайте.',
            'fabric_description_en' => 'Описание материала на английском — аналог fabric_description_pl.',
            'meta_title_pl' => 'SEO-заголовок для поисковых систем (польская версия). Если пусто — подставляется название товара. Рекомендуется до 60 символов.',
            'meta_title_en' => 'SEO-заголовок для английской версии страницы. Необязательно.',
            'meta_description_pl' => 'SEO-описание (meta description) на польском — краткий текст для сниппета в Google. Обычно 120–160 символов.',
            'meta_description_en' => 'SEO-описание на английском для поисковиков.',
            'status' => 'Статус публикации товара. draft — черновик, не виден на сайте. published — опубликован и доступен покупателям. archived — в архиве, скрыт с витрины. Значение example в шаблоне — служебное, такие строки импорт пропускает.',
            'type' => 'Тип товара в каталоге. variable — товар с выбором размера (одежда, обувь, сумки с S/M/L). simple — один вариант без выбора размера (аксессуар one size, единственный размер). Влияет на отображение кнопок размеров на карточке.',
            'brand_code' => 'Бренд товара. Укажите код из админки (например chanel) или название (Chanel) — регистр не важен. Если бренда ещё нет в базе, он будет создан автоматически при импорте.',
            'primary_category_code' => 'Главная категория товара — определяет основной раздел на сайте и хлебные крошки. Код или название: women, men, accessories, Damskie и т.д. Создаётся автоматически, если отсутствует.',
            'category_codes' => 'Дополнительные категории через запятую. Товар может быть в нескольких разделах одновременно. Формат: код или название каждой категории, разделитель — запятая.',
            'catalog_codes' => 'Каталоги / витрины — специальные подборки на сайте (главная, распродажа, новинки). Перечислите коды или названия через запятую: main, trendy, Wyprzedaż.',
            'tag_codes' => 'Теги для фильтрации и маркировки (новинка, sale, bestseller). Коды или названия через запятую. Отсутствующие теги создаются при импорте.',
            'size_grid_code' => 'Пресет кнопок выбора размера на карточке товара (НЕ таблица мерок!). Определяет, какие размеры покупатель может нажать: clothing_letter_women (S/M/L для одежды), footwear_eu (обувь), bags_sml (сумки S/M/L), accessories_one_size (без размера). Код или название пресета из раздела «Размерные сетки».',
            'size_chart_preset_code' => 'Пресет таблицы мерок в сантиметрах (обхват груди, талии, бёдер) — отдельный блок на странице товара. Для одежды — да, для сумок и аксессуаров обычно не нужен. Код из «Пресеты таблиц мерок» (например women_dresses_cm, men_tops_cm) или название.',
            'base_price' => 'Базовая цена товара в PLN (злотых). Для товаров type=variable может служить ценой по умолчанию, если в вариантах цены не указаны. Число без символа валюты: 1290 или 1290.50.',
            'compare_at_price' => '«Старая» цена для отображения скидки — зачёркнутая цена рядом с актуальной. Должна быть выше base_price. Необязательно. Пример: было 1590, стало 1290.',
            'model_slug' => 'Связь цветовых вариантов ОДНОЙ модели. Заполняйте ТОЛЬКО если импортируете один и тот же товар в разных цветах (одно платье — чёрное и бежевое): укажите одинаковый model_slug, разные SKU и цвет. Для разных моделей оставьте пустым — иначе на сайте они будут показаны как «другие цвета» друг друга.',
            'color_code' => 'Существующий цвет из справочника «Цвета». Код (czarny) или название (Czarny) — регистр не важен. Если цвета нет в базе, он будет создан автоматически.',
            'color_label' => 'Новый цвет: название на польском (Czarny, Beżowy). Создаёт запись в справочнике с автопереводом EN и генерацией кода. Альтернатива color_code — не заполняйте оба, если не нужно.',
            'color_slug' => '[Устарело] Используйте color_code. Технический код цвета из справочника.',
            'color_hex' => 'HEX-образец цвета (#1A1A1A или 1A1A1A). При создании нового цвета задаёт оттенок в справочнике и на карточке товара.',
            'is_featured' => 'Флаг «Рекомендованный товар». 1 или true — товар может попадать в блоки «Рекомендуем» на главной. 0 или пусто — обычный товар.',
            'is_new' => 'Флаг «Новинка». 1 — на карточке показывается бейдж «Новинка». 0 — без бейджа.',
            'is_ready_to_ship' => 'Флаг «В наличии / готов к отправке». 1 — товар отмечен как доступный для быстрой отправки. 0 — без этой отметки (не путать с остатками на складе).',
            'custom_tailoring_enabled' => 'Разрешить индивидуальный пошив для этого товара. 1 — на сайте появится опция заказа по меркам. 0 — стандартная покупка по размерам из пресета.',
            'weight_grams' => 'Вес товара в граммах для расчёта доставки. Целое число: 450 для лёгкой блузки, 1200 для пальто.',
            'sort_order' => 'Порядок сортировки в списках админки и на сайте (где применяется). Меньшее число — выше в списке. 0 — по умолчанию.',
            'published_at' => 'Дата и время публикации. Если status=published и поле пустое — товар считается опубликованным сразу. Формат: 2026-06-15 или 2026-06-15 10:30:00.',
            'variants' => 'Самый компактный способ задать размеры, цены и остатки в одной ячейке. Формат: размер:цена:остаток через запятую. Если эта колонка заполнена — отдельные variant_sizes / variant_prices / variant_stocks в той же строке игнорируются. Размеры должны существовать в выбранном size_grid_code.',
            'variant_sizes' => 'Список размеров через запятую — в том же порядке, что variant_prices и variant_stocks. Коды размеров из пресета size_grid_code: s,m,l или 25,30,35 для сумок. Альтернатива колонке variants.',
            'variant_prices' => 'Цены каждого размера в PLN через запятую. Первое число — цена первого размера из variant_sizes, второе — второго и т.д. Порядок обязан совпадать с variant_sizes.',
            'variant_stocks' => 'Остатки на складе для каждого размера через запятую. Порядок как у variant_sizes. 0 — размер есть, но нет в наличии.',
            'variant_skus' => 'Отдельные артикулы для каждого размера (необязательно). Через запятую в порядке размеров. Если пусто — формируются автоматически: ОСНОВНОЙ-SKU-РАЗМЕР.',
            'variant_compare_at_prices' => 'Старые (зачёркнутые) цены для каждого размера через запятую. Порядок как у variant_sizes. Необязательно.',
            'variant_barcodes' => 'Штрихкоды (EAN) для каждого размера через запятую. Для учёта и маркетплейсов. Порядок как у размеров.',
            'variant_defaults' => 'Какой размер выбран по умолчанию на карточке. Одно значение из списка размеров: m или l. Если пусто — первый размер в списке.',
            'variant_size' => '[Устаревший формат] Один размер в строке. Используйте variant_sizes или variants. Оставлен для совместимости со старыми файлами. При импорте нескольких строк с одним SKU каждая строка = один размер.',
            'variant_sku' => '[Устаревший формат] SKU одного варианта в строке. Предпочтительнее variant_skus или автогенерация.',
            'variant_price' => '[Устаревший формат] Цена одного варианта в строке. Предпочтительнее variant_prices или variants.',
            'variant_compare_at_price' => '[Устаревший формат] Старая цена одного варианта в строке.',
            'variant_stock' => '[Устаревший формат] Остаток одного варианта в строке.',
            'variant_barcode' => '[Устаревший формат] Штрихкод одного варианта в строке.',
            'variant_is_default' => '[Устаревший формат] 1 — этот вариант по умолчанию. Предпочтительнее variant_defaults.',
        ];
    }

    /**
     * Примеры значений для документации и шаблона.
     *
     * @return array<string, string>
     */
    public static function examples(): array
    {
        return [
            'sku' => 'DRESS-001-BLACK',
            'name_pl' => 'Sukienka wieczorowa',
            'name_en' => 'Evening dress',
            'slug_pl' => 'sukienka-wieczorowa',
            'slug_en' => 'evening-dress',
            'subtitle_pl' => 'Kolekcja wiosenna 2026',
            'subtitle_en' => 'Spring 2026 collection',
            'short_description_pl' => 'Elegancka sukienka na wieczór.',
            'short_description_en' => 'Elegant evening dress.',
            'description_pl' => 'Sukienka z wiskozy, podszewka poliestrowa…',
            'description_en' => 'Viscose dress with polyester lining…',
            'fit_description_pl' => 'Dopasowana w talii, lekko rozkloszowana.',
            'fit_description_en' => 'Fitted at the waist, slightly flared.',
            'fabric_description_pl' => '70% wiskoza, 30% poliester',
            'fabric_description_en' => '70% viscose, 30% polyester',
            'meta_title_pl' => 'Sukienka wieczorowa — sklep',
            'meta_title_en' => 'Evening dress — shop',
            'meta_description_pl' => 'Elegancka sukienka wieczorowa. Dostawa w całej Polsce.',
            'meta_description_en' => 'Elegant evening dress. Delivery across Poland.',
            'status' => 'published',
            'type' => 'variable',
            'brand_code' => 'chanel',
            'primary_category_code' => 'women',
            'category_codes' => 'women, dresses',
            'catalog_codes' => 'main, trendy',
            'tag_codes' => 'nowość, bestseller',
            'size_grid_code' => 'clothing_letter_women',
            'size_chart_preset_code' => 'women_dresses_cm',
            'base_price' => '1290',
            'compare_at_price' => '1590',
            'model_slug' => 'evening-dress-classic',
            'color_code' => 'czarny',
            'color_hex' => '#1A1A1A',
            'is_featured' => '1',
            'is_new' => '1',
            'is_ready_to_ship' => '1',
            'custom_tailoring_enabled' => '0',
            'weight_grams' => '450',
            'sort_order' => '10',
            'published_at' => '2026-06-15 10:00:00',
            'variants' => 's:1290:5, m:1290:3, l:1390:2',
            'variant_sizes' => 's, m, l',
            'variant_prices' => '1290, 1290, 1390',
            'variant_stocks' => '5, 3, 2',
            'variant_skus' => 'DRESS-S, DRESS-M, DRESS-L',
            'variant_compare_at_prices' => '1590, 1590, 1690',
            'variant_barcodes' => '5901234123457, 5901234123464',
            'variant_defaults' => 'm',
            'variant_size' => 'm',
            'variant_sku' => 'DRESS-M',
            'variant_price' => '1290',
            'variant_compare_at_price' => '1590',
            'variant_stock' => '3',
            'variant_barcode' => '5901234123457',
            'variant_is_default' => '1',
        ];
    }

    /**
     * Строки документации по группам для страницы импорта.
     *
     * @return array<string, array{label: string, rows: list<array{key: string, title: string, description: string, example: string}>}>
     */
    public static function documentationSections(): array
    {
        $labels = static::labelsRu();
        $descriptions = static::descriptions();
        $examples = static::examples();
        $sections = [];

        foreach (static::groups() as $groupKey => $group) {
            $rows = [];

            foreach ($group['columns'] as $key) {
                $rows[] = [
                    'key' => $key,
                    'title' => $labels[$key] ?? $key,
                    'description' => $descriptions[$key] ?? '—',
                    'example' => $examples[$key] ?? '—',
                ];
            }

            $sections[$groupKey] = [
                'label' => $group['label'],
                'rows' => $rows,
            ];
        }

        return $sections;
    }

    /**
     * Понятные русские названия полей (для документации и выбора колонок).
     *
     * @return array<string, string>
     */
    public static function labelsRu(): array
    {
        return [
            'sku' => 'Артикул (SKU)',
            'name_pl' => 'Название на польском',
            'name_en' => 'Название на английском',
            'slug_pl' => 'URL-адрес (польский)',
            'slug_en' => 'URL-адрес (английский)',
            'subtitle_pl' => 'Подзаголовок (польский)',
            'subtitle_en' => 'Подзаголовок (английский)',
            'short_description_pl' => 'Краткое описание (польский)',
            'short_description_en' => 'Краткое описание (английский)',
            'description_pl' => 'Полное описание (польский)',
            'description_en' => 'Полное описание (английский)',
            'fit_description_pl' => 'Посадка (польский)',
            'fit_description_en' => 'Посадка (английский)',
            'fabric_description_pl' => 'Ткань / материал (польский)',
            'fabric_description_en' => 'Ткань / материал (английский)',
            'meta_title_pl' => 'SEO-заголовок (польский)',
            'meta_title_en' => 'SEO-заголовок (английский)',
            'meta_description_pl' => 'SEO-описание (польский)',
            'meta_description_en' => 'SEO-описание (английский)',
            'status' => 'Статус публикации',
            'type' => 'Тип товара',
            'brand_code' => 'Бренд',
            'primary_category_code' => 'Главная категория',
            'category_codes' => 'Дополнительные категории',
            'catalog_codes' => 'Каталоги / витрины',
            'tag_codes' => 'Теги',
            'size_grid_code' => 'Пресет кнопок размеров',
            'size_chart_preset_code' => 'Пресет таблицы мерок',
            'base_price' => 'Цена (PLN)',
            'compare_at_price' => 'Старая цена (PLN)',
            'model_slug' => 'Slug модели (связь цветов)',
            'color_code' => 'Код цвета (справочник)',
            'color_label' => 'Название нового цвета',
            'color_slug' => 'Slug цвета (устар.)',
            'color_hex' => 'Цвет (HEX)',
            'is_featured' => 'Рекомендованный товар',
            'is_new' => 'Новинка',
            'is_ready_to_ship' => 'В наличии / готов к отправке',
            'custom_tailoring_enabled' => 'Индивидуальный пошив',
            'weight_grams' => 'Вес (граммы)',
            'sort_order' => 'Порядок сортировки',
            'published_at' => 'Дата публикации',
            'variants' => 'Варианты (компактный формат)',
            'variant_sizes' => 'Размеры вариантов',
            'variant_prices' => 'Цены вариантов',
            'variant_stocks' => 'Остатки вариантов',
            'variant_skus' => 'Артикулы вариантов',
            'variant_compare_at_prices' => 'Старые цены вариантов',
            'variant_barcodes' => 'Штрихкоды вариантов',
            'variant_defaults' => 'Размер по умолчанию',
            'variant_size' => '[устар.] Размер в строке',
            'variant_sku' => '[устар.] Артикул варианта в строке',
            'variant_price' => '[устар.] Цена варианта в строке',
            'variant_compare_at_price' => '[устар.] Старая цена в строке',
            'variant_stock' => '[устар.] Остаток в строке',
            'variant_barcode' => '[устар.] Штрихкод в строке',
            'variant_is_default' => '[устар.] Вариант по умолчанию',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function labelsPl(): array
    {
        $labels = static::labelsRu();

        return array_merge($labels, [
            'name_pl' => 'Название PL *',
            'name_en' => 'Название EN',
        ]);
    }

    /**
     * @return array<string, list<string>>
     */
    public static function aliases(): array
    {
        return [
            'sku' => ['sku', 'артикул', 'artikul', 'article'],
            'name_pl' => ['name_pl', 'name', 'nazwa', 'название', 'nazwa_pl'],
        ];
    }
}
