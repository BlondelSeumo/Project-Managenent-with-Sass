<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'status',
        'description',
        'start_date',
        'end_date',
        'budget',
        'workspace',
        'created_by',
        'is_active',
    ];

    public function creater()
    {
        return $this->hasOne('App\User', 'id', 'created_by');
    }

    public function workspaceData()
    {
        return $this->hasOne('App\Workspace', 'id', 'workspace');
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'user_projects', 'project_id', 'user_id')->withPivot('is_active');
    }

    public function clients()
    {
        return $this->belongsToMany('App\Client', 'client_projects', 'project_id', 'client_id')->withPivot('is_active');
    }

    public function countTask()
    {
        return Task::where('project_id', '=', $this->id)->count();
    }

    public function tasks()
    {
        return Task::where('project_id', '=', $this->id)->get();
    }

    public function user_tasks($user_id){
        return Task::where('project_id','=',$this->id)->where('assign_to','=',$user_id)->get();
    }
    public function user_done_tasks($user_id){
        return Task::join('stages','stages.id','=','tasks.status')->where('project_id','=',$this->id)->where('assign_to','=',$user_id)->where('stages.complete','=','1')->get();
    }

    public function timesheet()
    {
        return Timesheet::where('project_id', '=', $this->id)->get();
    }


    public function countTaskComments()
    {
        return Task::join('comments', 'comments.task_id', '=', 'tasks.id')->where('project_id', '=', $this->id)->count();
    }

    public function getProgress()
    {

        $total     = Task::where('project_id', '=', $this->id)->count();
        $totalDone = Task::where('project_id', '=', $this->id)->where('status', '=', 'done')->count();
        if($totalDone == 0)
        {
            return 0;
        }

        return round(($totalDone * 100) / $total);
    }

    public function milestones()
    {
        return $this->hasMany('App\Milestone', 'project_id', 'id');
    }

    public function files()
    {
        return $this->hasMany('App\ProjectFile', 'project_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany('App\ActivityLog', 'project_id', 'id')->orderBy('id', 'desc');
    }
}
