<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    /**
     * Check if the order can transition to a given status.
     */
    public function canTransitionTo(string $status): bool
    {
        return match ($this) {
            self::PENDING => $status === self::PROCESSING->value || $status === self::CANCELLED->value,
            self::PROCESSING => $status === self::COMPLETED->value || $status === self::CANCELLED->value,
            self::COMPLETED => false,
            self::CANCELLED => false,
        };
    }
}
