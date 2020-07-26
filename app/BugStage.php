<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BugStage extends Model
{
    protected $fillable = ['name','complete','workspace_id','order'];
}
