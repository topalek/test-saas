<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         id
 * @property string      name
 * @property string      description
 * @property string      code
 * @property FeatureType type
 * @property int         limit
 * @property int         sort
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class Feature extends Model
{

    protected $fillable = [
        'name',
        'description',
        'code',
        'type',
        'limit',
        'sort',
    ];

    protected $casts = [
        'type' => FeatureType::class
    ];
}
