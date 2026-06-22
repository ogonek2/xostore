<?php

namespace App\Support\Feeds;

final class GoogleMerchantFeedBuilder
{
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
            $this->writeElement($xml, 'g:id', $item->id);
            $this->writeElement($xml, 'g:title', $item->title);
            $this->writeElement($xml, 'g:description', $item->description);
            $this->writeElement($xml, 'g:link', $item->link);
            $this->writeElement($xml, 'g:image_link', $item->imageLink);
            $this->writeElement($xml, 'g:availability', $item->availability);
            $this->writeElement($xml, 'g:price', $item->price);
            $this->writeOptional($xml, 'g:sale_price', $item->salePrice);
            $this->writeElement($xml, 'g:brand', $item->brand);
            $this->writeElement($xml, 'g:condition', $item->condition);
            $this->writeElement($xml, 'g:item_group_id', $item->itemGroupId);
            $this->writeOptional($xml, 'g:google_product_category', $item->googleProductCategory);
            $this->writeOptional($xml, 'g:gtin', $item->gtin);
            $this->writeOptional($xml, 'g:mpn', $item->mpn);
            $this->writeOptional($xml, 'g:size', $item->size);
            $this->writeOptional($xml, 'g:color', $item->color);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }

    private function writeElement(\XMLWriter $xml, string $name, string $value): void
    {
        $xml->startElement($name);
        $xml->writeCData($value);
        $xml->endElement();
    }

    private function writeOptional(\XMLWriter $xml, string $name, ?string $value): void
    {
        if ($value === null || trim($value) === '') {
            return;
        }

        $this->writeElement($xml, $name, $value);
    }
}
