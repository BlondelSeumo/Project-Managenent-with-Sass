<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'name',
        'code',
        'discount',
        'limit',
        'description',
    ];


    public function used_coupon()
    {
        return $this->hasMany('App\UserCoupon', 'coupon', 'id')->count();
    }
}
