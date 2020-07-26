<?php

namespace App\Http\Controllers;

use App\Mail\SendLoginDetail;
use App\Message;
use App\Notification;
use App\Plan;
use App\Project;
use App\User;
use App\UserProject;
use App\UserWorkspace;
use App\Utility;
use App\Mail\SendWorkspaceInvication;
use App\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Config;
use Pusher\Pusher;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index($slug = '')
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace)
        {
            $users = User::select('users.*', 'user_workspaces.permission', 'user_workspaces.is_active')->join('user_workspaces', 'user_workspaces.user_id', '=', 'users.id');
            $users->where('user_workspaces.workspace_id', '=', $currantWorkspace->id);
            $users = $users->get();
        }
        else
        {
            $users = User::where('type', '!=', 'admin')->get();
        }

        return view('users.index', compact('currantWorkspace', 'users'));
    }

    public function account()
    {
        $user             = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug('');

        return view('users.account', compact('currantWorkspace', 'user'));
    }

    public function edit($slug, $id)
    {
        $user             = User::find($id);
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        return view('users.edit', compact('currantWorkspace', 'user'));
    }

    public function deleteAvatar()
    {
        $objUser         = Auth::user();
        $objUser->avatar = '';
        $objUser->save();

        return redirect()->back()->with('success', 'Avatar deleted successfully');
    }

    public function update($slug = null, $id = null, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($id)
        {
            $objUser = User::find($id);
        }
        else
        {
            $objUser = Auth::user();
        }
        $validation         = [];
        $validation['name'] = 'required';
        if($request->avatar)
        {
            $validation['avatar'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
        }
        $request->validate($validation);

        $post = $request->all();
        if($request->avatar)
        {
            $avatarName = $objUser->id . '_avatar' . time() . '.' . $request->avatar->getClientOriginalExtension();
            $request->avatar->storeAs('avatars', $avatarName);
            $post['avatar'] = $avatarName;
        }

        $objUser->update($post);

        if($request->permission){
            $permission = UserWorkspace::where('user_id','=',$objUser->id)->where('workspace_id','=',$currantWorkspace->id)->first();
            $permission->permission = $request->permission;
            $permission->save();
        }

        return redirect()->back()->with('success', __('User Updated Successfully!'));
    }

    public function destroy($user_id)
    {
        if($user_id != 1)
        {
            $user = User::find($user_id);
            $user->delete();

            return redirect()->back()->with('success', __('User Deleted Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Some Thing Is Wrong!'));
        }
    }

    public function changePlan($user_id){
        $user = Auth::user();
        if($user->type == 'admin'){
            $plans = Plan::get();
            $user = User::find($user_id);
            return view('users.change_plan', compact('plans', 'user'));
        }else{
            return redirect()->back()->with('error', __('Some Thing Is Wrong!'));
        }
    }
    public function updatePlan($user_id,Request $request){
        $user = Auth::user();
        if($user->type == 'admin'){
            $objUser = User::find($user_id);
            $assignPlan = $objUser->assignPlan($request->plan);
            if($assignPlan['is_success'])
            {
                return redirect()->back()->with('success', __('Plan Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __($assignPlan['error']));
            }
        }else{
            return redirect()->back()->with('error', __('Some Thing Is Wrong!'));
        }
    }

    public function updatePassword(Request $request)
    {
        if(Auth::Check())
        {
            $request->validate(
                [
                    'old_password' => 'required',
                    'password' => 'required|same:password',
                    'password_confirmation' => 'required|same:password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;

            if(Hash::check($request_data['old_password'], $current_password))
            {

                $objUser->password = Hash::make($request_data['password']);;
                $objUser->save();

                return redirect()->back()->with('success', __('Password Updated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Please Enter Correct Current Password!'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Some Thing Is Wrong!'));
        }
    }


    public function getUserJson($workspace_id)
    {
        $return  = [];
        $objdata = UserWorkspace::select('user.email')->join('users', 'users.id', '=', 'user_workspaces.user_id')->where('user_workspaces.is_active', '=', 1)->where('users.id', '!=', auth()->id())->get();
        foreach($objdata as $data)
        {
            $return[] = $data->email;
        }

        return response()->json($return);
    }

    public function getProjectUserJson($projectID)
    {
        $project = Project::find($projectID);
        return $project->users->toJSON();
    }
    public function getProjectMilestoneJson($projectID)
    {
        $project = Project::find($projectID);
        return $project->milestones->toJSON();
    }

    public function invite($slug)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        return view('users.invite', compact('currantWorkspace'));
    }

    public function inviteUser($slug, Request $request)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $post             = $request->all();
        $email            = $post['users_list'];

        $registerUsers = User::where('email', $email)->first();
        if(!$registerUsers)
        {
            $objUser = \Auth::user();
            $plan    = Plan::find($objUser->plan);
            if($plan)
            {
                $totalWS = $objUser->countUsers($currantWorkspace->id);
                if($totalWS < $plan->max_users || $plan->max_users == -1)
                {
                    $arrUser                      = [];
                    $arrUser['name']              = __('No Name');
                    $arrUser['email']             = $email;
                    $password                     = Str::random(8);
                    $arrUser['password']          = Hash::make($password);
                    $arrUser['currant_workspace'] = $currantWorkspace->id;
                    $registerUsers                = User::create($arrUser);
                    $assignPlan                   = $registerUsers->assignPlan(1);
                    $registerUsers->password      = $password;
                    if(!$assignPlan['is_success'])
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
                }
                else
                {
                    return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Default plan is deleted.'));
            }

        }

        // assign workspace first
        $is_assigned = false;
        foreach($registerUsers->workspace as $workspace)
        {
            if($workspace->id == $currantWorkspace->id)
            {
                $is_assigned = true;
            }
        }

        if(!$is_assigned)
        {
            UserWorkspace::create(
                [
                    'user_id' => $registerUsers->id,
                    'workspace_id' => $currantWorkspace->id,
                    'permission' => 'Member',
                ]
            );

            try
            {
                Mail::to($registerUsers->email)->send(new SendWorkspaceInvication($registerUsers, $currantWorkspace));
            }
            catch(\Exception $e)
            {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }

        }


        return redirect()->route('users.index', $currantWorkspace->slug)->with('success', __('Users Invited Successfully!') . ((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
    }

    public function removeUser($slug, $id)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $userWorkspace    = UserWorkspace::where('user_id', '=', $id)->where('workspace_id', '=', $currantWorkspace->id)->first();
        if($userWorkspace)
        {
            $user = User::find($id);
            $userProjectCount = $user->countProject($currantWorkspace->id);
            if($userProjectCount == 0){
                $userWorkspace->delete();
            }else{
                return redirect()->route('users.index', $currantWorkspace->slug)->with('warning', __('Please Remove User From Project!'));
            }

            return redirect()->route('users.index', $currantWorkspace->slug)->with('success', __('User Removed Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }
    public function chatIndex($slug = '')
    {
        if(env('CHAT_MODULE') == 'yes') {
            $objUser = Auth::user();
            $currantWorkspace = Utility::getWorkspaceBySlug($slug);
            if ($currantWorkspace) {
                $users = User::select('users.*', 'user_workspaces.permission', 'user_workspaces.is_active')->join('user_workspaces', 'user_workspaces.user_id', '=', 'users.id');
                $users->where('user_workspaces.workspace_id', '=', $currantWorkspace->id)->where('users.id', '!=', $objUser->id);
                $users = $users->get();
            } else {
                $users = User::where('type', '!=', 'admin')->get();
            }

            return view('chats.index', compact('currantWorkspace', 'users'));
        }else{
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }

    public function getMessage($currantWorkspace, $user_id)
    {
        $workspace = Workspace::find($currantWorkspace);
        Utility::getWorkspaceBySlug($workspace->slug);
        $my_id = Auth::id();

        // Make read all unread message
        Message::where(
            [
                'workspace_id' => $currantWorkspace,
                'from' => $user_id,
                'to' => $my_id,
                'is_read' => 0
            ]
        )->update(['is_read' => 1]);

        // Get all message from selected user
        $messages = Message::where(
            function ($query) use ($currantWorkspace, $user_id, $my_id){
                $query->where('workspace_id', $currantWorkspace)->where('from', $user_id)->where('to', $my_id);
            }
        )->oRwhere(
            function ($query) use ($currantWorkspace, $user_id, $my_id){
                $query->where('workspace_id', $currantWorkspace)->where('from', $my_id)->where('to', $user_id);
            }
        )->get();

        return view('chats.message', ['messages' => $messages]);
    }

    public function sendMessage(Request $request)
    {
        $from             = Auth::id();
        $currantWorkspace = Workspace::find($request->workspace_id);
        $to               = $request->receiver_id;
        $message          = $request->message;

        $data               = new Message();
        $data->workspace_id = $currantWorkspace->id;
        $data->from         = $from;
        $data->to           = $to;
        $data->message      = $message;
        $data->is_read      = 0; // message will be unread when sending message
        $data->save();

        // pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true,
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), $options
        );

        $data = [
            'from' => $from,
            'to' => $to,
        ]; // sending from and to user id when pressed enter
        $pusher->trigger($currantWorkspace->slug, 'chat', $data);
        return response()->json($data,200);
    }

    public function notificationSeen($slug){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $user = Auth::user();
        Notification::where('workspace_id','=',$currantWorkspace->id)->where('user_id','=',$user->id)->update(['is_read' => 1]);
        return response()->json(['is_success'=>true],200);
    }
    public function getMessagePopup($slug){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $user = Auth::user();
        $messages = Message::whereIn('id',function($query) use($currantWorkspace,$user){
            $query->select(\DB::raw('MAX(id)'))->from('messages')->where('workspace_id','=',$currantWorkspace->id)->where('to', $user->id)->where('is_read','=',0);
        })->orderBy('id','desc')->get();

        return view('chats.popup',compact('messages','currantWorkspace'));
    }
    public function messageSeen($slug){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $user = Auth::user();
        Message::where('workspace_id','=',$currantWorkspace->id)->where('to','=',$user->id)->update(['is_read' => 1]);
        return response()->json(['is_success'=>true],200);
    }
}
