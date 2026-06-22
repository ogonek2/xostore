<?php

namespace App\Support\Feeds;

final class FacebookCatalogFeedBuilder
{
    /** @var list<string> */
    private const HEADERS = [
        'id',
        'title',
        'description',
        'availability',
        'condition',
        'price',
        'link',
        'image_link',
        'brand',
        'item_group_id',
        'google_product_category',
        'size',
        'color',
        'gtin',
        'mpn',
        'sale_price',
    ];

    /**
     * @param  list<ProductFeedItem>  $items
     */
    public function build(array $items): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, self::HEADERS);

        foreach ($items as $item) {
            fputcsv($handle, [
                $item->id,
                $item->title,
                $item->description,
                $item->availability,
                $item->condition,
                $item->price,
                $item->link,
                $item->imageLink,
                $item->brand,
                $item->itemGroupId,
                $item->googleProductCategory ?? '',
                $item->size ?? '',
                $item->color ?? '',
                $item->gtin ?? '',
                $item->mpn ?? '',
                $item->salePrice ?? '',
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }
}
