<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'workspace_id',
        'user_id',
        'type',
        'data',
        'is_read',
    ];

    public function toHtml(){

        $data = json_decode($this->data);
        $link = '#';
        $icon = 'fa fa-bell';
        $icon_color = 'bg-primary';
        $text = '';

        if($this->type == 'task_assign'){
            $project = Project::find($data->project_id);
            if($project){
                $link = route('projects.task.board',[$this->workspace_id,$data->project_id]);
                $text = __('New task assign')." <b>".$data->title."</b> ".__('in project')." <b>".$project->name."</b>";
                $icon = "fa fa-clock-o";
                if($data->priority == 'Low'){
                    $icon_color = 'bg-success';
                }elseif($data->priority == 'High'){
                    $icon_color = 'bg-danger';
                }
            }else{
                return '';
            }
        }
        elseif($this->type == 'project_assign'){
            $link = route('projects.show',[$this->workspace_id,$data->id]);
            $text = __('New project assign')." <b>".$data->name."</b>";
            $icon = "fa fa-suitcase";
        }
        elseif($this->type == 'bug_assign'){
            $project = Project::find($data->project_id);
            if($project){
                $link = route('projects.bug.report',[$this->workspace_id,$data->project_id]);
                $text = __('New bug assign')." <b>".$data->title."</b> ".__('in project')." <b>".$project->name."</b>";
                $icon = "fa fa-bug";
                if($data->priority == 'Low'){
                    $icon_color = 'bg-success';
                }elseif($data->priority == 'High'){
                    $icon_color = 'bg-danger';
                }
            }else{
                return '';
            }
        }

        $date = $this->created_at->diffForHumans();
        $html ='<a href="'.$link.'" class="dropdown-item dropdown-item-unread">
                    <div class="dropdown-item-icon '.$icon_color.' text-white">
                        <i class="'.$icon.'"></i>
                    </div>
                    <div class="dropdown-item-desc">
                        '.$text.'
                        <div class="time text-primary">'.$date.'</div>
                    </div>
                </a>';

        return $html;

    }
}
