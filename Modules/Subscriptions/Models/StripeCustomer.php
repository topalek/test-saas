<?php

namespace App\Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;

class StripeCustomer extends Model
{
    protected $table   = 'stripe_customers';
    protected $guarded = [];
    protected $dates   = [
        //
    ];

    public function model()
    {
        return $this->morphTo();
    }
}
