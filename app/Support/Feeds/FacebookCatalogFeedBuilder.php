<?php

namespace App\Support\Feeds;

use App\Models\FeedSettings;

final class FacebookCatalogFeedBuilder
{
    /** @var list<string> */
    private const ALL_HEADERS = [
        FeedField::ID,
        FeedField::TITLE,
        FeedField::DESCRIPTION,
        FeedField::AVAILABILITY,
        FeedField::CONDITION,
        FeedField::PRICE,
        FeedField::LINK,
        FeedField::IMAGE_LINK,
        FeedField::BRAND,
        FeedField::ITEM_GROUP_ID,
        FeedField::GOOGLE_PRODUCT_CATEGORY,
        FeedField::SIZE,
        FeedField::COLOR,
        FeedField::GTIN,
        FeedField::MPN,
        FeedField::SALE_PRICE,
    ];

    public function __construct(
        private readonly ?FeedSettings $settings = null,
    ) {}

    /**
     * @param  list<ProductFeedItem>  $items
     */
    public function build(array $items): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            return '';
        }

        $headers = $this->activeHeaders();

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers);

        foreach ($items as $item) {
            $row = [];

            foreach ($headers as $header) {
                $row[] = $this->valueFor($item, $header);
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);

        return $csv;
    }

    /**
     * @return list<string>
     */
    private function activeHeaders(): array
    {
        return array_values(array_filter(
            self::ALL_HEADERS,
            fn (string $field): bool => $this->settings?->isFieldEnabled($field) ?? true,
        ));
    }

    private function valueFor(ProductFeedItem $item, string $header): string
    {
        return match ($header) {
            FeedField::ID => $item->id,
            FeedField::TITLE => $item->title,
            FeedField::DESCRIPTION => $item->description,
            FeedField::AVAILABILITY => $item->availability,
            FeedField::CONDITION => $item->condition,
            FeedField::PRICE => $item->price,
            FeedField::LINK => $item->link,
            FeedField::IMAGE_LINK => $item->imageLink,
            FeedField::BRAND => $item->brand,
            FeedField::ITEM_GROUP_ID => $item->itemGroupId,
            FeedField::GOOGLE_PRODUCT_CATEGORY => $item->googleProductCategory ?? '',
            FeedField::SIZE => $item->size ?? '',
            FeedField::COLOR => $item->color ?? '',
            FeedField::GTIN => $item->gtin ?? '',
            FeedField::MPN => $item->mpn ?? '',
            FeedField::SALE_PRICE => $item->salePrice ?? '',
            default => '',
        };
    }
}
