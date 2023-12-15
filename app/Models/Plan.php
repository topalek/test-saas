<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int         id
 * @property string      name
 * @property string      description
 * @property float|null  price
 * @property int         period
 * @property int         sort
 * @property Carbon|null deleted_at
 * @property Carbon|null created_at
 * @property Carbon|null updated_at
 */
class Plan extends Model
{

    protected $fillable = [
        'name',
        'description',
        'price',
        'period',
        'sort',
    ];

    protected $casts = [];
}
