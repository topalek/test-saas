<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * @property int         id
 * @property int         grace_days
 * @property string      name
 * @property float|null  price
 * @property int         periodicity
 * @property string      periodicity_type
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class Plan extends \LucasDotVin\Soulbscription\Models\Plan
{

    protected $fillable = [
        'grace_days',
        'name',
        'price',
        'periodicity_type',
        'periodicity',
    ];

    protected $casts = [];
}
