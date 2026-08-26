<?php

namespace App\Support\Feeds;

use App\Models\FeedSettings;

final class GoogleMerchantFeedBuilder
{
    public function __construct(
        private readonly ?FeedSettings $settings = null,
    ) {}

    /**
     * @param  list<ProductFeedItem>  $items
     */
    public function build(array $items, string $shopName, string $shopUrl): string
    {
        $xml = new \XMLWriter;
        $xml->openMemory();
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $xml->startElement('channel');
        $xml->writeElement('title', $shopName);
        $xml->writeElement('link', $shopUrl);
        $xml->writeElement('description', $shopName.' product feed');

        foreach ($items as $item) {
            $xml->startElement('item');
            $this->writeIfEnabled($xml, FeedField::ID, 'g:id', $item->id);
            $this->writeIfEnabled($xml, FeedField::TITLE, 'g:title', $item->title);
            $this->writeOptionalIfEnabled($xml, FeedField::DESCRIPTION, 'g:description', $item->description);
            $this->writeIfEnabled($xml, FeedField::LINK, 'g:link', $item->link);
            $this->writeIfEnabled($xml, FeedField::IMAGE_LINK, 'g:image_link', $item->imageLink);
            $this->writeIfEnabled($xml, FeedField::AVAILABILITY, 'g:availability', $item->availability);
            $this->writeIfEnabled($xml, FeedField::PRICE, 'g:price', $item->price);
            $this->writeOptionalIfEnabled($xml, FeedField::SALE_PRICE, 'g:sale_price', $item->salePrice);
            $this->writeOptionalIfEnabled($xml, FeedField::BRAND, 'g:brand', $item->brand);
            $this->writeOptionalIfEnabled($xml, FeedField::CONDITION, 'g:condition', $item->condition);
            $this->writeOptionalIfEnabled($xml, FeedField::ITEM_GROUP_ID, 'g:item_group_id', $item->itemGroupId);
            $this->writeOptionalIfEnabled($xml, FeedField::GOOGLE_PRODUCT_CATEGORY, 'g:google_product_category', $item->googleProductCategory);
            $this->writeOptionalIfEnabled($xml, FeedField::GTIN, 'g:gtin', $item->gtin);
            $this->writeOptionalIfEnabled($xml, FeedField::MPN, 'g:mpn', $item->mpn);
            $this->writeOptionalIfEnabled($xml, FeedField::SIZE, 'g:size', $item->size);
            $this->writeOptionalIfEnabled($xml, FeedField::COLOR, 'g:color', $item->color);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function enabled(string $field): bool
    {
        return $this->settings?->isFieldEnabled($field) ?? true;
    }

    private function writeIfEnabled(\XMLWriter $xml, string $field, string $name, string $value): void
    {
        if (! $this->enabled($field)) {
            return;
        }

        $this->writeElement($xml, $name, $value);
    }

    private function writeOptionalIfEnabled(\XMLWriter $xml, string $field, string $name, ?string $value): void
    {
        if (! $this->enabled($field)) {
            return;
        }

        if ($value === null || trim($value) === '') {
            return;
        }

        $this->writeElement($xml, $name, $value);
    }

    private function writeElement(\XMLWriter $xml, string $name, string $value): void
    {
        $xml->startElement($name);
        $xml->writeCData($value);
        $xml->endElement();
    }
}
