<?php

namespace App\Support;

use InvalidArgumentException;

class BarcodeGenerator
{
    /**
     * Code 128 bar/space width patterns for symbol values 0 to 106.
     *
     * @var array<int, string>
     */
    private const PATTERNS = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];

    private const START_CODE_B = 104;

    private const STOP = 106;

    /**
     * Encode a value into the Code 128B bar/space width sequence.
     */
    public static function encode(string $value): string
    {
        if ($value === '') {
            throw new InvalidArgumentException('Barcode value cannot be empty.');
        }

        $checksum = self::START_CODE_B;
        $codes = [self::START_CODE_B];
        $characters = str_split($value);

        foreach ($characters as $index => $character) {
            $ascii = ord($character);

            if ($ascii < 32 || $ascii > 126) {
                throw new InvalidArgumentException('Code 128B only supports printable ASCII characters.');
            }

            $code = $ascii - 32;
            $codes[] = $code;
            $checksum += $code * ($index + 1);
        }

        $codes[] = $checksum % 103;
        $codes[] = self::STOP;

        $pattern = '';

        foreach ($codes as $code) {
            $pattern .= self::PATTERNS[$code];
        }

        return $pattern;
    }

    /**
     * Render the value as an inline SVG barcode.
     */
    public static function svg(string $value, int $height = 70, float $moduleWidth = 1.6, bool $withText = true): string
    {
        $pattern = self::encode($value);
        $quietZone = $moduleWidth * 10;
        $x = $quietZone;
        $bars = '';
        $isBar = true;

        foreach (str_split($pattern) as $width) {
            $widthPx = (int) $width * $moduleWidth;

            if ($isBar) {
                $bars .= sprintf(
                    '<rect x="%.2f" y="0" width="%.2f" height="%d" fill="#111827"/>',
                    $x,
                    $widthPx,
                    $height,
                );
            }

            $x += $widthPx;
            $isBar = ! $isBar;
        }

        $totalWidth = $x + $quietZone;
        $textHeight = $withText ? 18 : 0;
        $totalHeight = $height + $textHeight;
        $text = $withText
            ? sprintf(
                '<text x="%.2f" y="%d" text-anchor="middle" font-family="ui-monospace, SFMono-Regular, Menlo, monospace" font-size="12" fill="#111827">%s</text>',
                $totalWidth / 2,
                $height + 14,
                htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            )
            : '';

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%.2f" height="%d" viewBox="0 0 %.2f %d" role="img" aria-label="Barcode %s" shape-rendering="crispEdges"><rect width="100%%" height="100%%" fill="#ffffff"/>%s%s</svg>',
            $totalWidth,
            $totalHeight,
            $totalWidth,
            $totalHeight,
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8'),
            $bars,
            $text,
        );
    }
}
