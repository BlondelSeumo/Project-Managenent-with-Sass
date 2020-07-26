<?php

namespace App\Http\Controllers;

use App\Task;
use App\Utility;
use Auth;

class CalenderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug)
    {
        $objUser = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($objUser->getGuard() == 'client'){
            $tasks            = Task::select('tasks.*')->join('projects', 'projects.id', '=', 'tasks.project_id')->join('client_projects', 'projects.id', '=', 'client_projects.project_id')->where('client_projects.client_id', '=', $objUser->id)->where('client_projects.permission','LIKE','%show task%')->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }elseif($currantWorkspace->permission == 'Owner'){
            $tasks            = Task::select('tasks.*')->join('projects', 'projects.id', '=', 'tasks.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->get();
        }else{
            $tasks            = Task::select('tasks.*')->join('projects', 'projects.id', '=', 'tasks.project_id')->where('projects.workspace', '=', $currantWorkspace->id)->where('tasks.assign_to', '=', Auth::user()->id)->get();
        }

        $arrayJson        = [];
        foreach($tasks as $task)
        {
            $arrayJson[] = [
                "title" => $task->title,
                "start" => $task->start_date,
                "end" => $task->due_date,
                "url" =>(($objUser->getGuard() != 'client')?route(
                    'tasks.show', [
                        $currantWorkspace->slug,
                        $task->project_id,
                        $task->id,
                    ]
                ):''),
                "task_id" => $task->id,
                "task_url" => (($objUser->getGuard() != 'client')?route(
                    'tasks.drag.event', [
                        $currantWorkspace->slug,
                        $task->project_id,
                        $task->id,
                    ]
                ):''),
                "className" => (($task->priority == 'Medium') ? 'bg-warning border-warning' : (($task->priority == 'High') ? 'bg-danger border-danger' : '')),
                "allDay" => true,
            ];
        }

        return view('calendar.index', compact('currantWorkspace', 'arrayJson'));
    }
}
