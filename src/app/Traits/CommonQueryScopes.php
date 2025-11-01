<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait CommonQueryScopes
{
    /**
     * Scope a query to filter records by a specific date on the 'date' column.
     */
    public function scopeFilterByDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope a query to search records by a keyword in the 'title' column.
     */
    public function scopeSearchByTitle(Builder $query, string $keyword): Builder
    {
        return $query->where('title', 'LIKE', '%' . $keyword . '%');
    }
}