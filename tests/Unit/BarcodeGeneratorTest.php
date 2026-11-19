<?php

namespace Tests\Unit;

use App\Support\BarcodeGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BarcodeGeneratorTest extends TestCase
{
    public function test_encode_builds_the_code_128b_symbol_with_checksum(): void
    {
        $this->assertSame(
            '211214'.'111323'.'131123'.'2331112',
            BarcodeGenerator::encode('A'),
        );
    }

    public function test_encode_rejects_empty_and_non_ascii_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BarcodeGenerator::encode('');
    }

    public function test_encode_rejects_characters_outside_printable_ascii(): void
    {
        $this->expectException(InvalidArgumentException::class);

        BarcodeGenerator::encode("A\u{00E9}");
    }

    public function test_svg_renders_bars_and_an_accessible_label(): void
    {
        $svg = BarcodeGenerator::svg('SKU-123', 50, 1.4);

        $this->assertStringStartsWith('<svg xmlns="http://www.w3.org/2000/svg"', $svg);
        $this->assertStringContainsString('aria-label="Barcode SKU-123"', $svg);
        $this->assertStringContainsString('<rect', $svg);
        $this->assertStringContainsString('>SKU-123</text>', $svg);
    }

    public function test_svg_can_omit_the_human_readable_text(): void
    {
        $svg = BarcodeGenerator::svg('BIN-A-01', 40, 1.2, false);

        $this->assertStringNotContainsString('<text', $svg);
    }
}
