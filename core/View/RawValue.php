<?php

declare(strict_types=1);

namespace Core\View;

/**
 * HTML kacislama islemini atlamasi gereken view icerigini isaretler.
 */
final class RawValue
{
    /**
     * @param string $value Ham HTML metni.
     */
    public function __construct(
        private readonly string $value
    ) {
    }

    /**
     * Ham HTML degerini dondurur.
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }
}
