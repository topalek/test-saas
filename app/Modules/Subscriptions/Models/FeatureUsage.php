<?php

namespace App\Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureUsage extends Model
{
    protected $table = 'feature_usages';
    protected $guarded = [];

    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    public function scopeCode(Builder $query, string $code): void
    {
        $query->where('code', $code);
    }
}
