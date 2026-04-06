<?php

declare(strict_types=1);

namespace Core;

/**
 * String veriler uzerinde sik kullanilan islemler icin hafif static yardimci sinif.
 */
final class Str
{
    /**
     * Metin icinde belirli bir ifadenin gecip gecmedigini kontrol etmek icin kullanilir.
     */
    public static function contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_contains($haystack, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Metnin belirli bir ifadeyle baslayip baslamadigini kontrol etmek icin kullanilir.
     */
    public static function startsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_starts_with($haystack, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Metnin belirli bir ifadeyle bitip bitmedigini kontrol etmek icin kullanilir.
     */
    public static function endsWith(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_ends_with($haystack, (string) $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Metinde verilen ayirac oncesindeki bolumu almak icin kullanilir.
     */
    public static function before(string $subject, string $search): string
    {
        if ($search === '' || ! str_contains($subject, $search)) {
            return $subject;
        }

        return explode($search, $subject, 2)[0];
    }

    /**
     * Metinde verilen ayirac sonrasindaki bolumu almak icin kullanilir.
     */
    public static function after(string $subject, string $search): string
    {
        if ($search === '' || ! str_contains($subject, $search)) {
            return $subject;
        }

        return explode($search, $subject, 2)[1];
    }

    /**
     * Iki isaret arasindaki metni cekmek icin kullanilir.
     */
    public static function between(string $subject, string $from, string $to): string
    {
        return self::before(self::after($subject, $from), $to);
    }

    /**
     * Metni kucuk harfe cevirmek icin kullanilir.
     */
    public static function lower(string $value): string
    {
        return mb_strtolower($value);
    }

    /**
     * Metnin karakter uzunlugunu guvenli sekilde almak icin kullanilir.
     */
    public static function length(string $value): int
    {
        return mb_strlen($value);
    }

    /**
     * Metni buyuk harfe cevirmek icin kullanilir.
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value);
    }

    /**
     * Metni kelime basi buyuk basliga cevirmek icin kullanilir.
     */
    public static function title(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }

    /**
     * Metindeki fazla bosluklari tek bosluga indirgemek icin kullanilir.
     */
    public static function squish(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? $value;

        return trim($value);
    }

    /**
     * Metni belirli uzunlukta sinirlayip sonuna ek ifade koymak icin kullanilir.
     */
    public static function limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit)) . $end;
    }

    /**
     * Metni verilen karakter sinirina gore kelimeyi ortadan bolmeden kisaltmak icin kullanilir.
     */
    public static function shorten(string $value, int $limit = 100, string $end = '...'): string
    {
        $value = trim($value);

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        if ($limit <= 0) {
            return $end;
        }

        $slice = trim(mb_substr($value, 0, $limit + 1));
        $lastSpace = mb_strrpos($slice, ' ');

        if ($lastSpace === false) {
            return rtrim(mb_substr($value, 0, $limit)) . $end;
        }

        return rtrim(mb_substr($slice, 0, $lastSpace)) . $end;
    }

    /**
     * Metni URL veya dosya adina uygun slug bicimine cevirmek icin kullanilir.
     */
    public static function slug(string $value, string $separator = '-'): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = self::lower($value);
        $value = preg_replace('/[^a-z0-9]+/i', $separator, $value) ?? $value;

        return trim($value, $separator);
    }

    /**
     * Metni snake_case bicimine cevirmek icin kullanilir.
     */
    public static function snake(string $value): string
    {
        $value = preg_replace('/(.)(?=[A-Z])/u', '$1_', $value) ?? $value;

        return self::slug(str_replace(['-', ' '], '_', $value), '_');
    }

    /**
     * Metni kebab-case bicimine cevirmek icin kullanilir.
     */
    public static function kebab(string $value): string
    {
        return str_replace('_', '-', self::snake($value));
    }

    /**
     * Metni StudlyCase bicimine cevirmek icin kullanilir.
     */
    public static function studly(string $value): string
    {
        $value = preg_replace('/[_\-\s]+/', ' ', $value) ?? $value;
        $value = self::title(self::lower($value));

        return str_replace(' ', '', $value);
    }

    /**
     * Metni camelCase bicimine cevirmek icin kullanilir.
     */
    public static function camel(string $value): string
    {
        return lcfirst(self::studly($value));
    }

    /**
     * Metin icindeki ifadeleri degistirmek icin kullanilir.
     */
    public static function replace(string|array $search, string|array $replace, string|array $subject): string|array
    {
        return str_replace($search, $replace, $subject);
    }

    /**
     * Anahtar-deger eslesmesiyle toplu string donusumu yapmak icin kullanilir.
     *
     * Bu metod PHP'deki strtr mantigina yakindir ve ozellikle placeholder
     * degisimlerinde daha okunur bir kullanim saglar.
     *
     * @param array<string, string> $replacements
     */
    public static function translate(string $subject, array $replacements): string
    {
        return strtr($subject, $replacements);
    }
}
