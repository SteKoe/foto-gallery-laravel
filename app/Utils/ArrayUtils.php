<?php

namespace App\Utils;

use App\Models\GalleryImageDescriptor;

class ArrayUtils
{
    /**
     * @param GalleryImageDescriptor[] $minuend
     * @param GalleryImageDescriptor[] $subtrahend
     * @return GalleryImageDescriptor[]
     */
    public static function subtract(array $minuend, array $subtrahend): array
    {
        $subtrahendIds = array_column($subtrahend, 'fileid');

        return array_filter($minuend, function ($m) use ($subtrahendIds) {
            return !in_array($m->fileid, $subtrahendIds);
        });
    }

    /**
     * @param callable $callback
     * @param array $array
     * @return array
     */
    public static function flatMap(callable $callback, array $array): array {
        $mapped = array_map($callback, $array);
        return array_merge(...$mapped);
    }
}
