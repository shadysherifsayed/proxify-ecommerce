<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Schema;

class DatabaseHelper
{
    /**
     * Check if the current database supports full-text search.
     *
     * @return bool
     */
    public static function supportsFullTextSearch(): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        return in_array($driver, ['mysql', 'pgsql']);
    }
}