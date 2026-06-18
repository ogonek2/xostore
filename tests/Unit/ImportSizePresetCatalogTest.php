<?php

namespace Tests\Unit;

use App\Support\Import\ImportSizePresetCatalog;
use Tests\TestCase;

class ImportSizePresetCatalogTest extends TestCase
{
    public function test_resolves_size_chart_alias_from_documentation(): void
    {
        $this->assertSame('women_dresses_cm', ImportSizePresetCatalog::resolveSizeChartPresetCode('dresses_women'));
    }

    public function test_resolves_size_grid_legacy_alias(): void
    {
        $this->assertSame('footwear_eu', ImportSizePresetCatalog::resolveSizeGridCode('eu_footwear'));
    }
}
