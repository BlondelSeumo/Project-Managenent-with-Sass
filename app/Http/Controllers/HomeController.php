<?php

namespace App\Http\Controllers;

use App\Client;
use App\ClientProject;
use App\ClientWorkspace;
use App\Order;
use App\Plan;
use App\User;
use App\Utility;
use App\UserProject;
use App\Task;
use App\Todo;
use App\Calendar;
use App\UserWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Storage;
class HomeController extends Controller
{

    public function check()
    {

        $user = Auth::user();
        if($user->type != 'admin') {
            $plan = Plan::find($user->plan);
            if ($plan) {
                if ($plan->duration != 'Unlimited') {
                    $datetime1 = new \DateTime($user->plan_expire_date);
                    $datetime2 = new \DateTime(date('Y-m-d'));
                    $interval = $datetime1->diff($datetime2);
                    $days = $interval->format('%a');
                    if ($days <= 0) {
                        $user->assignPlan(1);
                        return redirect()->route('home')->with('info', __('Your Plan is expired.'));
                    }
                }
            } else {
                return redirect()->route('home')
                    ->with('error', __('Plan not found'));
            }
        }
        return redirect()->route('home');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($slug = '')
    {
        $userObj = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($userObj->type=='admin'){
            $totalUsers = User::where('type','!=','admin')->count();
            $totalPaidUsers = User::where('type','!=','admin')->where('plan','!=',1)->count();
            $totalOrderAmount = Order::where('payment_status','=','succeeded')->sum('price');
            $totalOrders = Order::where('payment_status','=','succeeded')->count();
            $totalPlans = Plan::count();
            $mostPlans = Plan::where('id',function($query){
                $query->select('plan_id')
                    ->from('orders')
                    ->groupBy('plan_id')
                    ->orderBy(\DB::raw('COUNT(plan_id)'))->limit(1);
            })->first();

            $chartData = $this->getOrderChart(['duration'=>'week']);
            return view('home', compact('totalUsers','totalPaidUsers','totalOrders','totalOrderAmount','totalPlans','mostPlans','chartData'));
        }
        elseif($currantWorkspace) {

            if($userObj->getGuard() == 'client') {

                $totalProject = ClientProject::join("projects", "projects.id", "=", "client_projects.project_id")->where("client_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->count();
                $totalBugs = ClientProject::join("bug_reports", "bug_reports.project_id", "=", "client_projects.project_id")->join("projects", "projects.id", "=", "client_projects.project_id")->where('projects.workspace', '=', $currantWorkspace->id)->count();
                $totalTask = ClientProject::join("tasks", "tasks.project_id", "=", "client_projects.project_id")->join("projects", "projects.id", "=", "client_projects.project_id")->where('projects.workspace', '=', $currantWorkspace->id)->where("client_id", "=", $userObj->id)->count();
                $completeTask = ClientProject::join("tasks", "tasks.project_id", "=", "client_projects.project_id")->join("projects", "projects.id", "=", "client_projects.project_id")->where('projects.workspace', '=', $currantWorkspace->id)->where("client_id", "=", $userObj->id)->where('tasks.status', '=', 'done')->count();
                $tasks = Task::select(['tasks.*','stages.name as status','stages.complete'])->join("client_projects", "tasks.project_id", "=", "client_projects.project_id")->join("projects", "projects.id", "=", "client_projects.project_id")->join("stages", "stages.id", "=", "tasks.status")->where('projects.workspace', '=', $currantWorkspace->id)->where("client_id", "=", $userObj->id)->orderBy('tasks.id', 'desc')->limit(4)->get();
                $totalMembers = UserWorkspace::where('workspace_id', '=', $currantWorkspace->id)->count();
                $projectProcess = ClientProject::join("projects", "projects.id", "=", "client_projects.project_id")->where('projects.workspace', '=', $currantWorkspace->id)->where("client_id", "=", $userObj->id)->groupBy('projects.status')->selectRaw('count(projects.id) as count, projects.status')->pluck('count', 'projects.status');


                $arrProcessPer = [];
                $arrProcessLable = [];
                foreach ($projectProcess as $lable => $process) {
                    $arrProcessLable[] = $lable;
                    if($totalProject == 0){
                        $arrProcessPer[] = 0.00;
                    }else{
                        $arrProcessPer[] = round(($process * 100) / $totalProject, 2);
                    }

                }
                $arrProcessClass = ['text-success', 'text-primary', 'text-danger'];
                $chartData = app('App\Http\Controllers\ProjectController')->getProjectChart(['workspace_id' => $currantWorkspace->id, 'duration' => 'week']);

                return view('home', compact('currantWorkspace', 'totalProject', 'totalBugs', 'totalTask', 'totalMembers', 'arrProcessLable', 'arrProcessPer', 'arrProcessClass', 'completeTask', 'tasks', 'chartData'));

            }else{
                $totalProject = UserProject::join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->count();

                if ($currantWorkspace->permission == 'Owner') {
                    $totalBugs = UserProject::join("bug_reports", "bug_reports.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->count();
                    $totalTask = UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->count();
                    $completeTask = UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.status', '=', 'done')->count();
                    $tasks = Task::select(['tasks.*','stages.name as status','stages.complete'])->join("user_projects", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->join("stages", "stages.id", "=", "tasks.status")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->orderBy('tasks.id', 'desc')->limit(4)->get();
                } else {
                    $totalBugs = UserProject::join("bug_reports", "bug_reports.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('bug_reports.assign_to', '=', $userObj->id)->count();
                    $totalTask = UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', $userObj->id)->count();
                    $completeTask = UserProject::join("tasks", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', $userObj->id)->where('tasks.status', '=', 'done')->count();
                    $tasks = Task::select(['tasks.*','stages.name as status','stages.complete'])->join("user_projects", "tasks.project_id", "=", "user_projects.project_id")->join("projects", "projects.id", "=", "user_projects.project_id")->join("stages", "stages.id", "=", "tasks.status")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', $userObj->id)->orderBy('tasks.id', 'desc')->limit(4)->get();
                }


                $totalMembers = UserWorkspace::where('workspace_id', '=', $currantWorkspace->id)->count();

                $projectProcess = UserProject::join("projects", "projects.id", "=", "user_projects.project_id")->where("user_id", "=", $userObj->id)->where('projects.workspace', '=', $currantWorkspace->id)->groupBy('projects.status')->selectRaw('count(projects.id) as count, projects.status')->pluck('count', 'projects.status');
                $arrProcessLable = [];
                $arrProcessPer = [];
                $arrProcessLable = [];
                foreach ($projectProcess as $lable => $process) {
                    $arrProcessLable[] = $lable;
                    if($totalProject == 0){
                        $arrProcessPer[] = 0.00;
                    }else{
                        $arrProcessPer[] = round(($process * 100) / $totalProject, 2);
                    }
                }
                $arrProcessClass = ['text-success', 'text-primary', 'text-danger'];

                $chartData = app('App\Http\Controllers\ProjectController')->getProjectChart(['workspace_id' => $currantWorkspace->id, 'duration' => 'week']);

                return view('home', compact('currantWorkspace', 'totalProject', 'totalBugs', 'totalTask', 'totalMembers', 'arrProcessLable', 'arrProcessPer', 'arrProcessClass', 'completeTask', 'tasks', 'chartData'));
            }
        }
        else{
            return view('home', compact('currantWorkspace'));
        }
    }
    public function getOrderChart($arrParam){
        $arrDuration = [];
        if($arrParam['duration']){

            if($arrParam['duration'] == 'week'){
                $previous_week = strtotime("-1 week +1 day");


                for ($i=0;$i<7;$i++){
                    $arrDuration[date('Y-m-d',$previous_week)] = date('D',$previous_week);
                    $previous_week = strtotime(date('Y-m-d',$previous_week). " +1 day");
                }
            }
        }
//        dd($arrDuration);
        $arrTask = [];
        $arrTask['label'] = [];
        $arrTask['data'] = [];
        foreach ($arrDuration as $date => $label){


            $data = Order::select(\DB::raw('count(*) as total'))
                ->whereDate('created_at','=',$date)->first();
            $arrTask['label'][]=$label;
            $arrTask['data'][]=$data->total;
        }
        return $arrTask;
    }
}
