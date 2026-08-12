<?php

namespace App\Enums;

enum VoteType: string
{
    case Recommend = 'recommend';
    case NotRecommend = 'not_recommend';

    public function label(): string
    {
        return match ($this) {
            self::Recommend => __('Recommend'),
            self::NotRecommend => __('Do not recommend'),
        };
    }
}
