<?php

namespace App\Support\Shop;

final class HeroBannerFrame
{
    /**
     * @return array<string, string>
     */
    public static function heightOptions(): array
    {
        return [
            'auto' => 'Авто (по картинке)',
            'sm' => 'Компактная',
            'md' => 'Средняя',
            'lg' => 'Высокая',
            'xl' => 'Очень высокая',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function widthOptions(): array
    {
        return [
            'full' => 'На всю ширину',
            'wide' => 'Широкая (до 90rem)',
            'medium' => 'Средняя (до 72rem)',
            'narrow' => 'Узкая (до 56rem)',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function fitOptions(): array
    {
        return [
            'contain' => 'Вписать целиком (без обрезки)',
            'cover' => 'Заполнить блок (может обрезать)',
        ];
    }

    public static function heightClass(?string $preset, bool $forSlider = false): string
    {
        $preset = $preset ?: 'auto';

        if ($preset === 'auto' && $forSlider) {
            $preset = 'md';
        }

        return match ($preset) {
            'sm' => 'h-[min(40vw,12rem)] sm:h-[min(36vw,15rem)] lg:h-[18rem]',
            'md' => 'h-[min(48vw,15rem)] sm:h-[min(42vw,18rem)] lg:h-[22rem]',
            'lg' => 'h-[min(58vw,18rem)] sm:h-[min(48vw,22rem)] lg:h-[28rem]',
            'xl' => 'h-[min(68vw,22rem)] sm:h-[min(54vw,26rem)] lg:h-[32rem]',
            default => 'h-auto',
        };
    }

    public static function widthClass(?string $preset): string
    {
        return match ($preset ?: 'full') {
            'wide' => 'mx-auto w-full max-w-[90rem]',
            'medium' => 'mx-auto w-full max-w-[72rem]',
            'narrow' => 'mx-auto w-full max-w-[56rem]',
            default => 'w-full',
        };
    }

    public static function isAutoHeight(?string $preset): bool
    {
        return ($preset ?: 'auto') === 'auto';
    }

    public static function normalizeFit(?string $fit): string
    {
        return $fit === 'cover' ? 'cover' : 'contain';
    }
}
