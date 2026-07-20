<?php

return [
    'name' => env('SHOP_NAME', 'XOStore'),

    'seo' => [
        'default_title' => env('SHOP_SEO_DEFAULT_TITLE'),
        'default_description' => env('SHOP_SEO_DEFAULT_DESCRIPTION'),
        'og_image' => env('SHOP_SEO_OG_IMAGE'),
    ],
    'default_language' => env('SHOP_DEFAULT_LANGUAGE', 'pl'),
    'fallback_language' => env('SHOP_FALLBACK_LANGUAGE', 'en'),
    'currency' => env('SHOP_CURRENCY', 'PLN'),
    'currency_symbol' => env('SHOP_CURRENCY_SYMBOL', 'zł'),

    'contact' => [
        'email' => env('SHOP_CONTACT_EMAIL', 'hello@xostorebrand.com'),
        'phone' => env('SHOP_PHONE', '+48 22 000 00 00'),
    ],

    'newsletter' => [
        'default_group_slug' => env('SHOP_NEWSLETTER_DEFAULT_GROUP', 'website'),
        'from_name' => env('SHOP_NEWSLETTER_FROM_NAME', env('SHOP_NAME', 'XOStore')),
        'from_address' => env('SHOP_NEWSLETTER_FROM_ADDRESS', env('MAIL_FROM_ADDRESS')),
    ],

    'chat' => [
        'provider' => env('SHOP_CHAT_PROVIDER'), // telegram | respondio | jivo | crisp | tawk
        'telegram_url' => env('SHOP_CHAT_TELEGRAM_URL'),
        'respondio_channel_id' => env('SHOP_CHAT_RESPONDIO_CHANNEL_ID'),
        'jivo_widget_id' => env('SHOP_CHAT_JIVO_WIDGET_ID'),
        'crisp_website_id' => env('SHOP_CHAT_CRISP_WEBSITE_ID'),
        'tawk_property_id' => env('SHOP_CHAT_TAWK_PROPERTY_ID'),
        'tawk_widget_id' => env('SHOP_CHAT_TAWK_WIDGET_ID'),
    ],

    'telegram' => [
        'enabled' => (bool) env('TELEGRAM_NOTIFICATIONS_ENABLED', false),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_ADMIN_CHAT_ID'),
        'verify_ssl' => env('TELEGRAM_VERIFY_SSL'),
        'admin_url' => env('TELEGRAM_ADMIN_URL'),
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

    'media' => [
        'max_upload_bytes' => (int) env('SHOP_MEDIA_MAX_UPLOAD_BYTES', 1_048_576),
        'max_width' => (int) env('SHOP_MEDIA_MAX_WIDTH', 2000),
        'max_height' => (int) env('SHOP_MEDIA_MAX_HEIGHT', 2500),
        'jpeg_quality' => (int) env('SHOP_MEDIA_JPEG_QUALITY', 85),
        'cdn_resize' => (bool) env('SHOP_MEDIA_CDN_RESIZE', true),
        'card_width' => (int) env('SHOP_MEDIA_CARD_WIDTH', 640),
        'gallery_width' => (int) env('SHOP_MEDIA_GALLERY_WIDTH', 1280),
        'thumb_width' => (int) env('SHOP_MEDIA_THUMB_WIDTH', 160),
    ],

    'feeds' => [
        'enabled' => (bool) env('SHOP_FEEDS_ENABLED', true),
        'disk' => env('SHOP_FEEDS_DISK', 'public'),
        'directory' => env('SHOP_FEEDS_DIRECTORY', 'feeds'),
        'regenerate_on_product_save' => (bool) env('SHOP_FEEDS_REGENERATE_ON_SAVE', true),
        'product_condition' => env('SHOP_FEEDS_PRODUCT_CONDITION', 'new'),
        'google' => [
            'slug' => env('SHOP_FEEDS_GOOGLE_SLUG', 'google-merchant.xml'),
            'default_category' => env('SHOP_FEEDS_GOOGLE_CATEGORY', 'Apparel & Accessories > Clothing'),
        ],
        'facebook' => [
            'slug' => env('SHOP_FEEDS_FACEBOOK_SLUG', 'facebook-catalog.csv'),
        ],
    ],

    'mega_menu' => [
        'product_limit' => (int) env('SHOP_MEGA_MENU_PRODUCT_LIMIT', 4),
        'mobile_link_limit' => (int) env('SHOP_MEGA_MENU_MOBILE_LINK_LIMIT', 8),
    ],

    'homepage_banners' => [
        'enabled' => (bool) env('SHOP_HOMEPAGE_BANNERS_ENABLED', true),
    ],

    'banner' => [
        'translatable_fields' => [
            'title',
            'link_url',
        ],
        'required_translation_fields' => [],
    ],

    'checkout' => [
        'shipping_cost' => (float) env('SHOP_SHIPPING_COST', 15),
        'free_shipping_from' => (float) env('SHOP_FREE_SHIPPING_FROM', 500),
    ],

    'auto_translate' => [
        'verify_ssl' => env('SHOP_AUTO_TRANSLATE_VERIFY_SSL'),
    ],

    'product' => [
        'translatable_fields' => [
            'name',
            'slug',
            'subtitle',
            'short_description',
            'description',
            'fit_description',
            'fabric_description',
            'tailoring_description',
            'meta_title',
            'meta_description',
        ],
    ],

    'product_detail_item' => [
        'translatable_fields' => [
            'label',
            'description',
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

    'color' => [
        'translatable_fields' => ['name'],
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

    'hero_banner_item' => [
        'translatable_fields' => [
            'title',
            'subtitle',
            'button_label',
        ],
        // Only image is required for hero cards — texts and CTA stay optional.
        'required_translation_fields' => [],
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

    'size_chart_preset' => [
        'translatable_fields' => ['name', 'description'],
    ],

    'landing_page' => [
        'translatable_fields' => [
            'name',
            'slug',
            'meta_title',
            'meta_description',
        ],
    ],

    'landing_page_block' => [
        'translatable_fields' => [
            'title',
            'subtitle',
            'content',
            'button_label',
            'link_url',
            'caption',
        ],
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
