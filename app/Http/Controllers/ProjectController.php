<?php

namespace App\Http\Controllers;

use App\ActivityLog;
use App\BugComment;
use App\BugFile;
use App\BugReport;
use App\BugStage;
use App\Client;
use App\ClientProject;
use App\Comment;
use App\Mail\SendInvication;
use App\Mail\SendLoginDetail;
use App\Mail\SendWorkspaceInvication;
use App\Mail\ShareProjectToClient;
use App\Milestone;
use App\Plan;
use App\Project;
use App\ProjectFile;
use App\Stage;
use App\SubTask;
use App\Task;
use App\TaskFile;
use App\Timesheet;
use App\User;
use App\UserProject;
use App\UserWorkspace;
use App\Utility;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client')
        {
            $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        else
        {
            $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }

        return view('projects.index', compact('currantWorkspace', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store($slug, Request $request)
    {
        $objUser          = \Auth::user();
        $plan             = Plan::find($objUser->plan);
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($plan)
        {
            $totalWS = $objUser->countWorkspaceProject($currantWorkspace->id);
            if($totalWS < $plan->max_projects || $plan->max_projects == -1)
            {

                $request->validate(
                    [
                        'name' => 'required',
                    ]
                );

                $post = $request->all();

                $post['workspace']  = $currantWorkspace->id;
                $post['created_by'] = $objUser->id;
                $userList           = [];
                if(isset($post['users_list']))
                {
                    $userList = $post['users_list'];
                }
                $userList[] = $objUser->email;
                $userList   = array_filter($userList);
                $objProject = Project::create($post);

                foreach($userList as $email)
                {
                    $permission    = 'Member';
                    $registerUsers = User::where('email', $email)->first();
                    if($registerUsers)
                    {
                        if($registerUsers->id == $objUser->id)
                        {
                            $permission = 'Owner';
                        }
                        $this->inviteUser($registerUsers, $objProject, $permission);
                    }
                    else
                    {
                        $arrUser                      = [];
                        $arrUser['name']              = 'No Name';
                        $arrUser['email']             = $email;
                        $password                     = Str::random(8);
                        $arrUser['password']          = Hash::make($password);
                        $arrUser['currant_workspace'] = $objProject->workspace;
                        $registerUsers                = User::create($arrUser);
                        $registerUsers->password      = $password;

                        $assignPlan = $registerUsers->assignPlan(1);


                        try
                        {
                            Mail::to($email)->send(new SendLoginDetail($registerUsers));
                        }
                        catch(\Exception $e)
                        {
                            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                        }

                        $this->inviteUser($registerUsers, $objProject, $permission);
                    }
                }

                return redirect()->route('projects.index', $currantWorkspace->slug)->with('success', __('Project Created Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
            }
            else
            {
                return redirect()->back()->with('error', __('Your project limit is over, Please upgrade plan.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Default plan is deleted.'));
        }
    }

    public function inviteUser(User $user, Project $project, $permission)
    {
        // assign workspace first
        $is_assigned = false;
        foreach($user->workspace as $workspace)
        {
            if($workspace->id == $project->workspace)
            {
                $is_assigned = true;
            }
        }

        if(!$is_assigned)
        {
            UserWorkspace::create(
                [
                    'user_id' => $user->id,
                    'workspace_id' => $project->workspace,
                    'permission' => $permission,
                ]
            );
            try
            {
                Mail::to($user->email)->send(new SendWorkspaceInvication($user, $project->workspaceData));
            }
            catch(\Exception $e)
            {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }
        }

        // assign project
        $arrData               = [];
        $arrData['user_id']    = $user->id;
        $arrData['project_id'] = $project->id;
        $is_invited            = UserProject::where($arrData)->first();
        if(!$is_invited)
        {
            UserProject::create($arrData);
            if($permission != 'Owner')
            {
                try
                {
                    Mail::to($user->email)->send(new SendInvication($user, $project));
                }
                catch(\Exception $e)
                {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }
                Utility::sandNotification('project_assign', $project->workspaceData, $user->id, $project);
            }
        }
    }

    public function invite($slug, $projectID, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $post             = $request->all();
        $userList         = $post['users_list'];

        $objProject = Project::find($projectID);

        foreach($userList as $email)
        {
            $permission    = 'Member';
            $registerUsers = User::where('email', $email)->first();
            if($registerUsers)
            {
                $this->inviteUser($registerUsers, $objProject, $permission);
            }
            else
            {
                $arrUser                      = [];
                $arrUser['name']              = 'No Name';
                $arrUser['email']             = $email;
                $password                     = Str::random(8);
                $arrUser['password']          = Hash::make($password);
                $arrUser['currant_workspace'] = $objProject->workspace;
                $registerUsers                = User::create($arrUser);
                $registerUsers->password      = $password;

                $assignPlan = $registerUsers->assignPlan(1);
                if($assignPlan['is_success'])
                {
                    return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
                }
                else
                {
                    return redirect()->route('plans.index')->with('error', __($assignPlan['error']));
                }

                try
                {
                    Mail::to($email)->send(new SendLoginDetail($registerUsers));
                }
                catch(\Exception $e)
                {
                    $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                }

                $this->inviteUser($registerUsers, $objProject, $permission);
            }

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'user_type' => get_class(\Auth::user()),
                    'project_id' => $objProject->id,
                    'log_type' => 'Invite User',
                    'remark' => json_encode(['user_id' => $registerUsers->id]),
                ]
            );
        }

        return redirect()->back()->with('success', __('Users Invited Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function show($slug, $projectID)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        $chartData = $this->getProjectChart(
            [
                'workspace_id'=> $currantWorkspace->id,
                'project_id' => $projectID,
                'duration' => 'week',
            ]
        );

        return view('projects.show', compact('currantWorkspace', 'project', 'chartData'));
    }

    public function getProjectChart($arrParam)
    {
        $arrDuration = [];
        if($arrParam['duration'])
        {

            if($arrParam['duration'] == 'week')
            {
                $previous_week = strtotime("-1 week +1 day");


                for($i = 0; $i < 7; $i++)
                {
                    $arrDuration[date('Y-m-d', $previous_week)] = date('D', $previous_week);
                    $previous_week                              = strtotime(date('Y-m-d', $previous_week) . " +1 day");
                }
            }
        }
        //        dd($arrDuration);
        $arrTask             = [];
        $arrTask['label']    = [];
        $stages = Stage::where('workspace_id','=',$arrParam['workspace_id'])->orderBy('order')->pluck('name','id')->all();

        foreach($arrDuration as $date => $label)
        {
            $objProject = Task::select('status', DB::raw('count(*) as total'))->whereDate('updated_at', '=', $date)->groupBy('status');

            if(isset($arrParam['project_id']))
            {
                $objProject->where('project_id', '=', $arrParam['project_id']);
            }
            if(isset($arrParam['workspace_id']))
            {
                $objProject->whereIn(
                    'project_id', function ($query) use ($arrParam){
                    $query->select('id')->from('projects')->where('workspace', '=', $arrParam['workspace_id']);
                }
                );
            }
            $data     = $objProject->pluck('total','status')->all();

            foreach($stages as $id => $stage)
            {
                if(isset($data[$id]))
                {
                    $arrTask[$id][] = $data[$id];
                }else{
                    $arrTask[$id][] = 0;
                }
            }
            $arrTask['label'][]    = __($label);
        }
        $arrTask['stages']=$stages;
        return $arrTask;

    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($slug, $projectID)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();

        return view('projects.edit', compact('currantWorkspace', 'project'));
    }

    public function create($slug)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        return view('projects.create', compact('currantWorkspace'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function popup($slug, $projectID)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();

        return view('projects.invite', compact('currantWorkspace', 'project'));
    }

    public function userDelete($slug, $project_id, $user_id)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        if($currantWorkspace->permission == 'Owner')
        {
            if(count($project->user_tasks($user_id)) == 0)
            {
                UserProject::where('user_id', '=', $user_id)->where('project_id', '=', $project->id)->delete();

                return redirect()->back()->with('success', __('User Deleted Successfully!'));
            }
            else
            {
                return redirect()->back()->with('warning', __('Please Remove User From Tasks!'));
            }
        }
        else
        {
            return redirect()->route('projects.index', $slug)->with('error', __('You can\'t Delete Project!'));
        }
    }

    public function sharePopup($slug, $projectID)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();

        return view('projects.share', compact('currantWorkspace', 'project'));
    }

    public function clientDelete($slug, $project_id, $client_id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::find($project_id)->first();
        if($currantWorkspace->permission == 'Owner')
        {
            ClientProject::where('client_id', '=', $client_id)->where('project_id', '=', $project->id)->delete();

            return redirect()->back()->with('success', __('Client Deleted Successfully!'));
        }
        else
        {
            return redirect()->route('projects.index', $slug)->with('error', __('You can\'t Delete Project!'));
        }
    }

    public function share($slug, $projectID, Request $request)
    {
        $project = Project::find($projectID);
        foreach($request->clients as $client_id)
        {
            $client = Client::find($client_id);

            if(ClientProject::where('client_id', '=', $client_id)->where('project_id', '=', $projectID)->count() == 0)
            {
                ClientProject::create(
                    [
                        'client_id' => $client_id,
                        'project_id' => $projectID,
                        'permission' => json_encode($client->getAllPermission()),
                    ]
                );
            }

            try
            {
                Mail::to($client->email)->send(new ShareProjectToClient($client, $project));
            }
            catch(\Exception $e)
            {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

            ActivityLog::create(
                [
                    'user_id' => \Auth::user()->id,
                    'user_type' => get_class(\Auth::user()),
                    'project_id' => $project->id,
                    'log_type' => 'Share with Client',
                    'remark' => json_encode(['client_id' => $client->id]),
                ]
            );

        }

        return redirect()->back()->with('success', __('Project Share Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Project $project
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $slug, $projectID)
    {
        $request->validate(
            [
                'name' => 'required',
            ]
        );
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        $project->update($request->all());

        return redirect()->back()->with('success', __('Project Updated Successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Int $projectID
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug, $projectID)
    {
        $objUser = Auth::user();
        $project = Project::find($projectID);

        if($project->created_by == $objUser->id)
        {
            UserProject::where('project_id', '=', $projectID)->delete();
            ProjectFile::where('project_id', '=', $projectID)->delete();
            $project->delete();

            return redirect()->route('projects.index', $slug)->with('success', __('Project Deleted Successfully!'));
        }
        else
        {
            return redirect()->route('projects.index', $slug)->with('error', __('You can\'t Delete Project!'));
        }
    }

    /**
     * Leave the specified resource from storage.
     *
     * @param Int $projectID
     *
     * @return \Illuminate\Http\Response
     */
    public function leave($slug, $projectID)
    {
        $objUser     = Auth::user();
        $userProject = Project::find($projectID);
        UserProject::where('project_id', '=', $userProject->id)->where('user_id', '=', $objUser->id)->delete();

        return redirect()->route('projects.index', $slug)->with('success', __('Project Leave Successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Int $projectID
     *
     * @return \Illuminate\Http\Response
     */
    public function taskBoard($slug, $projectID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        $objUser = Auth::user();
        if($objUser->getGuard() == 'client')
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        $stages = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->get();

        $statusClass = [];
        foreach($stages as &$status)
        {
            $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
            $task          = Task::where('project_id', '=', $projectID);
            if($currantWorkspace->permission != 'Owner' && $objUser->getGuard() != 'client')
            {
                if(isset($objUser) && $objUser)
                {
                    $task->where('assign_to', '=', $objUser->id);
                }
            }
            $task->orderBy('order');
            $status['tasks'] = $task->where('status', '=', $status->id)->get();
        }

        return view('projects.taskboard', compact('currantWorkspace', 'project', 'stages', 'statusClass'));
    }

    public function taskCreate($slug, $projectID)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client')
        {
            $project  = Project::select('projects.*')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        else
        {
            $project  = Project::select('projects.*')->where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }

        $users = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $projectID)->get();

        return view('projects.taskCreate', compact('currantWorkspace', 'project', 'projects', 'users'));
    }

    public function taskStore(Request $request, $slug, $projectID)
    {
        $request->validate(
            [
                'project_id' => 'required',
                'title' => 'required',
                'priority' => 'required',
                'assign_to' => 'required',
                'due_date' => 'required',
            ]
        );
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $request->project_id)->first();
        }

        if($project)
        {
            $post = $request->all();
            $stage = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->first();
            if($stage) {
                $post['status']= $stage->id;
                $task = Task::create($post);

                ActivityLog::create(
                    [
                        'user_id' => \Auth::user()->id,
                        'user_type' => get_class(\Auth::user()),
                        'project_id' => $projectID,
                        'log_type' => 'Create Task',
                        'remark' => json_encode(['title' => $task->title]),
                    ]
                );

                Utility::sandNotification('task_assign', $currantWorkspace, $request->assign_to, $task);

                if ($objUser->getGuard() == 'client') {
                    return redirect()->route(
                        'client.projects.task.board', [
                            $currantWorkspace->slug,
                            $request->project_id,
                        ]
                    )->with('success', __('Task Create Successfully!'));
                } else {
                    return redirect()->route(
                        'projects.task.board', [
                            $currantWorkspace->slug,
                            $request->project_id,
                        ]
                    )->with('success', __('Task Create Successfully!'));
                }
            }else{
                return redirect()->back()->with('error', __('Please add stages first.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('You can \'t Add Task!'));
        }
    }

    public function taskOrderUpdate(Request $request, $slug, $projectID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if(isset($request->sort))
        {
            foreach($request->sort as $index => $taskID)
            {
                echo $index . "-" . $taskID;
                $task        = Task::find($taskID);
                $task->order = $index;
                $task->save();
            }
        }
        if($request->new_status != $request->old_status)
        {
            $new_status = Stage::find($request->new_status);
            $old_status = Stage::find($request->old_status);
            $user         = Auth::user();
            $task         = Task::find($request->id);
            $task->status = $request->new_status;
            $task->save();

            $name = $user->name;
            $id   = $user->id;

            ActivityLog::create(
                [
                    'user_id' => $id,
                    'user_type' => get_class($user),
                    'project_id' => $projectID,
                    'log_type' => 'Move',
                    'remark' => json_encode(
                        [
                            'title' => $task->title,
                            'old_status' => $old_status->name,
                            'new_status' => $new_status->name,
                        ]
                    ),
                ]
            );

            return $task->toJson();
        }
    }

    public function taskEdit($slug, $projectID, $taskId)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project  = Project::select('projects.*')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        else
        {
            $project  = Project::select('projects.*')->where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
            $projects = Project::select('projects.*')->where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        $users = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $projectID)->get();
        $task  = Task::find($taskId);

        return view('projects.taskEdit', compact('currantWorkspace', 'project', 'projects', 'users', 'task'));
    }

    public function taskUpdate(Request $request, $slug, $projectID, $taskID)
    {
        $request->validate(
            [
                'project_id' => 'required',
                'title' => 'required',
                'priority' => 'required',
                'assign_to' => 'required',
                'due_date' => 'required',
            ]
        );
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $request->project_id)->first();
        }
        if($project)
        {
            $post = $request->all();
            $task = Task::find($taskID);
            $task->update($post);

            return redirect()->back()->with('success', __('Task Updated Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('You can \'t Edit Task!'));
        }
    }

    public function taskDestroy($slug, $projectID, $taskID)
    {
        $objUser = Auth::user();
        $task    = Task::find($taskID);
        $project = Project::find($task->project_id);
        if($project->created_by == $objUser->id || $objUser->getGuard() == 'client')
        {
            $task->delete();

            return redirect()->back()->with('success', __('Task Deleted Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('You can\'t Delete Task!'));
        }
    }

    public function taskShow($slug, $projectID, $taskID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $task             = Task::find($taskID);
        $objUser          = Auth::user();

        $clientID = '';
        if($objUser->getGuard() == 'client')
        {
            $clientID = $objUser->id;
        }

        return view('projects.taskShow', compact('currantWorkspace', 'task', 'clientID'));
    }

    public function taskDrag(Request $request, $slug, $projectID, $taskID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $task             = Task::find($taskID);
        $task->start_date = $request->start;
        $task->due_date   = $request->end;
        $task->save();
    }

    public function commentStore(Request $request, $slug, $projectID, $taskID, $clientID = '')
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $post             = [];
        $post['task_id']  = $taskID;
        $post['comment']  = $request->comment;
        if($clientID)
        {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        }
        else
        {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $comment = Comment::create($post);
        if($comment->user_type == 'Client')
        {
            $user = $comment->client;
        }
        else
        {
            $user = $comment->user;
        }
        if(empty($clientID))
        {
            $comment->deleteUrl = route(
                'comment.destroy', [
                                     $currantWorkspace->slug,
                                     $projectID,
                                     $taskID,
                                     $comment->id,
                                 ]
            );
        }

        return $comment->toJson();
    }

    public function commentDestroy(Request $request, $slug, $projectID, $taskID, $commentID)
    {
        $comment = Comment::find($commentID);
        $comment->delete();

        return "true";
    }

    public function commentStoreFile(Request $request, $slug, $projectID, $taskID, $clientID = '')
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $request->validate(
            ['file' => 'required|mimes:jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,zip,rar|max:2048']
        );
        $fileName = $taskID . time() . "_" . $request->file->getClientOriginalName();
        $request->file->storeAs('tasks', $fileName);
        $post['task_id']   = $taskID;
        $post['file']      = $fileName;
        $post['name']      = $request->file->getClientOriginalName();
        $post['extension'] = "." . $request->file->getClientOriginalExtension();
        $post['file_size'] = round(($request->file->getSize() / 1024) / 1024, 2) . ' MB';
        if($clientID)
        {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        }
        else
        {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $TaskFile            = TaskFile::create($post);
        $user                = $TaskFile->user;
        $TaskFile->deleteUrl = '';
        if(empty($clientID))
        {
            $TaskFile->deleteUrl = route(
                'comment.destroy.file', [
                                          $currantWorkspace->slug,
                                          $projectID,
                                          $taskID,
                                          $TaskFile->id,
                                      ]
            );
        }

        return $TaskFile->toJson();
    }

    public function commentDestroyFile(Request $request, $slug, $projectID, $taskID, $fileID)
    {
        $commentFile = TaskFile::find($fileID);
        $path        = storage_path('tasks/' . $commentFile->file);
        if(file_exists($path))
        {
            \File::delete($path);
        }
        $commentFile->delete();

        return "true";
    }

    public function getSearchJson($slug, $search)
    {
        $user             = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($user->getGuard() == 'client')
        {
            $objProject = Project::select(
                [
                    'projects.id',
                    'projects.name',
                ]
            )->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $user->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.name', 'LIKE', $search . "%")->get();
            $arrProject = [];
            foreach($objProject as $project)
            {
                $arrProject[] = [
                    'text' => $project->name,
                    'link' => route(
                        'client.projects.show', [
                                                  $currantWorkspace->slug,
                                                  $project->id,
                                              ]
                    ),
                ];
            }
        }
        else
        {
            $objProject = Project::select(
                [
                    'projects.id',
                    'projects.name',
                ]
            )->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $user->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.name', 'LIKE', $search . "%")->get();
            $arrProject = [];
            foreach($objProject as $project)
            {
                $arrProject[] = [
                    'text' => $project->name,
                    'link' => route(
                        'projects.show', [
                                           $currantWorkspace->slug,
                                           $project->id,
                                       ]
                    ),
                ];
            }
        }

        if($user->getGuard() == 'client')
        {
            $arrTask = [];
            $objTask = Task::select(
                [
                    'tasks.project_id',
                    'tasks.title',
                ]
            )->join('projects', 'tasks.project_id', '=', 'projects.id')->join('client_projects', 'client_projects.project_id', '=', 'projects.id')->where('client_projects.client_id', '=', $user->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.title', 'LIKE', $search . "%")->get();
            foreach($objTask as $task)
            {
                $arrTask[] = [
                    'text' => $task->title,
                    'link' => route(
                        'client.projects.task.board', [
                                                        $currantWorkspace->slug,
                                                        $task->project_id,
                                                    ]
                    ),
                ];
            }
        }
        else
        {
            $objTask = Task::select(
                [
                    'tasks.project_id',
                    'tasks.title',
                ]
            )->join('projects', 'tasks.project_id', '=', 'projects.id')->join('user_projects', 'user_projects.project_id', '=', 'projects.id')->where('user_projects.user_id', '=', $user->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.title', 'LIKE', $search . "%")->get();
            $arrTask = [];
            foreach($objTask as $task)
            {
                $arrTask[] = [
                    'text' => $task->title,
                    'link' => route(
                        'projects.task.board', [
                                                 $currantWorkspace->slug,
                                                 $task->project_id,
                                             ]
                    ),
                ];
            }
        }

        return json_encode(
            [
                'Projects' => $arrProject,
                'Tasks' => $arrTask,
            ]
        );
    }

    public function milestone($slug, $projectID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::find($projectID);

        return view('projects.milestone', compact('currantWorkspace', 'project'));
    }

    public function milestoneStore($slug, $projectID, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::find($projectID);
        $request->validate(
            [
                'title' => 'required',
                'status' => 'required',
                'cost' => 'required',
            ]
        );

        $milestone             = new Milestone();
        $milestone->project_id = $project->id;
        $milestone->title      = $request->title;
        $milestone->status     = $request->status;
        $milestone->cost       = $request->cost;
        $milestone->summary    = $request->summary;
        $milestone->save();

        ActivityLog::create(
            [
                'user_id' => \Auth::user()->id,
                'user_type' => get_class(\Auth::user()),
                'project_id' => $project->id,
                'log_type' => 'Create Milestone',
                'remark' => json_encode(['title' => $milestone->title]),
            ]
        );

        return redirect()->back()->with('success', __('Milestone Created Successfully!'));
    }

    public function milestoneEdit($slug, $milestoneID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $milestone        = Milestone::find($milestoneID);

        return view('projects.milestoneEdit', compact('currantWorkspace', 'milestone'));
    }

    public function milestoneUpdate($slug, $milestoneID, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        $request->validate(
            [
                'title' => 'required',
                'status' => 'required',
                'cost' => 'required',
            ]
        );

        $milestone          = Milestone::find($milestoneID);
        $milestone->title   = $request->title;
        $milestone->status  = $request->status;
        $milestone->cost    = $request->cost;
        $milestone->summary = $request->summary;
        $milestone->save();

        return redirect()->back()->with('success', __('Milestone Updated Successfully!'));
    }

    public function milestoneDestroy($slug, $milestoneID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $milestone        = Milestone::find($milestoneID);
        $milestone->delete();

        return redirect()->back()->with('success', __('Milestone deleted Successfully!'));
    }

    public function milestoneShow($slug, $milestoneID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $milestone        = Milestone::find($milestoneID);

        return view('projects.milestoneShow', compact('currantWorkspace', 'milestone'));
    }


    public function subTaskStore(Request $request, $slug, $projectID, $taskID, $clientID = '')
    {
        $post             = [];
        $post['task_id']  = $taskID;
        $post['name']     = $request->name;
        $post['due_date'] = $request->due_date;
        $post['status']   = 0;
        if($clientID)
        {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        }
        else
        {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $subtask = SubTask::create($post);
        if($subtask->user_type == 'Client')
        {
            $user = $subtask->client;
        }
        else
        {
            $user = $subtask->user;
        }
        $subtask->updateUrl = route(
            'subtask.update', [
                                $slug,
                                $projectID,
                                $subtask->id,
                            ]
        );
        $subtask->deleteUrl = route(
            'subtask.destroy', [
                                 $slug,
                                 $projectID,
                                 $subtask->id,
                             ]
        );

        return $subtask->toJson();
    }

    public function subTaskUpdate($slug, $projectID, $subtaskID)
    {
        $subtask = SubTask::find($subtaskID);
        if($subtask->status == 0)
        {
            $subtask->status = 1;
        }
        else
        {
            $subtask->status = 0;
        }
        $subtask->save();

        return $subtask->toJson();
    }

    public function subTaskDestroy($slug, $projectID, $subtaskID)
    {
        $subtask = SubTask::find($subtaskID);
        $subtask->delete();

        return "true";
    }

    public function fileUpload($slug, $id, Request $request)
    {
        $project = Project::find($id);
        $request->validate(['file' => 'required|mimes:png,jpeg,jpg,pdf,doc,txt|max:2048']);
        $file_name = $request->file->getClientOriginalName();
        $file_path = $project->id . "_" . md5(time()) . "_" . $request->file->getClientOriginalName();
        $request->file->storeAs('project_files', $file_path);

        $file                 = ProjectFile::create(
            [
                'project_id' => $project->id,
                'file_name' => $file_name,
                'file_path' => $file_path,
            ]
        );
        $return               = [];
        $return['is_success'] = true;
        $return['download']   = route(
            'projects.file.download', [
                                        $slug,
                                        $project->id,
                                        $file->id,
                                    ]
        );
        $return['delete']     = route(
            'projects.file.delete', [
                                      $slug,
                                      $project->id,
                                      $file->id,
                                  ]
        );

        ActivityLog::create(
            [
                'user_id' => \Auth::user()->id,
                'user_type' => get_class(\Auth::user()),
                'project_id' => $project->id,
                'log_type' => 'Upload File',
                'remark' => json_encode(['file_name' => $file_name]),
            ]
        );

        return response()->json($return);
    }

    public function fileDownload($slug, $id, $file_id)
    {

        $project = Project::find($id);

        $file = ProjectFile::find($file_id);
        if($file)
        {
            $file_path = storage_path('project_files/' . $file->file_path);
            $filename  = $file->file_name;

            return \Response::download(
                $file_path, $filename, [
                              'Content-Length: ' . filesize($file_path),
                          ]
            );
        }
        else
        {
            return redirect()->back()->with('error', __('File is not exist.'));
        }
    }

    public function fileDelete($slug, $id, $file_id)
    {
        $project = Project::find($id);

        $file = ProjectFile::find($file_id);
        if($file)
        {
            $path = storage_path('project_files/' . $file->file_path);
            if(file_exists($path))
            {
                \File::delete($path);
            }
            $file->delete();

            return response()->json(['is_success' => true], 200);
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'error' => __('File is not exist.'),
                ], 200
            );
        }
    }

    // Timesheet
    public function timesheet($slug)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $objUser = Auth::user();
        if($objUser->getGuard() == 'client'){
            $timesheets = Timesheet::select('timesheets.*')->join('projects', 'projects.id', '=', 'timesheets.project_id')->join('tasks', 'tasks.id', '=', 'timesheets.task_id')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->where('client_projects.client_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('client_projects.permission','LIKE','%show timesheet%')->get();
        }
        elseif ($currantWorkspace->permission == 'Owner') {
            $timesheets = Timesheet::select('timesheets.*')->join('projects', 'projects.id', '=', 'timesheets.project_id')->join('tasks', 'tasks.id', '=', 'timesheets.task_id')->where('projects.workspace', '=', $currantWorkspace->id)->get();
        } else {
            $timesheets = Timesheet::select('timesheets.*')->join('projects', 'projects.id', '=', 'timesheets.project_id')->join('tasks', 'tasks.id', '=', 'timesheets.task_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', $objUser->id)->get();
        }
        return view('projects.timesheet', compact('currantWorkspace', 'timesheets'));

    }

    public function timesheetCreate($slug)
    {
        $objUser = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $projects = Project::select('projects.*')->join('user_projects','projects.id','=','user_projects.project_id')->where('user_projects.user_id','=',$objUser->id)->where('projects.workspace','=',$currantWorkspace->id)->get();

        return view('projects.timesheetCreate', compact('currantWorkspace', 'projects'));
    }

    public function getTask($slug,$project_id=null){

        if($project_id) {
            $currantWorkspace = Utility::getWorkspaceBySlug($slug);
            $objUser = Auth::user();
            if ($currantWorkspace->permission == 'Owner') {
                $tasks = Task::where('project_id', '=', $project_id)->get();
            } else {
                $tasks = Task::where('project_id', '=', $project_id)->where('assign_to', '=', $objUser->id)->get();
            }
            return response()->json($tasks);
        }
    }

    public function timesheetStore($slug, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $request->validate(
            [
                'task_id' => 'required',
                'date' => 'required',
                'time' => 'required',
            ]
        );

        $timesheet              = new Timesheet();
        $timesheet->project_id  = $request->project_id;
        $timesheet->task_id     = $request->task_id;
        $timesheet->date        = $request->date;
        $timesheet->time        = $request->time;
        $timesheet->description = $request->description;
        $timesheet->save();

        ActivityLog::create(
            [
                'user_id' => \Auth::user()->id,
                'user_type' => get_class(\Auth::user()),
                'project_id' => $request->project_id,
                'log_type' => 'Create Timesheet',
                'remark' => json_encode(['name' => \Auth::user()->name]),
            ]
        );


        return redirect()->back()->with('success', __('Timesheet Created Successfully!'));
    }

    public function timesheetEdit($slug, $timesheetID)
    {
        $objUser = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $projects = Project::select('projects.*')->join('user_projects','projects.id','=','user_projects.project_id')->where('user_projects.user_id','=',$objUser->id)->where('projects.workspace','=',$currantWorkspace->id)->get();

        $timesheet        = Timesheet::find($timesheetID);

        return view('projects.timesheetEdit', compact('currantWorkspace', 'timesheet', 'projects'));
    }

    public function timesheetUpdate($slug, $timesheetID, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        $request->validate(
            [
                'task_id' => 'required',
                'date' => 'required',
                'time' => 'required',
            ]
        );

        $timesheet              = Timesheet::find($timesheetID);
        $timesheet->project_id  = $request->project_id;
        $timesheet->task_id     = $request->task_id;
        $timesheet->date        = $request->date;
        $timesheet->time        = $request->time;
        $timesheet->description = $request->description;
        $timesheet->save();

        return redirect()->back()->with('success', __('Timesheet Updated Successfully!'));
    }

    public function timesheetDestroy($slug, $timesheetID)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $timesheet        = Timesheet::find($timesheetID);
        $timesheet->delete();

        return redirect()->back()->with('success', __('Timesheet deleted Successfully!'));
    }

    public function clientPermission($slug, $project_id, $client_id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $project          = Project::find($project_id);
        $client           = Client::find($client_id);
        $permissions      = $client->getPermission($project_id);
        if(!$permissions)
        {
            $permissions = [];
        }

        return view('projects.client_permission', compact('currantWorkspace', 'project', 'client', 'permissions'));
    }

    public function clientPermissionStore($slug, $project_id, $client_id, Request $request)
    {
        $currantWorkspace          = Utility::getWorkspaceBySlug($slug);
        $clientProject             = ClientProject::where('client_id', '=', $client_id)->where('project_id', '=', $project_id)->first();
        $clientProject->permission = json_encode($request->permissions);
        $clientProject->save();

        return redirect()->back()->with('success', __('Permission Updated Successfully!'));
    }

    public function bugReport($slug, $project_id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        $objUser = Auth::user();
        if($objUser->getGuard() == 'client')
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        else
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        $stages = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->get();

        $statusClass = [];
        foreach($stages as &$status)
        {
            $statusClass[] = 'task-list-' . str_replace(' ', '_', $status->id);
            $bug           = BugReport::where('project_id', '=', $project_id);
            if($currantWorkspace->permission != 'Owner' && $objUser->getGuard() != 'client')
            {
                if(isset($objUser) && $objUser)
                {
                    $bug->where('assign_to', '=', $objUser->id);
                }
            }
            $bug->orderBy('order');

            $status['bugs'] = $bug->where('status', '=', $status->id)->get();
        }
        return view('projects.bug_report', compact('currantWorkspace', 'project', 'stages', 'statusClass'));
    }

    public function bugReportCreate($slug, $project_id)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        $arrStatus = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->pluck('name','id')->all();
        $users     = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $project_id)->get();

        return view('projects.bug_report_create', compact('currantWorkspace', 'project', 'users', 'arrStatus'));
    }

    public function bugReportStore(Request $request, $slug, $project_id)
    {
        $request->validate(
            [
                'title' => 'required',
                'priority' => 'required',
                'assign_to' => 'required',
                'status' => 'required',
            ]
        );
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }

        if($project)
        {
            $post               = $request->all();
            $post['project_id'] = $project_id;
            $bug                = BugReport::create($post);

            ActivityLog::create(
                [
                    'user_id' => $objUser->id,
                    'user_type' => get_class($objUser),
                    'project_id' => $project_id,
                    'log_type' => 'Create Bug',
                    'remark' => json_encode(['title' => $bug->title]),
                ]
            );
            Utility::sandNotification('bug_assign', $currantWorkspace, $request->assign_to, $bug);

            return redirect()->back()->with('success', __('Bug Create Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('You can \'t Add Bug!'));
        }
    }

    public function bugReportOrderUpdate(Request $request, $slug, $project_id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if(isset($request->sort))
        {
            foreach($request->sort as $index => $taskID)
            {
                echo $index . "-" . $taskID;
                $bug        = BugReport::find($taskID);
                $bug->order = $index;
                $bug->save();
            }
        }
        if($request->new_status != $request->old_status)
        {
            $new_status = BugStage::find($request->new_status);
            $old_status = BugStage::find($request->old_status);
            $user        = Auth::user();
            $bug         = BugReport::find($request->id);
            $bug->status = $request->new_status;
            $bug->save();

            $name = $user->name;
            $id   = $user->id;

            ActivityLog::create(
                [
                    'user_id' => $id,
                    'user_type' => get_class($user),
                    'project_id' => $project_id,
                    'log_type' => 'Move Bug',
                    'remark' => json_encode(
                        [
                            'title' => $bug->title,
                            'old_status' => $old_status->name,
                            'new_status' => $new_status->name,
                        ]
                    ),
                ]
            );

            return $bug->toJson();
        }
    }

    public function bugReportEdit($slug, $project_id, $bug_id)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        $users     = User::select('users.*')->join('user_projects', 'user_projects.user_id', '=', 'users.id')->where('project_id', '=', $project_id)->get();
        $bug       = BugReport::find($bug_id);
        $arrStatus = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->pluck('name','id')->all();

        return view('projects.bug_report_edit', compact('currantWorkspace', 'project', 'users', 'bug', 'arrStatus'));
    }

    public function bugReportUpdate(Request $request, $slug, $project_id, $bug_id)
    {
        $request->validate(
            [
                'title' => 'required',
                'priority' => 'required',
                'assign_to' => 'required',
                'status' => 'required',
            ]
        );
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        else
        {
            $project = Project::where('created_by', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $project_id)->first();
        }
        if($project)
        {
            $post = $request->all();
            $bug  = BugReport::find($bug_id);
            $bug->update($post);

            return redirect()->back()->with('success', __('Bug Updated Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('You can \'t Edit Bug!'));
        }
    }

    public function bugReportDestroy($slug, $project_id, $bug_id)
    {
        $objUser = Auth::user();
        $bug     = BugReport::find($bug_id);
        $project = Project::find($bug->project_id);
        if($project->created_by == $objUser->id || $objUser->getGuard() == 'client')
        {
            $bug->delete();

            return redirect()->back()->with('success', __('Bug Deleted Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('You can\'t Delete Bug!'));
        }
    }

    public function bugReportShow($slug, $project_id, $bug_id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $bug              = BugReport::find($bug_id);
        $objUser          = Auth::user();

        $clientID = '';
        if($objUser->getGuard() == 'client')
        {
            $clientID = $objUser->id;
        }

        return view('projects.bug_report_show', compact('currantWorkspace', 'bug', 'clientID'));
    }

    public function bugCommentStore(Request $request, $slug, $project_id, $bugID, $clientID = '')
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $post             = [];
        $post['bug_id']   = $bugID;
        $post['comment']  = $request->comment;
        if($clientID)
        {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        }
        else
        {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $comment = BugComment::create($post);
        if($comment->user_type == 'Client')
        {
            $user = $comment->client;
        }
        else
        {
            $user = $comment->user;
        }
        if(empty($clientID))
        {
            $comment->deleteUrl = route(
                'bug.comment.destroy', [
                                         $currantWorkspace->slug,
                                         $project_id,
                                         $bugID,
                                         $comment->id,
                                     ]
            );
        }

        return $comment->toJson();
    }

    public function bugCommentDestroy(Request $request, $slug, $project_id, $bug_id, $comment_id)
    {
        $comment = BugComment::find($comment_id);
        $comment->delete();

        return "true";
    }

    public function bugStoreFile(Request $request, $slug, $project_id, $bug_id, $clientID = '')
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $request->validate(
            ['file' => 'required|mimes:jpeg,jpg,png,gif,svg,pdf,txt,doc,docx,zip,rar|max:2048']
        );
        $fileName = $bug_id . time() . "_" . $request->file->getClientOriginalName();
        $request->file->storeAs('tasks', $fileName);
        $post['bug_id']    = $bug_id;
        $post['file']      = $fileName;
        $post['name']      = $request->file->getClientOriginalName();
        $post['extension'] = "." . $request->file->getClientOriginalExtension();
        $post['file_size'] = round(($request->file->getSize() / 1024) / 1024, 2) . ' MB';
        if($clientID)
        {
            $post['created_by'] = $clientID;
            $post['user_type']  = 'Client';
        }
        else
        {
            $post['created_by'] = Auth::user()->id;
            $post['user_type']  = 'User';
        }
        $TaskFile            = BugFile::create($post);
        $user                = $TaskFile->user;
        $TaskFile->deleteUrl = '';
        if(empty($clientID))
        {
            $TaskFile->deleteUrl = route(
                'bug.comment.destroy.file', [
                                              $currantWorkspace->slug,
                                              $project_id,
                                              $bug_id,
                                              $TaskFile->id,
                                          ]
            );
        }

        return $TaskFile->toJson();
    }

    public function bugDestroyFile(Request $request, $slug, $project_id, $bug_id, $file_id)
    {
        $commentFile = BugFile::find($file_id);
        $path        = storage_path('tasks/' . $commentFile->file);
        if(file_exists($path))
        {
            \File::delete($path);
        }
        $commentFile->delete();

        return "true";
    }

    public function allTasks($slug)
    {
        $userObj          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($userObj->getGuard() == 'client')
        {
            $projects = Project::select('projects.*')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->where('client_projects.client_id', '=', $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        else
        {
            $projects = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }
        $stages = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->get();
        $users = User::select('users.*')->join('user_workspaces', 'user_workspaces.user_id', '=', 'users.id')->where('user_workspaces.workspace_id', '=', $currantWorkspace->id)->get();

        return view('projects.tasks', compact('currantWorkspace', 'projects', 'users','stages'));
    }

    public function ajax_tasks($slug, Request $request)
    {
        $userObj          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->permission == 'Owner')
        {
            $tasks = Task::select(['tasks.*','stages.name as stage','stages.complete'])->join("user_projects", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->join("stages", "stages.id", "=", "tasks.status")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id);
        }
        else
        {
            $tasks = Task::select(['tasks.*','stages.name as stage','stages.complete'])->join("user_projects", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->join("stages", "stages.id", "=", "tasks.status")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', $userObj->id);
        }
        if($request->project)
        {
            $tasks->where('tasks.project_id', '=', $request->project);
        }
        if($request->assign_to)
        {
            $tasks->where('assign_to', '=', $request->assign_to);
        }
        if($request->priority)
        {
            $tasks->where('priority', '=', $request->priority);
        }
        if($request->status)
        {
            $tasks->where('tasks.status', '=', $request->status);
        }
        if($request->start_date && $request->end_date)
        {
            $tasks->whereBetween(
                'tasks.due_date', [
                    $request->start_date,
                    $request->end_date,
                ]
            );
        }
        $tasks = $tasks->get();
        $data  = [];
        foreach($tasks as $task)
        {
            $tmp                 = [];
            $tmp['title']        = '<a href="' . route(
                    'projects.task.board', [
                        $currantWorkspace->slug,
                        $task->project_id,
                    ]
                ) . '" class="text-body">' . $task->title . '</a>';
            $tmp['project_name'] = $task->project->name;
            $tmp['milestone']    = ($milestone = $task->milestone()) ? $milestone->title : '';
            $tmp['due_date']     = \App\Utility::get_timeago(strtotime($task->due_date));
            if($currantWorkspace->permission == 'Owner' || Auth::user()->getGuard() == 'client')
            {
                $tmp['user_name'] = $task->user->name;
            }

            if($task->complete == 1)
            {
                $tmp['status'] = '<span class="badge badge-success">' . __($task->stage) . '</span>';
            }else{
                $tmp['status'] = '<span class="badge badge-primary">' . __($task->stage) . '</span>';
            }

            if($task->priority == "High")
            {
                $tmp['priority'] = '<span class="badge badge-danger">' . __('High') . '</span>';
            }
            elseif($task->priority == "Medium")
            {
                $tmp['priority'] = '<span class="badge badge-info">' . __('Medium') . '</span>';
            }
            else
            {
                $tmp['priority'] = '<span class="badge badge-success">' . __('Low') . '</span>';
            }

            if($currantWorkspace->permission == 'Owner')
            {
                $tmp['action'] = '<a href="#" class="btn btn-sm btn-outline-primary" data-ajax-popup="true" data-size="lg" data-title="' . __('Edit Task') . '" data-url="' . route(
                        'tasks.edit', [
                            $currantWorkspace->slug,
                            $task->project_id,
                            $task->id,
                        ]
                    ) . '">
                                            <i class="mdi mdi-pencil mr-1"></i>' . __('Edit') . '</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm(\'' . __('Are you sure ?') . '\')?document.getElementById(\'delete-form-' . $task->id . '\').submit(): \'\');">
                                            <i class="mdi mdi-delete mr-1"></i>' . __('Delete') . '</a>
                                        <form id="delete-form-' . $task->id . '" action="' . route(
                        'tasks.destroy', [
                            $currantWorkspace->slug,
                            $task->project_id,
                            $task->id,
                        ]
                    ) . '" method="POST" style="display: none;">
                                            <input type="hidden" name="_token" value="' . csrf_token() . '">
                                            <input type="hidden" name="_method" value="DELETE">
                                        </form>';
            }
            $data[] = array_values($tmp);

        }

        return response()->json(['data' => $data], 200);
    }



    public function gantt($slug, $projectID, $duration = 'Week')
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        $tasks = [];
        if($project)
        {
            if($objUser->getGuard() == 'client' || $currantWorkspace->permission == 'Owner')
            {
                $tasksobj = Task::where('project_id', '=', $project->id)->orderBy('start_date')->get();
            }
            else
            {
                $tasksobj = Task::where('project_id', '=', $project->id)->where('assign_to', '=', $objUser->id)->orderBy('start_date')->get();
            }
            foreach($tasksobj as $task)
            {
                $tmp                 = [];
                $tmp['start']        = $task->start_date;
                $tmp['end']          = $task->due_date;
                $tmp['name']         = $task->title;
                $tmp['id']           = 'task_' . $task->id;
                $tmp['custom_class'] = strtolower($task->priority);
                $tmp['extra']        = [
                    'priority' => __($task->priority),
                    'comments' => count($task->comments),
                    'duration' => Date::parse($task->start_date)->format('d M Y H:i A') . ' - ' . Date::parse($task->due_date)->format('d M Y H:i A'),
                ];
                $tasks[]             = $tmp;
            }
        }

        return view('projects.gantt', compact('currantWorkspace', 'project', 'tasks', 'duration'));
    }


    public function ganttPost($slug, $projectID, Request $request)
    {
        $objUser          = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        if($objUser->getGuard() == 'client')
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        else
        {
            $project = Project::select('projects.*')->join('user_projects', 'projects.id', '=', 'user_projects.project_id')->where('user_projects.user_id', '=', $objUser->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('projects.id', '=', $projectID)->first();
        }
        if($project)
        {
            if($objUser->getGuard() == 'client' || $currantWorkspace->permission == 'Owner')
            {
                $id               = trim($request->task_id, 'task_');
                $task             = Task::find($id);
                $task->start_date = $request->start;
                $task->due_date   = $request->end;
                $task->save();

                return response()->json(
                    [
                        'is_success' => true,
                        'message' => __("Time Updated"),
                    ], 200
                );
            }
            else
            {
                return response()->json(
                    [
                        'is_success' => false,
                        'message' => __("You can't change Date!"),
                    ], 400
                );
            }
        }
        else
        {
            return response()->json(
                [
                    'is_success' => false,
                    'message' => __("Something is wrong."),
                ], 400
            );
        }
    }

}
