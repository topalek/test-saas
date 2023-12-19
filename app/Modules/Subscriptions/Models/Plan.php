<?php

namespace App\Modules\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use stdClass;

/**
 * @property int      $id
 * @property string   $name
 * @property string   $description
 * @property float    $price
 * @property string $currency_id
 * @property int    $sort
 * @property int    $period
 * @property stdClass $metadata
 * @property Carbon   $created_at
 * @property Carbon   $updated_at
 */

class Plan extends Model
{
    protected $table   = 'plans';
    protected $guarded = [];
    protected $casts   = [
        'metadata' => 'object',
        'price'    => 'float'
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class)
            ->using(FeaturePlan::class)
            ->withPivot(['value']);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

}
