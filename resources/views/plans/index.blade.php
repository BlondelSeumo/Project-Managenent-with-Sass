@extends('layouts.main')
@section('content')
    <section class="section">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(Auth::user()->type == 'admin')
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h2 class="section-title">{{ __('Plans') }}</h2>
                </div>
                <div class="col-sm-8">
                    <div class="text-sm-right">
                        <button type="button" class="btn btn-primary mt-4" data-ajax-popup="true" data-size="lg" data-title="{{ __('Add Plan') }}" data-url="{{route('plans.create')}}">
                            <i class="mdi mdi-plus"></i> {{ __('Add Plan') }}
                        </button>
                    </div>
                </div>
            </div>
            @if(empty(env('STRIPE_KEY')) || empty(env('STRIPE_SECRET')))
                <div class="alert alert-warning"><i class="dripicons-warning"></i> {{__('Please set stripe api key & secret key for add new plan')}}</div>
            @endif
        @else
            <h2 class="section-title">{{ __('Plans') }}</h2>
        @endif

        <div class="row justify-content-center plans">
            <div class="col-xl-10">
                @if(Auth::user()->type != 'admin')
                    <div class="text-center">
                        <h5 class="mb-2">{{__('Our Plans and Pricing')}}</h5>
                        <p class="text-muted w-50 m-auto">
                            {{__('We have plans and prices that fit your business perfectly. Make your client site a success with our products.')}}
                        </p>
                    </div>
                @endif

                <div class="row mt-5 mb-1 ">
                    @foreach ($plans as $key => $plan)

                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="pricing">

                                @if(Auth::user()->plan == $plan->id)
                                    <div class="pricing-title">
                                        {{__('Current Plan')}}
                                    </div>
                                @endif

                                @if(Auth::user()->type == 'admin')
                                    <div class="dropdown card-widgets float-right mt-2 mr-3">
                                        <a href="#" class="dropdown-toggle arrow-none text-muted" data-toggle="dropdown" aria-expanded="false">
                                            <i class="dripicons-gear"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a href="#" class="dropdown-item" data-ajax-popup="true" data-size="lg" data-title="{{__('Edit Plan')}}" data-url="{{route('plans.edit',$plan->id)}}"><i class="mdi mdi-pencil mr-1"></i>{{__('Edit')}}</a>
                                        </div>
                                    </div>
                                @endif

                                <div class="pricing-padding">
                                    <img @if($plan->image) src="{{asset('/storage/plans/'.$plan->image)}}" @else avatar="{{ $plan->name }}" @endif alt="plan image" class="rounded-circle card-pricing-icon">
                                    <div class="pricing-price">
                                        <div>${{ $plan->price }}</div>
                                        <div>@if($plan->duration!='Unlimited')/@endif {{ __($plan->duration) }}</div>
                                        <h5>{{ $plan->name }}</h5>
                                    </div>
                                    <div class="pricing-details">
                                        <div class="pricing-item">
                                            <div class="pricing-item-icon"><i class="dripicons-checkmark"></i></div>
                                            <div class="pricing-item-label">{{ ($plan->max_workspaces < 0)?__('Unlimited'):$plan->max_workspaces }} {{__('Workspaces')}}</div>
                                        </div>
                                        <div class="pricing-item">
                                            <div class="pricing-item-icon"><i class="dripicons-checkmark"></i></div>
                                            <div class="pricing-item-label">{{ ($plan->max_users<0)?__('Unlimited'):$plan->max_users }} {{__('Users Per Workspace')}}</div>
                                        </div>
                                        <div class="pricing-item">
                                            <div class="pricing-item-icon"><i class="dripicons-checkmark"></i></div>
                                            <div class="pricing-item-label">{{ ($plan->max_clients<0)?__('Unlimited'):$plan->max_clients }} {{__('Clients Per Workspace')}}</div>
                                        </div>
                                        <div class="pricing-item">
                                            <div class="pricing-item-icon"><i class="dripicons-checkmark"></i></div>
                                            <div class="pricing-item-label">{{ ($plan->max_projects<0)?__('Unlimited'):$plan->max_projects }} {{__('Projects Per Workspace')}}</div>
                                        </div>
                                        @if($plan->description)
                                            <p>
                                                {{$plan->description}}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                @if(Auth::user()->type != 'admin')
                                    @if(Auth::user()->plan != $plan->id)
                                        <div class="pricing-cta">
                                            <a href="{{route('stripe',\Illuminate\Support\Facades\Crypt::encrypt($plan->id))}}">{{__('Choose Plan')}} <i class="dripicons-arrow-right"></i></a>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        @if(($key+1)%3 == 0)
                </div>
                <div class="row mt-4 mb-1">
                    @endif
                    @endforeach
                </div>

            </div>
        </div>

    </section>
    <!-- container -->
@endsection
