<?php
namespace App;

use Auth;
use Jenssegers\Date\Date;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class Utility
{
    public function createSlug($table,$title, $id = 0)
    {
        // Normalize the title
        $slug = Str::slug($title,'-');
        // Get any that could possibly be related.
        // This cuts the queries down by doing it once.
        $allSlugs = $this->getRelatedSlugs($table,$slug, $id);
        // If we haven't used it before then we are all good.
        if (! $allSlugs->contains('slug', $slug)){
            return $slug;
        }
        // Just append numbers like a savage until we find not used.
        for ($i = 1; $i <= 100; $i++) {
            $newSlug = $slug.'-'.$i;
            if (! $allSlugs->contains('slug', $newSlug)) {
                return $newSlug;
            }
        }
        throw new \Exception('Can not create a unique slug');
    }
    protected function getRelatedSlugs($table,$slug, $id = 0)
    {
        return DB::table($table)->select()->where('slug', 'like', $slug.'%')
            ->where('id', '<>', $id)
            ->get();
    }

    public static function getWorkspaceBySlug($slug){

        $objUser = Auth::user();

        if($objUser && $objUser->currant_workspace){
            if($objUser->getGuard() == 'client') {
                $rs = Workspace::select(['workspaces.*'])->join('client_workspaces', 'workspaces.id', '=', 'client_workspaces.workspace_id')->where('workspaces.id', '=', $objUser->currant_workspace)->where('client_id', '=', $objUser->id)->first();
            }else{
                $rs = Workspace::select(['workspaces.*','user_workspaces.permission'])->join('user_workspaces', 'workspaces.id', '=', 'user_workspaces.workspace_id')->where('workspaces.id', '=', $objUser->currant_workspace)->where('user_id', '=', $objUser->id)->first();
            }
            if($rs){
                Utility::setLang($rs);
                return $rs;
            }
        }
        if($objUser && !empty($slug)){
            if($objUser->getGuard() == 'client') {
                $rs = Workspace::select(['workspaces.*'])->join('client_workspaces', 'workspaces.id', '=', 'client_workspaces.workspace_id')->where('slug', '=', $slug)->where('client_id','=',$objUser->id)->first();
            }else{
                $rs = Workspace::select(['workspaces.*','user_workspaces.permission'])->join('user_workspaces', 'workspaces.id', '=', 'user_workspaces.workspace_id')->where('slug', '=', $slug)->where('user_id','=',$objUser->id)->first();
            }

            if($rs){
                Utility::setLang($rs);
                return $rs;
            }
        }
        if($objUser) {
            if($objUser->getGuard() == 'client') {
                $rs = Workspace::select(['workspaces.*'])->join('client_workspaces', 'workspaces.id', '=', 'client_workspaces.workspace_id')->where('client_id', '=', $objUser->id)->orderBy('workspaces.id', 'desc')->limit(1)->first();
            }else{
                $rs = Workspace::select(['workspaces.*','user_workspaces.permission'])->join('user_workspaces', 'workspaces.id', '=', 'user_workspaces.workspace_id')->where('user_id', '=', $objUser->id)->orderBy('workspaces.id', 'desc')->limit(1)->first();
            }
            if ($rs) {
                Utility::setLang($rs);
                return $rs;
            }
        }
        else{
            $rs = Workspace::select(['workspaces.*'])->where('slug','=',$slug)->limit(1)->first();
            if ($rs) {
                Utility::setLang($rs);
                return $rs;
            }
        }
    }
    public static function setLang($Workspace){

        $dir    = base_path().'/resources/lang/'.$Workspace->id."/";
        if(is_dir($dir))
            $lang = $Workspace->id."/".$Workspace->lang;
        else
            $lang = $Workspace->lang;

        Date::setLocale(basename($lang));
        \App::setLocale($lang);

    }

    public static function get_timeago( $ptime )
    {
        $estimate_time = time() - $ptime;

        $ago = true;

        if( $estimate_time < 1 )
        {
            $ago = false;
            $estimate_time = abs($estimate_time);
        }

        $condition = array(
            12 * 30 * 24 * 60 * 60  =>  'year',
            30 * 24 * 60 * 60       =>  'month',
            24 * 60 * 60            =>  'day',
            60 * 60                 =>  'hour',
            60                      =>  'minute',
            1                       =>  'second'
        );

        foreach( $condition as $secs => $str )
        {
            $d = $estimate_time / $secs;

            if( $d >= 1 )
            {
                $r = round( $d );
                $str = $str . ( $r > 1 ? 's' : '' );
                return $r . ' ' . __($str) . ($ago?' '.__('ago'):'');
            }
        }
    }

    public static function formatBytes($size, $precision = 2)
    {
        if ($size > 0) {
            $size = (int) $size;
            $base = log($size) / log(1024);
            $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

            return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
        } else {
            return $size;
        }
    }

    public static function invoiceNumberFormat($number)
    {
        return 'INV' . sprintf("%05d", $number);
    }

    public static function dateFormat($date){
        $lang = \App::getLocale();
        \App::setLocale(basename($lang));
        $date = Date::parse($date)->format('d M Y');
        \App::setLocale($lang);
        return $date;
    }

    public static function sandNotification($type,$currantWorkspace,$user_id,$obj){

        $notification = Notification::create([
            'workspace_id'=>$currantWorkspace->id,
            'user_id'=>$user_id,
            'type'=>$type,
            'data'=>json_encode($obj),
            'is_read'=>0
        ]);

        // Push Notification
        $options = array(
            'cluster' => env('PUSHER_APP_CLUSTER'),
            'useTLS' => true,
        );
        $pusher  = new Pusher(
            env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), $options
        );
        $data = [];
        $data['html']    =  $notification->toHtml();
        $data['user_id'] =  $notification->user_id;
        // sending from and to user id when pressed enter
        $pusher->trigger($currantWorkspace->slug, 'notification', $data);

        // End Push Notification
    }
}
