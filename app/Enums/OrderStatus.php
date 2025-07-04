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
            self::PENDING => $status === 'processing',
            self::PROCESSING => $status === 'completed' || $status === 'cancelled',
            self::COMPLETED => false,
            self::CANCELLED => false,
        };
    }
}
