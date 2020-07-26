<?php

namespace App\Http\Controllers;


use App\BugReport;
use App\BugStage;
use App\Plan;
use App\Stage;
use App\Task;
use App\Tax;
use App\Utility;
use Auth;
use App\UserProject;
use App\Project;
use App\UserWorkspace;
use App\Workspace;
use Illuminate\Http\Request;
use Illuminate\Filesystem\Filesystem;

class WorkspaceController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $objUser = Auth::user();
        $plan = Plan::find($objUser->plan   );
        if($plan) {
            $totalWS = $objUser->countWorkspace();
            if($totalWS < $plan->max_workspaces || $plan->max_workspaces == -1) {

                $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                ]);

                $objWorkspace = Workspace::create(['created_by' => $objUser->id, 'name' => $request->name]);

                UserWorkspace::create(['user_id' => $objUser->id, 'workspace_id' => $objWorkspace->id, 'permission' => 'Owner']);

                $objUser->currant_workspace = $objWorkspace->id;
                $objUser->update();

                return redirect()->route('home', $objWorkspace->slug)->with('success', __('Workspace Created Successfully!'));
            }else{
                return redirect()->back()->with('error',__('Your workspace limit is over, Please upgrade plan.'));
            }
        }else{
            return redirect()->back()->with('error',__('Default plan is deleted.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Int  $workspaceID
     * @return \Illuminate\Http\Response
     */
    public function destroy($workspaceID)
    {
        $objUser = Auth::user();
        $workspace = Workspace::find($workspaceID);
        if($workspace->created_by == $objUser->id) {
            UserWorkspace::where('workspace_id', '=', $workspaceID)->delete();
            Stage::where('workspace_id', '=', $workspaceID)->delete();
            $workspace->delete();
            return redirect()->route('home')->with('success',__('Workspace Deleted Successfully!'));
        }
        else{
            return redirect()->route('home')->with('error',__('You can\'t delete Workspace!'));
        }
    }

    /**
     * Leave the specified resource from storage.
     *
     * @param  Int  $workspaceID
     * @return \Illuminate\Http\Response
     */
    public function leave($workspaceID)
    {
        $objUser = Auth::user();

        $userProjects = Project::where('workspace', '=', $workspaceID)->get();
        foreach ($userProjects as $userProject){
            UserProject::where('project_id','=',$userProject->id)->where('user_id', '=', $objUser->id)->delete();
        }
        UserWorkspace::where('workspace_id', '=', $workspaceID)->where('user_id', '=', $objUser->id)->delete();
        return redirect()->route('home')->with('success',__('Workspace Leave Successfully!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Int  $workspaceID
     * @return \Illuminate\Http\Response
     */
    public function changeCurrantWorkspace($workspaceID)
    {
        $objWorkspace = Workspace::find($workspaceID);
        if($objWorkspace->is_active) {
            $currantWorkspace = Utility::getWorkspaceBySlug($objWorkspace->slug);
            $objUser = Auth::user();
            $objUser->currant_workspace = $workspaceID;
            $objUser->update();
            return redirect()->back()->with('success', __('Workspace Change Successfully!'));
        }else{
            return redirect()->back()->with('error', __('Workspace is locked'));
        }
    }
    public function changeLangWorkspace($workspaceID,$lang)
    {
        $workspace = Workspace::find($workspaceID);
        $workspace->lang = $lang;
        $workspace->save();
        return redirect()->back()->with('success',__('Workspace Language Change Successfully!'));
    }

    public function langWorkspace($slug,$currantLang){

        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        $dir    = base_path().'/resources/lang/'.$currantWorkspace->id."/".$currantLang;
        if(!is_dir($dir)) {
			$dir = base_path() . '/resources/lang/'.$currantLang;
			if(!is_dir($dir)) {
            	$dir = base_path() . '/resources/lang/en';
			}
        }
        $arrLabel = json_decode(file_get_contents($dir.'.json'));

        $arrFiles = array_diff(scandir($dir), array('..', '.'));
        $arrMessage = [];
        foreach ($arrFiles as $file){
            $fileName =  basename($file,".php");
            $fileData = $myArray = include $dir."/".$file;
            if(is_array($fileData))
                $arrMessage[$fileName] = $fileData ;
        }
        return view('lang.index',compact('currantWorkspace','currantLang','arrLabel','arrMessage'));
    }

    public function storeLangDataWorkspace($slug,$currantLang,Request $request){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
		$Filesystem = new Filesystem();
        $dir = base_path().'/resources/lang/'.$currantWorkspace->id;
        if(!is_dir($dir)) {
            mkdir($dir);
            chmod($dir,0777);
        }
        $jsonFile = $dir."/".$currantLang.".json";

        file_put_contents($jsonFile,json_encode($request->label));

        $langFolder = $dir."/".$currantLang;
        if(!is_dir($langFolder)) {
            mkdir($langFolder);
            chmod($langFolder,0777);

			$dirN = base_path().'/resources/lang/';
			$arrFiles = ['da','de','en','es','fr','it','nl'];
		 	foreach ($arrFiles as $file){
				echo $dirN.$file."  -- ".$dirN.$currantWorkspace->id."/".$file."<br>";

				if(is_dir($dirN."/".$file)){
					$Filesystem->copyDirectory($dirN.$file,$dirN.$currantWorkspace->id."/".$file);
					\File::copy($dirN.$file.".json",$dirN.$currantWorkspace->id."/".$file.".json");
				}
			}

        }

        foreach ($request->message as $fileName => $fileData){
            $content = "<?php return [";
            $content .= $this->buildArray($fileData);
            $content .= "];";
            file_put_contents($langFolder."/".$fileName.'.php',$content);
        }

        return redirect()->route('lang_workspace',[$currantWorkspace->slug,$currantLang])->with('success',__('Language Save Successfully!'));
    }
    public function buildArray($fileData)
    {
        $content = "";
        foreach ($fileData as $lable => $data)
        {
            if(is_array($data)){
                $content .= "'$lable'=>[".$this->buildArray($data)."],";
            }
            else{
                $content .= "'$lable'=>'".addslashes($data)."',";
            }
        }
        return $content;
    }
    public function createLangWorkspace($slug){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        return view('lang.create',compact('currantWorkspace'));
    }
    public function storeLangWorkspace($slug,Request $request){
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        $Filesystem = new Filesystem();
        $langCode = strtolower($request->code);

        $langDir = base_path().'/resources/lang/';
        $dir    = $langDir.$currantWorkspace->id;
        if(!is_dir($dir)) {
            mkdir($dir);
            chmod($dir,0777);

			$dirN = base_path().'/resources/lang/';
			$arrFiles = ['da','de','en','es','fr','it','nl'];
		 	foreach ($arrFiles as $file){
				if(is_dir($dirN."/".$file)){
					$Filesystem->copyDirectory($dirN.$file,$dirN.$currantWorkspace->id."/".$file);
					\File::copy($dirN.$file.".json",$dirN.$currantWorkspace->id."/".$file.".json");
				}
			}
        }

        if(!file_exists($dir.'/en.json')){
            \File::copy($langDir.'en.json',$dir.'/en.json');
            if(!is_dir($dir."/en")) {
                mkdir($dir."/en");
                chmod($dir."/en",0777);
            }
            $Filesystem->copyDirectory($langDir."en", $dir."/en/");
        }

        $dir    = $dir.'/'.$langCode;
        $jsonFile = $dir.".json";
        \File::copy($langDir.'en.json',$jsonFile);

        if(!is_dir($dir)) {
            mkdir($dir);
            chmod($dir,0777);
        }

        $Filesystem->copyDirectory($langDir."en", $dir."/");

        return redirect()->route('lang_workspace',[$currantWorkspace->slug,$langCode])->with('success',__('Language Created Successfully!'));
    }

    public function rename($workspaceID)
    {
        $objUser   = Auth::user();
        $workspace = Workspace::find($workspaceID);
        $currantWorkspace = Utility::getWorkspaceBySlug($workspace->slug);
        if($workspace->created_by == $objUser->id)
        {
            return view('users.rename_workspace', compact('workspace'));
        }
        else
        {
            return redirect()->route('home')->with('error', __('You can\'t rename Workspace!'));
        }

    }

    public function update(Request $request, $id)
    {
        $objUser   = Auth::user();
        $workspace = Workspace::find($id);

        if($workspace->created_by == $objUser->id)
        {
            $workspace->name = $request->name;
            $workspace->save();

            return redirect()->route('home')->with('success', __('Rename Successfully.!'));
        }
        else
        {
            return redirect()->route('home')->with('error', __('You can\'t rename Workspace!'));
        }
    }

    public function settings($slug){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            $taxes = Tax::where('workspace_id','=',$currantWorkspace->id)->get();
            $stages = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->get();
            $bugStages = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->get();
            $colors=['003580','666666','f2f6fa','f50102','f9b034','fbdd03','c1d82f','37a4e4','8a7966','6a737b','050f2c','0e3666','3baeff','3368e6','b84592','f64f81','f66c5f','fac168','46de98','40c7d0','be0028','2f9f45','371676','52325d','511378','0f3866','48c0b6','297cc0','ffffff','000'];
            return view('users.setting', compact('currantWorkspace','taxes','stages','bugStages','colors'));
        }else{
            return redirect()->route('home')->with('error', __("You can't access workspace settings!"));
        }
    }
    public function settingsStore($slug,Request $request){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            if($request->currency) {
                if($request->logo)
                {
                    $request->validate(['logo' => 'required|image|mimes:png|max:1024']);
                    $logoName = 'logo_'.$currantWorkspace->id.'.png';
                    $request->logo->storeAs('logo', $logoName);
                    $currantWorkspace->logo = $logoName;
                }
                $currantWorkspace->currency = $request->currency;
                $currantWorkspace->currency_code = $request->currency_code;
                $currantWorkspace->name = $request->name;
            }
            elseif($request->invoice_template) {
                $currantWorkspace->invoice_template = $request->invoice_template;
                $currantWorkspace->invoice_color = $request->invoice_color;
            }
            elseif($request->stripe_key) {
                $currantWorkspace->stripe_key = $request->stripe_key;
                $currantWorkspace->stripe_secret = $request->stripe_secret;
            }
            else{
                $currantWorkspace->company = $request->company;
                $currantWorkspace->address = $request->address;
                $currantWorkspace->city = $request->city;
                $currantWorkspace->state = $request->state;
                $currantWorkspace->zipcode = $request->zipcode;
                $currantWorkspace->country = $request->country;
                $currantWorkspace->telephone = $request->telephone;
            }
            $currantWorkspace->save();
            return redirect()->back()->with('success', __('Settings Save Successfully.!'));
        }else{
            return redirect()->route('home')->with('error', __("You can't access workspace settings!"));
        }
    }

    public function create_tax($slug){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            return view('users.create_tax', compact('currantWorkspace'));
        }
    }
    public function edit_tax($slug,$id){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            $tax = Tax::find($id);
            return view('users.edit_tax', compact('currantWorkspace','tax'));
        }
    }

    public function store_tax($slug,Request $request){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'rate' => ['required'],
            ]);
            $tax = new Tax();
            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->workspace_id = $currantWorkspace->id;
            $tax->save();
            return redirect()->back()->with('success', __('Tax Save Successfully.!'));
        }else{
            return redirect()->back()->with('error',__('Permission denied.'));
        }
    }
    public function update_tax($slug,Request $request,$id){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'rate' => ['required'],
            ]);
            $tax = Tax::find($id);
            $tax->name = $request->name;
            $tax->rate = $request->rate;
            $tax->save();
            return redirect()->back()->with('success', __('Tax Save Successfully.!'));
        }else{
            return redirect()->back()->with('error',__('Permission denied.'));
        }
    }
    public function destroy_tax($slug,$id){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){
            $tax = Tax::find($id);
            $tax->delete();
            return redirect()->back()->with('success', __('Tax Delete Successfully.!'));
        }else{
            return redirect()->back()->with('error',__('Permission denied.'));
        }
    }
    public function store_stages($slug,Request $request){

        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){

            $rules = [
                'stages' => 'required|present|array',
            ];
            $attributes = [];
            if($request->stages) {

                foreach ($request->stages as $key => $val) {
                    $rules['stages.' . $key . '.name'] = 'required|max:255';
                    $attributes['stages.' . $key . '.name'] = __('Stage Name');
                }
            }
            $validator = \Validator::make($request->all(), $rules,[],$attributes);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $arrStages = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->pluck('name','id')->all();
            $order = 0;
            foreach ($request->stages as $key => $stage){

                $obj = null;
                if($stage['id']){
                    $obj = Stage::find($stage['id']);
                    unset($arrStages[$obj->id]);
                }
                else{
                    $obj = new Stage();
                    $obj->workspace_id = $currantWorkspace->id;
                }
                $obj->name = $stage['name'];
                $obj->order = $order++;
                $obj->complete = 0;
                $obj->save();
            }

            $taskExist=[];
            if($arrStages){
                foreach ($arrStages as $id => $name){
                    $count = Task::where('status','=',$id)->count();
                    if($count!=0){
                        $taskExist[]=$name;
                    }else {
                        Stage::find($id)->delete();
                    }
                }
            }

            $lastStage = Stage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order','desc')->first();
            if($lastStage){
                $lastStage->complete = 1;
                $lastStage->save();
            }

            if(empty($taskExist)) {
                return redirect()->back()->with('success', __('Stage Save Successfully.!'));
            }else{
                return redirect()->back()->with('error', __('Please remove tasks from stage: '.implode(', ',$taskExist)));
            }

        }else{
            return redirect()->back()->with('error',__('Permission denied.'));
        }
    }
    public function store_bug_stages($slug,Request $request){
        $objUser   = Auth::user();
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->created_by == $objUser->id){

            $rules = [
                'stages' => 'required|present|array',
            ];
            $attributes = [];
            if($request->stages) {

                foreach ($request->stages as $key => $val) {
                    $rules['stages.' . $key . '.name'] = 'required|max:255';
                    $attributes['stages.' . $key . '.name'] = __('Stage Name');
                }
            }
            $validator = \Validator::make($request->all(), $rules,[],$attributes);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();
                return redirect()->back()->with('error', $messages->first());
            }

            $arrStages = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order')->pluck('name','id')->all();
            $order = 0;
            foreach ($request->stages as $key => $stage){

                $obj = null;
                if($stage['id']){
                    $obj = BugStage::find($stage['id']);
                    unset($arrStages[$obj->id]);
                }
                else{
                    $obj = new BugStage();
                    $obj->workspace_id = $currantWorkspace->id;
                }
                $obj->name = $stage['name'];
                $obj->order = $order++;
                $obj->complete = 0;
                $obj->save();
            }

            $taskExist=[];
            if($arrStages){
                foreach ($arrStages as $id => $name){
                    $count = BugReport::where('status','=',$id)->count();
                    if($count!=0){
                        $taskExist[]=$name;
                    }else {
                        BugStage::find($id)->delete();
                    }
                }
            }

            $lastStage = BugStage::where('workspace_id','=',$currantWorkspace->id)->orderBy('order','desc')->first();
            if($lastStage){
                $lastStage->complete = 1;
                $lastStage->save();
            }

            if(empty($taskExist)) {
                return redirect()->back()->with('success', __('Stage Save Successfully.!'));
            }else{
                return redirect()->back()->with('error', __('Please remove bugs from stage: '.implode(', ',$taskExist)));
            }


        }else{
            return redirect()->back()->with('error',__('Permission denied.'));
        }
    }
}
