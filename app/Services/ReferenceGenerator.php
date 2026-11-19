<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferenceGenerator
{
    /**
     * Generate a human readable document number, e.g. PO-20261120-0001.
     */
    public static function make(string $prefix, string $table, string $column = 'reference_no'): string
    {
        $stem = $prefix.'-'.now()->format('Ymd').'-';

        $last = DB::table($table)
            ->where($column, 'like', $stem.'%')
            ->orderByDesc($column)
            ->value($column);

        $sequence = $last === null ? 1 : ((int) Str::afterLast((string) $last, '-')) + 1;

        do {
            $reference = $stem.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table($table)->where($column, $reference)->exists());

        return $reference;
    }
}
