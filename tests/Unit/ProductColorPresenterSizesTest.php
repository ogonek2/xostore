<?php

namespace Tests\Unit;

use App\Support\Shop\ProductColorPresenter;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductColorPresenterSizesTest extends TestCase
{
    public function test_sizes_for_product_color_includes_variants_with_attribute_color_id(): void
    {
        $variants = collect([
            ['id' => 1, 'size' => 'S', 'size_value' => 's', 'color_id' => 42],
            ['id' => 2, 'size' => 'M', 'size_value' => 'm', 'color_id' => 42],
        ]);

        $sizes = ProductColorPresenter::sizesForColor($variants, 0, true);

        $this->assertCount(2, $sizes);
        $this->assertSame('S', $sizes[0]['label']);
        $this->assertSame('M', $sizes[1]['label']);
    }
}
