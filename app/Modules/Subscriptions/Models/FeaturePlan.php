<?php

namespace App\Modules\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int    $id
 * @property float  $value
 * @property int    $feature_id
 * @property int    $plan_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class FeaturePlan extends Pivot
{
    protected $fillable = [
        'value',
    ];

    public function feature()
    {
        return $this->belongsTo(Feature::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
