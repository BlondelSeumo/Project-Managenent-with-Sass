<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'currant_workspace',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getGuard(){
        return $this->guard;
    }

    public function workspace()
    {
        return $this->belongsToMany('App\Workspace', 'user_workspaces', 'user_id', 'workspace_id')->withPivot('permission');
    }

    public function currantWorkspace()
    {
        return $this->hasOne('App\Workspace', 'id', 'currant_workspace');
    }

    public function countProject($workspace_id = '')
    {
        $count = UserProject::join('projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $this->id);
        if(!empty($workspace_id))
        {
            $count->where('projects.workspace', '=', $workspace_id);
        }

        return $count->count();
    }

    public function countWorkspaceProject($workspace_id)
    {
        return Project::join('workspaces', 'workspaces.id', '=', 'projects.workspace')->where('workspaces.id', '=', $workspace_id)->count();
    }

    public function countWorkspace()
    {
        return Workspace::where('created_by', '=', $this->id)->count();
    }

    public function countUsers($workspace_id)
    {
        $count = UserWorkspace::join('workspaces', 'workspaces.id', '=', 'user_workspaces.workspace_id');
        if(!empty($workspace_id))
        {
            $count->where('workspaces.id', '=', $workspace_id);
        }
        else
        {
            $count->whereIn(
                'workspaces.id', function ($query){
                $query->select('workspace_id')->from('user_workspaces')->where('permission', '=', 'Owner')->where('user_id', '=', $this->id);
            }
            );
        }

        return $count->count();
    }

    public function countClients($workspace_id)
    {
        $count = ClientWorkspace::join('workspaces', 'workspaces.id', '=', 'client_workspaces.workspace_id');
        if(!empty($workspace_id))
        {
            $count->where('workspaces.id', '=', $workspace_id);
        }
        else
        {
            $count->whereIn(
                'workspaces.id', function ($query){
                $query->select('workspace_id')->from('user_workspaces')->where('permission', '=', 'Owner')->where('user_id', '=', $this->id);
            }
            );
        }

        return $count->count();
    }

    public function countTask($workspace_id)
    {
        return Task::join('projects', 'tasks.project_id', '=', 'projects.id')->where('projects.workspace', '=', $workspace_id)->where('tasks.assign_to', '=', $this->id)->count();
    }

    public function getPlan()
    {
        return $this->hasOne('App\Plan', 'id', 'plan');
    }

    public function assignPlan($planID)
    {
        $plan = Plan::find($planID);
        if($plan)
        {

            $workspaces      = Workspace::where('created_by', '=', $this->id)->get();
            $workspacesCount = 0;
            foreach($workspaces as $workspace)
            {
                $workspacesCount++;
                if($workspacesCount <= $plan->max_workspaces)
                {

                    $workspace->is_active = 1;
                    $workspace->save();

                    // project
                    $projectCount = 0;
                    foreach($workspace->projects as $project)
                    {
                        $projectCount++;
                        if($projectCount <= $plan->max_projects)
                        {
                            $project->is_active = 1;
                            $project->save();
                        }
                        else
                        {
                            $project->is_active = 0;
                            $project->save();
                        }
                    }

                    // Users
                    $userWorkspaces      = UserWorkspace::where('workspace_id', '=', $workspace->id)->get();
                    $userWorkspacesCount = 0;
                    foreach($userWorkspaces as $userWorkspace)
                    {
                        $userWorkspacesCount++;
                        if($userWorkspacesCount <= $plan->max_users)
                        {
                            $userWorkspace->is_active = 1;
                            $userWorkspace->save();

                            UserProject::join('projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('user_projects.user_id', '=', $userWorkspace->user_id)->update(['user_projects.is_active' => 1]);

                        }
                        else
                        {
                            $userWorkspace->is_active = 0;
                            $userWorkspace->save();

                            UserProject::join('projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('user_projects.user_id', '=', $userWorkspace->user_id)->update(['user_projects.is_active' => 0]);
                        }
                    }

                    // Clients
                    $clientWorkspaces      = ClientWorkspace::where('workspace_id', '=', $workspace->id)->get();
                    $clientWorkspacesCount = 0;
                    foreach($clientWorkspaces as $clientWorkspace)
                    {
                        $clientWorkspacesCount++;
                        if($clientWorkspacesCount <= $plan->max_clients)
                        {
                            $clientWorkspace->is_active = 1;
                            $clientWorkspace->save();

                            ClientProject::join('projects', 'projects.id', '=', 'client_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('client_projects.client_id', '=', $clientWorkspace->client_id)->update(['client_projects.is_active' => 1]);

                        }
                        else
                        {
                            $clientWorkspace->is_active = 0;
                            $clientWorkspace->save();

                            ClientProject::join('projects', 'projects.id', '=', 'client_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('client_projects.client_id', '=', $clientWorkspace->client_id)->update(['client_projects.is_active' => 0]);
                        }
                    }
                }
                else
                {
                    $workspace->is_active = 0;
                    $workspace->save();

                    // project
                    foreach($workspace->projects as $project)
                    {
                        $project->is_active = 0;
                        $project->save();
                    }
                    // Users
                    $userWorkspaces = UserWorkspace::where('workspace_id', '=', $workspace->id)->get();
                    foreach($userWorkspaces as $userWorkspace)
                    {
                        $userWorkspace->is_active = 0;
                        $userWorkspace->save();

                        UserProject::join('projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('user_projects.user_id', '=', $userWorkspace->user_id)->update(['user_projects.is_active' => 0]);
                    }

                    // Clients
                    $clientWorkspaces = ClientWorkspace::where('workspace_id', '=', $workspace->id)->get();
                    foreach($clientWorkspaces as $clientWorkspace)
                    {
                        $clientWorkspace->is_active = 0;
                        $clientWorkspace->save();

                        ClientProject::join('projects', 'projects.id', '=', 'client_projects.project_id')->where('projects.workspace', '=', $workspace->id)->where('client_projects.client_id', '=', $clientWorkspace->client_id)->update(['client_projects.is_active' => 0]);
                    }
                }
            }

            //            dd($plan);


            $this->plan = $plan->id;
            if($plan->duration == 'Month')
            {
                $this->plan_expire_date = Carbon::now()->addMonths(1)->isoFormat('YYYY-MM-DD');
            }
            elseif($plan->duration == 'Year')
            {
                $this->plan_expire_date = Carbon::now()->addYears(1)->isoFormat('YYYY-MM-DD');
            }
            $this->save();


            return ['is_success' => true];
        }
        else
        {
            return [
                'is_success' => false,
                'error' => 'Plan is deleted.',
            ];
        }
    }

    public function unread($workspace_id,$user_id)
    {
        return Message::where('from', '=', $this->id)->where('to','=',$user_id)->where('is_read', '=', 0)->where('workspace_id','=',$workspace_id)->count();
    }

    public function notifications($workspace_id){
        return Notification::where('user_id','=',$this->id)->where('workspace_id','=',$workspace_id)->where('is_read','=',0)->orderBy('id','desc')->get();
    }

    public function getInvoices($workspace_id){
        return Invoice::where('workspace_id','=',$workspace_id)->get();
    }
}
