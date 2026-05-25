<?php

return [
    'name' => env('SHOP_NAME', 'XOStore'),
    'default_language' => env('SHOP_DEFAULT_LANGUAGE', 'pl'),
    'fallback_language' => env('SHOP_FALLBACK_LANGUAGE', 'en'),
    'currency' => env('SHOP_CURRENCY', 'PLN'),
    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'zł'),

    'contact' => [
        'email' => env('SHOP_CONTACT_EMAIL', 'hello@xostorebrand.com'),
        'phone' => env('SHOP_PHONE', '+48 22 000 00 00'),
    ],

    'social' => [
        'instagram' => env('SHOP_INSTAGRAM_URL', 'https://instagram.com'),
        'facebook' => env('SHOP_FACEBOOK_URL', 'https://facebook.com'),
        'pinterest' => env('SHOP_PINTEREST_URL', 'https://pinterest.com'),
    ],

    'listing' => [
        'per_page' => (int) env('SHOP_CATALOG_PER_PAGE', 24),
        'infinite_scroll' => true,
    ],

    'product' => [
        'translatable_fields' => [
            'name',
            'slug',
            'short_description',
            'description',
            'meta_title',
            'meta_description',
        ],
    ],

    'catalog' => [
        'translatable_fields' => [
            'name',
            'slug',
            'description',
            'meta_title',
            'meta_description',
        ],
    ],

    'category' => [
        'translatable_fields' => [
            'name',
            'slug',
            'description',
            'meta_title',
            'meta_description',
        ],
        'types' => [
            'women' => 'Damskie',
            'men' => 'Męskie',
            'accessories' => 'Akcesoria',
            'unisex' => 'Unisex',
        ],
    ],

    'brand' => [
        'translatable_fields' => ['name', 'slug', 'description'],
    ],

    'tag' => [
        'translatable_fields' => ['name'],
    ],

    'promotion' => [
        'translatable_fields' => [
            'title',
            'subtitle',
            'cta_label',
        ],
    ],

    'attribute_group' => [
        'translatable_fields' => ['name'],
    ],

    'attribute' => [
        'translatable_fields' => ['name'],
    ],

    'attribute_value' => [
        'translatable_fields' => ['label'],
    ],

    'size_grid' => [
        'translatable_fields' => ['name', 'description'],
    ],

    /*
    | Homepage category showcase (horizontal cards block).
    | label / sublabel keys → lang/shop.php categories.labels|sublabels
    */
    'homepage_showcase' => [
        [
            'category_code' => 'women',
            'label' => 'clothing',
            'sublabel' => 'for_women',
            'image' => 'images/categories/women.jpg',
        ],
        [
            'category_code' => 'men',
            'label' => 'clothing',
            'sublabel' => 'for_men',
            'image' => 'images/categories/men.jpg',
        ],
        [
            'category_code' => 'accessories',
            'label' => 'accessories',
            'image' => 'images/categories/accessories.jpg',
        ],
        [
            'category_code' => 'women-shoes',
            'label' => 'footwear',
            'sublabel' => 'for_women',
            'image' => 'images/categories/shoes.jpg',
        ],
    ],
];
