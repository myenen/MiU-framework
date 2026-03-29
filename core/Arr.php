<?php

declare(strict_types=1);

namespace Core;

/**
 * Dizi verileri uzerinde sik kullanilan islemler icin hafif static yardimci sinif.
 */
final class Arr
{
    /**
     * Birden fazla diziyi soldan saga birlestirmek icin kullanilir.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function merge(array ...$arrays): array
    {
        return array_merge(...$arrays);
    }

    /**
     * Birden fazla diziyi recursive olarak birlestirmek icin kullanilir.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function mergeRecursive(array ...$arrays): array
    {
        return array_merge_recursive(...$arrays);
    }

    /**
     * Sonraki dizilerin oncekileri anahtar bazli ezmesini istediginizde kullanilir.
     *
     * @param array<int|string, mixed> ...$arrays
     * @return array<int|string, mixed>
     */
    public static function replace(array ...$arrays): array
    {
        return array_replace(...$arrays);
    }

    /**
     * Noktali anahtar destegiyle dizi icine deger yazmak icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @param string|int|null $key
     * @param mixed $value
     * @return array<int|string, mixed>
     */
    public static function set(array $array, string|int|null $key, mixed $value): array
    {
        if ($key === null) {
            $array[] = $value;

            return $array;
        }

        if (is_int($key) || ! str_contains((string) $key, '.')) {
            $array[$key] = $value;

            return $array;
        }

        $segments = explode('.', (string) $key);
        $current = &$array;

        foreach ($segments as $segment) {
            if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                $current[$segment] = [];
            }

            $current = &$current[$segment];
        }

        $current = $value;

        return $array;
    }

    /**
     * Noktali anahtar destegiyle dizi icinden deger silmek icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @param string|int|array<int, string|int> $keys
     * @return array<int|string, mixed>
     */
    public static function forget(array $array, string|int|array $keys): array
    {
        foreach (self::wrap($keys) as $key) {
            if (is_int($key) || ! str_contains((string) $key, '.')) {
                unset($array[$key]);
                continue;
            }

            $segments = explode('.', (string) $key);
            $last = array_pop($segments);
            $current = &$array;

            foreach ($segments as $segment) {
                if (! isset($current[$segment]) || ! is_array($current[$segment])) {
                    continue 2;
                }

                $current = &$current[$segment];
            }

            if ($last !== null) {
                unset($current[$last]);
            }
        }

        return $array;
    }

    /**
     * Diziden bir degeri alip ayni anda kaynaktan silmek istediginizde kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @param string|int $key
     * @param mixed $default
     * @return array{0:mixed,1:array<int|string,mixed>}
     */
    public static function pull(array $array, string|int $key, mixed $default = null): array
    {
        $value = self::get($array, $key, $default);
        $array = self::forget($array, $key);

        return [$value, $array];
    }

    /**
     * Ic ice diziler dahil bir degerin herhangi bir yerde gecip gecmedigini kontrol etmek icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @param mixed $needle
     * @param bool $strict
     * @return bool
     */
    public static function contains(array $array, mixed $needle, bool $strict = true): bool
    {
        foreach ($array as $value) {
            if (is_array($value) && self::contains($value, $needle, $strict)) {
                return true;
            }

            if ($strict ? $value === $needle : $value == $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verilen noktalı anahtarın iç içe yapıda bulunup bulunmadığını kontrol etmek için kullanılır.
     *
     * @param array<int|string, mixed> $array
     * @param string|int $key
     * @return bool
     */
    public static function containsKey(array $array, string|int $key): bool
    {
        return self::has($array, $key);
    }

    /**
     * İç içe diziler dahil verilen değerin geçtiği ilk anahtar yolunu bulmak için kullanılır.
     *
     * @param array<int|string, mixed> $array
     * @param mixed $needle
     * @param bool $strict
     * @param string $prefix
     * @return string|int|null
     */
    public static function search(array $array, mixed $needle, bool $strict = true, string $prefix = ''): string|int|null
    {
        foreach ($array as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $found = self::search($value, $needle, $strict, $path);

                if ($found !== null) {
                    return $found;
                }
            }

            if ($strict ? $value === $needle : $value == $needle) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Liste içinde verilen noktalı alan ve değere uyan ilk kaydı bulmak için kullanılır.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @param mixed $value
     * @return array<string, mixed>|object|null
     */
    public static function findWhere(array $items, string $key, mixed $value): array|object|null
    {
        foreach ($items as $item) {
            $current = is_array($item) ? self::get($item, $key) : ($item->{$key} ?? null);

            if ($current === $value) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Tekil bir degeri her zaman dizi olarak ele almak istediginizde kullanilir.
     *
     * @param mixed $value
     * @return array<int|string, mixed>
     */
    public static function wrap(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Noktali anahtar destegiyle diziden guvenli sekilde veri okumak icin kullanilir.
     *
     * @param array<mixed> $array
     * @param string|int $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $array, string|int $key, mixed $default = null): mixed
    {
        if (is_int($key) || ! str_contains((string) $key, '.')) {
            return $array[$key] ?? $default;
        }

        $segments = explode('.', (string) $key);
        $value = $array;

        foreach ($segments as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Verilen anahtarin dizide olup olmadigini kontrol etmek icin kullanilir.
     *
     * @param array<mixed> $array
     * @param string|int $key
     * @return bool
     */
    public static function has(array $array, string|int $key): bool
    {
        $marker = new \stdClass();

        return self::get($array, $key, $marker) !== $marker;
    }

    /**
     * Diziden sadece belirli anahtarlari secip alt kume olusturmak icin kullanilir.
     *
     * @param array<mixed> $array
     * @param array<int, string|int> $keys
     * @return array<mixed>
     */
    public static function only(array $array, array $keys): array
    {
        $result = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                $result[$key] = $array[$key];
            }
        }

        return $result;
    }

    /**
     * Diziden istenmeyen anahtarlari cikarip kalan veriyi almak icin kullanilir.
     *
     * @param array<mixed> $array
     * @param array<int, string|int> $keys
     * @return array<mixed>
     */
    public static function except(array $array, array $keys): array
    {
        foreach ($keys as $key) {
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Dizinin ilk elemanina hizli erismek istediginizde kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @return mixed
     */
    public static function first(array $array, mixed $default = null): mixed
    {
        if ($array === []) {
            return $default;
        }

        return reset($array);
    }

    /**
     * Dizinin son elemanina hizli erismek istediginizde kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @return mixed
     */
    public static function last(array $array, mixed $default = null): mixed
    {
        if ($array === []) {
            return $default;
        }

        return end($array);
    }

    /**
     * Nesne ya da dizi listelerinden tek bir alanin degerlerini cekmek icin kullanilir.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @return array<int, mixed>
     */
    public static function pluck(array $items, string $key): array
    {
        $result = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                $result[] = self::get($item, $key);
                continue;
            }

            if (is_object($item)) {
                $result[] = $item->{$key} ?? null;
            }
        }

        return $result;
    }

    /**
     * Dizi elemanlarini yeni degerlere donusturmek icin kullanilir.
     *
     * @param array<int|string, mixed> $items
     * @param callable $callback
     * @return array<int|string, mixed>
     */
    public static function map(array $items, callable $callback): array
    {
        $result = [];

        foreach ($items as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return $result;
    }

    /**
     * Verilen kosula uyan elemanlari secmek icin kullanilir.
     *
     * @param array<int|string, mixed> $items
     * @param callable|null $callback
     * @return array<int|string, mixed>
     */
    public static function filter(array $items, ?callable $callback = null): array
    {
        if ($callback === null) {
            return array_filter($items);
        }

        $result = [];

        foreach ($items as $key => $value) {
            if ($callback($value, $key) === true) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Dizi elemanlarini tek bir sonuc degerinde toplamak icin kullanilir.
     *
     * @param array<int|string, mixed> $items
     * @param callable $callback
     * @param mixed $initial
     * @return mixed
     */
    public static function reduce(array $items, callable $callback, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($items as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Bir listeyi belirli bir alan degerine gore anahtarlamak icin kullanilir.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @return array<int|string, array<string, mixed>|object>
     */
    public static function keyBy(array $items, string $key): array
    {
        $result = [];

        foreach ($items as $item) {
            $value = is_array($item) ? self::get($item, $key) : ($item->{$key} ?? null);

            if ($value === null || $value === '') {
                continue;
            }

            $result[(string) $value] = $item;
        }

        return $result;
    }

    /**
     * Elemanlari belirli bir alanin degerine gore gruplamak icin kullanilir.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @return array<int|string, array<int, array<string, mixed>|object>>
     */
    public static function groupBy(array $items, string $key): array
    {
        $result = [];

        foreach ($items as $item) {
            $value = is_array($item) ? self::get($item, $key) : ($item->{$key} ?? null);
            $groupKey = is_scalar($value) || $value === null ? (string) $value : 'group';
            $result[$groupKey] ??= [];
            $result[$groupKey][] = $item;
        }

        return $result;
    }

    /**
     * Ic ice dizileri daha duz bir yapiya indirmek icin kullanilir.
     *
     * @param array<int|string, mixed> $items
     * @param int $depth
     * @return array<int, mixed>
     */
    public static function flatten(array $items, int $depth = PHP_INT_MAX): array
    {
        $result = [];

        foreach ($items as $item) {
            if (is_array($item) && $depth > 0) {
                array_push($result, ...self::flatten($item, $depth - 1));
                continue;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Liste icindeki kayitlari belirli bir alan ve degerle filtrelemek icin kullanilir.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @param mixed $value
     * @return array<int, array<string, mixed>|object>
     */
    public static function where(array $items, string $key, mixed $value): array
    {
        return array_values(self::filter($items, static function (mixed $item) use ($key, $value): bool {
            $current = is_array($item) ? self::get($item, $key) : ($item->{$key} ?? null);

            return $current === $value;
        }));
    }

    /**
     * Callback sonucuna gore esnek siralama yapmak icin kullanilir.
     *
     * @param array<int|string, mixed> $items
     * @param callable $callback
     * @return array<int|string, mixed>
     */
    public static function sortBy(array $items, callable $callback, string $direction = 'asc'): array
    {
        uasort($items, static function (mixed $left, mixed $right) use ($callback, $direction): int {
            $comparison = $callback($left) <=> $callback($right);

            return strtolower($direction) === 'desc' ? -$comparison : $comparison;
        });

        return $items;
    }

    /**
     * Kayit listesini belirli bir alanin degerine gore siralamak icin kullanilir.
     *
     * @param array<int, array<string, mixed>|object> $items
     * @param string $key
     * @param string $direction
     * @return array<int, array<string, mixed>|object>
     */
    public static function sortByKey(array $items, string $key, string $direction = 'asc'): array
    {
        usort($items, static function (mixed $left, mixed $right) use ($key, $direction): int {
            $leftValue = is_array($left) ? self::get($left, $key) : ($left->{$key} ?? null);
            $rightValue = is_array($right) ? self::get($right, $key) : ($right->{$key} ?? null);

            $comparison = ($leftValue <=> $rightValue);

            return strtolower($direction) === 'desc' ? -$comparison : $comparison;
        });

        return $items;
    }

    /**
     * Diziyi anahtarlarina gore artan ya da azalan bicimde siralamak icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @return array<int|string, mixed>
     */
    public static function sortKeys(array $array, string $direction = 'asc'): array
    {
        if (strtolower($direction) === 'desc') {
            krsort($array);

            return $array;
        }

        ksort($array);

        return $array;
    }

    /**
     * Anahtarlari sifirdan baslayacak sekilde deger listesini yeniden indexlemek icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @return array<int, mixed>
     */
    public static function values(array $array): array
    {
        return array_values($array);
    }

    /**
     * Dizinin associative olup olmadigini anlamak icin kullanilir.
     *
     * @param array<int|string, mixed> $array
     * @return bool
     */
    public static function isAssoc(array $array): bool
    {
        return array_keys($array) !== range(0, count($array) - 1);
    }
}
