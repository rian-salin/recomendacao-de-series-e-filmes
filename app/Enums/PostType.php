<?php

namespace App\Enums;

enum PostType: string
{
    case Movie = 'movie';
    case Series = 'series';

    public function label(): string
    {
        return match ($this) {
            self::Movie => __('Movie'),
            self::Series => __('Series'),
        };
    }
}
