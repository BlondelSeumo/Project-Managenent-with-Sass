<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $fillable = [
        'user',
        'coupon',
    ];

    public function userDetail()
    {
        return $this->hasOne('App\User', 'id', 'user');
    }

    public function coupon_detail()
    {
        return $this->hasOne('App\Coupon', 'id', 'coupon');
    }


}
