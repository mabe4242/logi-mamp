<?php

namespace App\Enums;

class RequestStatus
{
    public const PENDING = 0;
    public const APPROVED = 1;

    public static function label(int $status)
    {
        return match ($status) {
            self::PENDING => '',
            self::APPROVED => '',
            default => '不明',
        };
    }
}