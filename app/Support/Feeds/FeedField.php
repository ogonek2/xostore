<?php

namespace App\Support\Feeds;

/**
 * Feed attribute keys (Google/Facebook schema), not product DB columns.
 */
final class FeedField
{
    public const ID = 'id';

    public const TITLE = 'title';

    public const DESCRIPTION = 'description';

    public const LINK = 'link';

    public const IMAGE_LINK = 'image_link';

    public const AVAILABILITY = 'availability';

    public const PRICE = 'price';

    public const SALE_PRICE = 'sale_price';

    public const BRAND = 'brand';

    public const CONDITION = 'condition';

    public const ITEM_GROUP_ID = 'item_group_id';

    public const GOOGLE_PRODUCT_CATEGORY = 'google_product_category';

    public const GTIN = 'gtin';

    public const MPN = 'mpn';

    public const SIZE = 'size';

    public const COLOR = 'color';

    /** Fields that must always be present in a valid Merchant/Catalog feed. */
    public const REQUIRED = [
        self::ID,
        self::TITLE,
        self::LINK,
        self::IMAGE_LINK,
        self::AVAILABILITY,
        self::PRICE,
    ];

    /** Optional fields that can be toggled in admin. */
    public const OPTIONAL = [
        self::DESCRIPTION,
        self::BRAND,
        self::CONDITION,
        self::SALE_PRICE,
        self::ITEM_GROUP_ID,
        self::GOOGLE_PRODUCT_CATEGORY,
        self::SIZE,
        self::COLOR,
        self::GTIN,
        self::MPN,
    ];

    /**
     * @return array<string, string> key => admin label
     */
    public static function optionalLabels(): array
    {
        return [
            self::DESCRIPTION => 'description',
            self::BRAND => 'brand',
            self::CONDITION => 'condition',
            self::SALE_PRICE => 'sale_price',
            self::ITEM_GROUP_ID => 'item_group_id',
            self::GOOGLE_PRODUCT_CATEGORY => 'google_product_category',
            self::SIZE => 'size',
            self::COLOR => 'color',
            self::GTIN => 'gtin',
            self::MPN => 'mpn',
        ];
    }

    /**
     * @return list<string>
     */
    public static function defaultEnabled(): array
    {
        return array_values(array_merge(self::REQUIRED, self::OPTIONAL));
    }
}
