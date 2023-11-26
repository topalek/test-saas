<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * @property int         id
 * @property string      name
 * @property string      title
 * @property int         consumable
 * @property bool        quota
 * @property bool        postpaid
 * @property int         periodicity
 * @property string      periodicity_type
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class Feature extends \LucasDotVin\Soulbscription\Models\Feature
{

    protected $fillable = [
        'consumable',
        'name',
        'title',
        'periodicity_type',
        'periodicity',
        'quota',
        'postpaid',
    ];

    protected $casts = [];
}
