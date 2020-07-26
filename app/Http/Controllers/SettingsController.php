<?php

namespace App\Http\Controllers;

use App\Mail\EmailTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function index()
    {
        $user = \Auth::user();
        if($user->type == 'admin')
        {
            return view('setting');
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }

    public function store(Request $request)
    {
        $user = \Auth::user();
        if($user->type == 'admin')
        {
            if($request->logo)
            {
                $request->validate(['logo' => 'required|image|mimes:png|max:1024']);
                $logoName = 'logo.png';
                $request->logo->storeAs('logo', $logoName);
            }
            if($request->full_logo)
            {
                $request->validate(['full_logo' => 'required|image|mimes:png|max:1024']);
                $logoName = 'logo-full.png';
                $request->full_logo->storeAs('logo', $logoName);
            }

            $rules = [
                'mail_driver' => 'required|string|max:50',
                'mail_host' => 'required|string|max:50',
                'mail_port' => 'required|string|max:50',
                'mail_username' => 'required|string|max:50',
                'mail_password' => 'required|string|max:50',
                'mail_encryption' => 'required|string|max:50',
                'stripe_key' => 'required|string|max:50',
                'stripe_secret' => 'required|string|max:50',
            ];

            if($request->enable_chat =='yes'){
                $rules['pusher_app_id'] = 'required|string|max:50';
                $rules['pusher_app_key'] = 'required|string|max:50';
                $rules['pusher_app_secret'] = 'required|string|max:50';
                $rules['pusher_app_cluster'] = 'required|string|max:50';
            }

            $request->validate($rules);
            $arrEnv = [
                'MAIL_DRIVER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'STRIPE_KEY' => $request->stripe_key,
                'STRIPE_SECRET' => $request->stripe_secret,
                'CHAT_MODULE'=> $request->enable_chat,
                'PUSHER_APP_ID'=> $request->pusher_app_id,
                'PUSHER_APP_KEY'=> $request->pusher_app_key,
                'PUSHER_APP_SECRET'=> $request->pusher_app_secret,
                'PUSHER_APP_CLUSTER'=> $request->pusher_app_cluster,
            ];

            if($this->setEnvironmentValue($arrEnv)){
                return redirect()->back()->with('success', __('Setting updated successfully'));
            }else{
                return redirect()->back()->with('error', __('Something is wrong'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }
    public function setEnvironmentValue($values)
    {
        $envFile = app()->environmentFilePath();
        $str     = file_get_contents($envFile);
        if(count($values) > 0)
        {
            foreach($values as $envKey => $envValue)
            {
                //                $str               .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition       = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine           = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);
                // If key does not exist, add it
                if(!$keyPosition || !$endOfLinePosition || !$oldLine)
                {
                    $str .= "{$envKey}='{$envValue}'\n";
                }
                else
                {
                    $str = str_replace($oldLine, "{$envKey}='{$envValue}'", $str);
                }
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        if(!file_put_contents($envFile, $str))
        {
            return false;
        }
        return true;
    }

    public function testEmail(Request $request)
    {
        $user = \Auth::user();

        if($user->type == 'admin')
        {
            $data=[];
            $data['mail_driver'] = $request->mail_driver;
            $data['mail_host'] = $request->mail_host;
            $data['mail_port'] = $request->mail_port;
            $data['mail_username'] = $request->mail_username;
            $data['mail_password'] = $request->mail_password;
            $data['mail_encryption'] = $request->mail_encryption;
            return view('users.test_email',compact('data'));
        }
        else
        {
            return response()->json(['error' => __('Permission Denied.')], 401);
        }
    }

    public function testEmailSend(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'mail_driver' => 'required',
            'mail_host' => 'required',
            'mail_port' => 'required',
            'mail_username' => 'required',
            'mail_password' => 'required',
        ]);
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        try
        {
            config([
                'mail.driver' => $request->mail_driver,
                'mail.host' => $request->mail_host,
                'mail.port' => $request->mail_port,
                'mail.encryption' => $request->mail_encryption,
                'mail.username' => $request->mail_username,
                'mail.password' => $request->mail_password,
                'mail.from.address' => $request->mail_username,
                'mail.from.name' => config('name'),
            ]);
            Mail::to($request->email)->send(new EmailTest());
        }
        catch(\Exception $e)
        {
            return response()->json(['is_success'=>false,'message'=>$e->getMessage()]);
//            return redirect()->back()->with('error', 'Something is Wrong.');
        }
        return response()->json(['is_success'=>true,'message'=>__('Email send Successfully')]);
    }

}
