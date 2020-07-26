<?php
namespace App\Http\Controllers;

use App\Client;
use App\ClientProject;
use App\ClientWorkspace;
use App\Mail\SendClientLoginDetail;
use App\Plan;
use App\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function clientLogout(Request $request){
        \Auth::guard('client')->logout();

        $request->session()->invalidate();

        return redirect()->route('client.login');
    }

    public function index($slug)
    {
        $this->middleware('auth');
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($currantWorkspace->creater->id == \Auth::user()->id) {
            $clients = Client::select(
                [
                    'clients.*',
                    'client_workspaces.is_active',
                ]
            )->join('client_workspaces', 'client_workspaces.client_id', '=', 'clients.id')->where('client_workspaces.workspace_id', '=', $currantWorkspace->id)->get();

            return view('clients.index', compact('currantWorkspace', 'clients'));
        }else{
            return redirect()->route('home');
        }
    }

    public function create($slug)
    {
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        return view('clients.create', compact('currantWorkspace'));
    }

    public function store($slug, Request $request)
    {
        $objUser          = \Auth::user();
        $plan             = Plan::find($objUser->plan);
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);
        if($plan)
        {

            $totalWS = $objUser->countClients($currantWorkspace->id);
            if($totalWS < $plan->max_clients || $plan->max_clients == -1)
            {

                $registerClient = Client::where('email', '=', $request->email)->first();
                if(!$registerClient)
                {
                    $arrUser['name']              = $request->name;
                    $arrUser['email']             = $request->email;
                    $arrUser['password']          = Hash::make($request->password);
                    $registerClient               = Client::create($arrUser);
                    try
                    {
                        $registerClient->password = $request->password;
                        Mail::to($request->email)->send(new SendClientLoginDetail($registerClient));
                    }
                    catch(\Exception $e)
                    {
                        $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
                    }
                }
                $checkClient = ClientWorkspace::where('client_id', '=', $registerClient->id)->where('workspace_id', '=', $currantWorkspace->id)->first();
                if(!$checkClient) {
                    ClientWorkspace::create(
                        [
                            'client_id' => $registerClient->id,
                            'workspace_id' => $currantWorkspace->id,
                        ]
                    );
                }

                return redirect()->route('clients.index', $currantWorkspace->slug)->with('success', __('Client Created Successfully!').((isset($smtp_error)) ? ' <br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
            }
            else
            {
                return redirect()->back()->with('error', __('Your client limit is over, Please upgrade plan.'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Default plan is deleted.'));
        }
    }

    public function edit($slug, $id)
    {
        $client           = Client::find($id);
        $currantWorkspace = Utility::getWorkspaceBySlug($slug);

        return view('clients.edit', compact('client', 'currantWorkspace'));
    }

    public function update($slug, $id, Request $request)
    {
        $client = Client::find($id);
        if($client)
        {
            $currantWorkspace = Utility::getWorkspaceBySlug($slug);
            $client->name     = $request->name;
            if($request->password){
                $client->password = Hash::make($request->password);
            }
            $client->save();
            return redirect()->route('clients.index', $currantWorkspace->slug)->with('success', __('Client Updated Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }

    public function destroy($slug, $id)
    {
        $client = Client::find($id);
        if($client)
        {
            $currantWorkspace = Utility::getWorkspaceBySlug($slug);
            ClientWorkspace::where('client_id', '=', $client->id)->delete();
            ClientProject::where('client_id', '=', $client->id)->delete();
            $client->delete();

            return redirect()->route('clients.index', $currantWorkspace->slug)->with('success', __('Client Deleted Successfully!'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }

    public function updateBilling(Request $request){
        $objUser = \Auth::user();
        $objUser->address = $request->address;
        $objUser->city = $request->city;
        $objUser->state = $request->state;
        $objUser->zipcode = $request->zipcode;
        $objUser->country = $request->country;
        $objUser->telephone = $request->telephone;
        $objUser->save();
        return redirect()->back()->with('success', __('Billing Details Updated Successfully!'));
    }
}
