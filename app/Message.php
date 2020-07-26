<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'from',
        'to',
        'message',
        'is_read',
    ];
    public function from_data(){
        return $this->hasOne('App\User','id','from');
    }
}
