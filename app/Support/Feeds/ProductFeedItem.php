<?php

namespace App\Support\Feeds;

final readonly class ProductFeedItem
{
    public function __construct(
        public string $id,
        public string $itemGroupId,
        public string $title,
        public string $description,
        public string $link,
        public string $imageLink,
        public string $availability,
        public string $price,
        public ?string $salePrice,
        public string $brand,
        public string $condition,
        public ?string $googleProductCategory,
        public ?string $gtin,
        public ?string $mpn,
        public ?string $size,
        public ?string $color,
    ) {}
}
