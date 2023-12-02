<?php

namespace App\Modules\Subscriptions\Models;

use App\Modules\Subscriptions\Enums\FeatureType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

/**
 * @property int         $id
 * @property int         $plan_id
 * @property string      $name
 * @property string      $code
 * @property string      $description
 * @property FeatureType $type
 * @property int         $limit
 * @property stdClass    $metadata
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Feature extends Model
{
    protected $table   = 'features';
    protected $guarded = [];
    protected $casts   = [
        'metadata' => 'object',
        'type'     => FeatureType::class
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function scopeCode(Builder $query, string $code): void
    {
        $query->where('code', $code);
    }

    public function scopeLimited(Builder $query): void
    {
        $query->where('type', FeatureType::limit);
    }

    public function scopeFeature(Builder $query): void
    {
        $query->where('type', FeatureType::feature);
    }

    public function isUnlimited(): bool
    {
        return ($this->type == FeatureType::limit && $this->limit < 0);
    }
}
