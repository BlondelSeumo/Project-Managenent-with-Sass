<?php

namespace App\Http\Controllers;

use App\Plan;
use App\Utility;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currantWorkspace = Utility::getWorkspaceBySlug('');
        $plans            = Plan::get();

        if(\Auth::user()->type == 'admin' || $currantWorkspace->creater->id == \Auth::user()->id)
        {
            return view('plans.index', compact('plans', 'currantWorkspace'));
        }
        else
        {
            return redirect()->route('home');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $plan = new Plan();

        return view('plans.create', compact('plan'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(empty(env('STRIPE_KEY')) || empty(env('STRIPE_SECRET')))
        {
            return redirect()->back()->with('error', __('Please set stripe api key & secret key for add new plan'));
        }
        else
        {
            $validation                   = [];
            $validation['name']           = 'required|unique:plans';
            $validation['price']          = 'required|numeric|min:0';
            $validation['duration']       = 'required';
            $validation['max_workspaces'] = 'required|numeric';
            $validation['max_users']      = 'required|numeric';
            $validation['max_clients']    = 'required|numeric';
            $validation['max_projects']   = 'required|numeric';
            if($request->image)
            {
                $validation['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
            }
            $request->validate($validation);
            $post = $request->all();
            if($request->image)
            {
                $avatarName = 'plan_' . time() . '.' . $request->image->getClientOriginalExtension();
                $request->image->storeAs('plans', $avatarName);
                $post['image'] = $avatarName;
            }
            if(Plan::create($post))
            {
                return redirect()->back()->with('success', __('Plan created Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong'));
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Plan $plan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($planID)
    {
        $plan = Plan::find($planID);

        return view('plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function update($planID, Request $request)
    {
        if(empty(env('STRIPE_KEY')) || empty(env('STRIPE_SECRET')))
        {
            return redirect()->back()->with('error', __('Please set stripe api key & secret key for add new plan'));
        }
        else
        {
            $plan = Plan::find($planID);
            if($plan)
            {
                $validation                   = [];
                $validation['name']           = 'required|unique:plans,name,' . $planID;
                $validation['price']          = 'required|numeric|min:0';
                $validation['duration']       = 'required';
                $validation['max_workspaces'] = 'required|numeric';
                $validation['max_users']      = 'required|numeric';
                $validation['max_clients']    = 'required|numeric';
                $validation['max_projects']   = 'required|numeric';
                if($request->image)
                {
                    $validation['image'] = 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048';
                }
                $request->validate($validation);

                $post = $request->all();
                if($request->image)
                {
                    $avatarName = 'plan_' . time() . '.' . $request->image->getClientOriginalExtension();
                    $request->image->storeAs('plans', $avatarName);
                    $post['image'] = $avatarName;
                }
                if($plan->update($post))
                {
                    return redirect()->back()->with('success', __('Plan updated Successfully!'));
                }
                else
                {
                    return redirect()->back()->with('error', __('Something is wrong'));
                }
            }
            else
            {
                return redirect()->back()->with('error', __('Plan not found'));
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Plan $plan
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($planID)
    {

    }

    public function userPlan(Request $request)
    {

        $objUser = \Auth::user();
        $planID  = \Illuminate\Support\Facades\Crypt::decrypt($request->code);
        $plan    = Plan::find($planID);
        if($plan)
        {
            if($plan->price <= 0)
            {
                $objUser->assignPlan($plan->id);

                return redirect()->route('plans.index')->with('success', __('Plan activated Successfully!'));
            }
            else
            {
                return redirect()->back()->with('error', __('Something is wrong'));
            }
        }
        else
        {
            return redirect()->back()->with('error', __('Plan not found'));
        }
    }
}
