<?php

namespace App\Support;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class Slugger
{
    // TR karakterleri için geniş haritalama (Str::slug yeterli ama garantiye alalım)
    public static function make(string $name): string
    {
        $slug = Str::slug($name, '-');
        return $slug ?: 'urun';
    }

    // products tablosunda benzersiz slug üretir (urun, urun-2, urun-3 ...)
    public static function unique(string $base, ?int $ignoreId = null): string
    {
        $slug = self::make($base);
        $original = $slug;
        $i = 2;

        while (self::exists($slug, $ignoreId)) {
            $slug = $original.'-'.$i;
            $i++;
        }
        return $slug;
    }

    protected static function exists(string $slug, ?int $ignoreId = null): bool
    {
        $q = DB::table('products')->where('slug', $slug);
        if ($ignoreId) $q->where('id', '<>', $ignoreId);
        return $q->exists();
    }
}