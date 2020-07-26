@extends('layouts.main')

@section('content')

    <section class="section">
    @if(Auth::user()->type=='admin')
            <h2 class="section-title">{{ __('Dashboard') }}</h2>
            <div class="row">
            	<div class="col-12">
            		@if(empty(env('PUSHER_APP_ID')) || empty(env('PUSHER_APP_KEY')) || empty(env('PUSHER_APP_SECRET')) || empty(env('PUSHER_APP_CLUSTER')))
            			<div class="alert alert-warning"><i class="dripicons-warning"></i> {{ __('Please Add Pusher Detail ') }}<u><a href="{{ route('settings.index') }}">{{ __('here')}}</a></u></div>
            		@endif
            		@if(empty(env('MAIL_DRIVER')) || empty(env('MAIL_HOST')) || empty(env('MAIL_PORT')) || empty(env('MAIL_USERNAME'))  || empty(env('MAIL_PASSWORD'))  || empty(env('MAIL_PASSWORD')))
            			<div class="alert alert-warning"><i class="dripicons-warning"></i> {{ __('Please Add Mail Setting Detail ') }}<u><a href="{{ route('settings.index') }}">{{ __('here')}}</a></u></div>
            		@endif
            	</div>
                <div class="col-12">
                    <div class="widget-inline">
                        <div class="p-0">
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="card widget-flat">
                                        <div class="card-body">
                                            <div class="float-right">
                                                <i class="mdi mdi-account-multiple widget-icon"></i>
                                            </div>
                                            <h5 class="text-muted font-weight-normal mt-0" title="Number of Customers">{{ __('Total Users') }}</h5>
                                            <h3 class="mt-3 mb-3">{{number_format($totalUsers)}}</h3>
                                            <p class="mb-0 text-muted">
                                                <span class="text-primary mr-2">
                                                    <i class="mdi mdi-chess-king"></i> {{number_format($totalPaidUsers)}}</span>
                                                <span class="text-nowrap">{{ __('Paid Users') }}</span>
                                            </p>
                                        </div> <!-- end card-body-->
                                    </div> <!-- end card-->
                                </div>
                                <div class="col-lg-4">
                                    <div class="card widget-flat">
                                        <div class="card-body">
                                            <div class="float-right">
                                                <i class="mdi mdi-credit-card widget-icon"></i>
                                            </div>
                                            <h5 class="text-muted font-weight-normal mt-0" title="Number of Customers">{{ __('Total Orders') }}</h5>
                                            <h3 class="mt-3 mb-3">{{number_format($totalOrders)}}</h3>
                                            <p class="mb-0 text-muted">
                                                <span class="text-primary mr-2">
                                                    <i class="mdi mdi-cash-multiple"></i> ${{number_format($totalOrderAmount)}}</span>
                                                <span class="text-nowrap">{{ __('Total Order Amount') }}</span>
                                            </p>
                                        </div> <!-- end card-body-->
                                    </div> <!-- end card-->
                                </div>
                                <div class="col-lg-4">
                                    <div class="card widget-flat">
                                        <div class="card-body">
                                            <div class="float-right">
                                                <i class="dripicons-trophy widget-icon"></i>
                                            </div>
                                            <h5 class="text-muted font-weight-normal mt-0" title="Number of Customers">{{ __('Total Plan') }}</h5>
                                            <h3 class="mt-3 mb-3">{{number_format($totalPlans)}}</h3>
                                            <p class="mb-0 text-muted">

                                                <span class="text-primary mr-2"><i class="mdi mdi-cards-heart"></i>@if($mostPlans) {{$mostPlans->name}} @else - @endif</span>
                                                <span class="text-nowrap">{{ __('Most purchase plan') }}</span>

                                            </p>
                                        </div> <!-- end card-body-->
                                    </div> <!-- end card-->
                                </div>
                            </div>
                        </div>
                    </div> <!-- end card-box-->
                </div> <!-- end col-->
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card animated">
                        <div class="card-header">
                            <h4>{{ __('Recent Order') }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="mt-3 chartjs-chart" style="height: 320px;">
                                <canvas id="task-area-chart"></canvas>
                            </div>

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

    @elseif($currantWorkspace)



            <h2 class="section-title">{{ __('Projects') }}</h2>

            <div class="row">
                <div class="col-12">
                    <div class="card widget-inline">
                        <div class="card-body p-0">
                            <div class="row no-gutters">
                                <div class="col-sm-6 col-xl-3 animated">
                                    <div class="card shadow-none m-0">
                                        <div class="card-body text-center">
                                            <i class="dripicons-briefcase text-muted" style="font-size: 24px;"></i>
                                            <h3><span>{{$totalProject}}</span></h3>
                                            <p class="text-muted font-15 mb-0">{{ __('Total Projects') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-xl-3 animated">
                                    <div class="card shadow-none m-0 border-left">
                                        <div class="card-body text-center">
                                            <i class="dripicons-checklist text-muted" style="font-size: 24px;"></i>
                                            <h3><span>{{$totalTask}}</span></h3>
                                            <p class="text-muted font-15 mb-0">{{ __('Total Tasks') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-xl-3 animated">
                                    <div class="card shadow-none m-0 border-left">
                                        <div class="card-body text-center">
                                            <i class="dripicons-user-group text-muted" style="font-size: 24px;"></i>
                                            <h3><span>{{$totalMembers}}</span></h3>
                                            <p class="text-muted font-15 mb-0">{{ __('Members') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-6 col-xl-3 animated">
                                    <div class="card shadow-none m-0 border-left">
                                        <div class="card-body text-center">
                                            <i class="mdi mdi-bug-outline text-muted" style="font-size: 24px;"></i>
                                            <h3><span>{{$totalBugs}}</span></h3>
                                            <p class="text-muted font-15 mb-0">{{ __('Bugs') }}</p>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- end row -->
                        </div>
                    </div> <!-- end card-box-->
                </div> <!-- end col-->
            </div>
            <!-- end row-->

            <div class="row">
                <div class="col-12">
                    <div class="card animated">
                        <div class="card-header">
                            <h4>{{ __('Tasks Overview') }}</h4>
                        </div>
                        <div class="card-body">

                            <div class="mt-3 chartjs-chart" style="height: 320px;">
                                <canvas id="task-area-chart"></canvas>
                            </div>

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->


            <div class="row">
                <div class="col-xl-4">
                    <div class="card animated">
                        <div class="card-header">
                            <h4>{{ __('Project Status') }}</h4>
                        </div>
                        <div class="card-body">

                            <div class="my-4 chartjs-chart">
                                <canvas id="project-status-chart"></canvas>
                            </div>

                            <div class="row text-center mt-2 py-2">

                                @foreach($arrProcessPer as $index => $value)

                                <div class="col-4">
                                    <i class="mdi mdi-trending-up {{$arrProcessClass[$index]}} mt-3 h3"></i>
                                    <h3 class="font-weight-normal">
                                        <span>{{$value}}%</span>
                                    </h3>
                                    <p class="text-muted mb-0">{{__($arrProcessLable[$index])}}</p>
                                </div>

                                @endforeach

                            </div>
                            <!-- end row-->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->

                <div class="col-xl-8">
                    <div class="card animated">
                        <div class="card-header">
                            <h4>{{ __('Tasks') }}</h4>
                        </div>
                        <div class="card-body">

                            <p><b>{{$completeTask}}</b> {{ __('Tasks completed out of')}} {{$totalTask}}</p>

                            <div class="table-responsive">
                                <table class="table table-centered table-hover mb-0 animated">
                                    <tbody>
                                    @foreach($tasks as $task)
                                        <tr>
                                            <td>
                                                <div class="font-14 my-1"><a href="{{route('projects.task.board',[$currantWorkspace->slug,$task->project_id])}}" class="text-body">{{$task->title}}</a></div>
                                                <span class="text-muted font-13">{{ __('Due in') }} {{\App\Utility::get_timeago(strtotime($task->due_date))}}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted font-13">{{ __('Status') }}</span> <br/>
                                                @if($task->complete=='1')
                                                    <span class="badge badge-success">{{__($task->status)}}</span>
                                                @else
                                                    <span class="badge badge-primary">{{__($task->status)}}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted font-13">{{ __('Project') }}</span>
                                                <div class="font-14 mt-1 font-weight-normal">{{$task->project->name}}</div>
                                            </td>
                                            @if($currantWorkspace->permission == 'Owner' || Auth::user()->getGuard() == 'client')
                                            <td>
                                                <span class="text-muted font-13">{{ __('Assigned to') }}</span>
                                                <div class="font-14 mt-1 font-weight-normal">{{$task->user->name}}</div>
                                            </td>
                                            @endif

                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

    @endif
    </section>
    <!-- container -->

@endsection



@push('scripts')

    <!-- third party js -->
    <script src="{{ asset('assets/js/vendor/Chart.bundle.min.js') }}"></script>
    <!-- third party js ends -->

<script>
window.chartColors = {
	red: 'rgb(255, 99, 132)',
	orange: 'rgb(255, 159, 64)',
	yellow: 'rgb(255, 205, 86)',
	green: 'rgb(75, 192, 192)',
	blue: 'rgb(54, 162, 235)',
	purple: 'rgb(153, 102, 255)',
	grey: 'rgb(201, 203, 207)'
};

    (function(global) {
	var MONTHS = [
		'January',
		'February',
		'March',
		'April',
		'May',
		'June',
		'July',
		'August',
		'September',
		'October',
		'November',
		'December'
	];

	var COLORS = [
		'#4dc9f6',
		'#f67019',
		'#f53794',
		'#537bc4',
		'#acc236',
		'#166a8f',
		'#00a950',
		'#58595b',
		'#8549ba'
	];

	var Samples = global.Samples || (global.Samples = {});
	var Color = global.Color;

	Samples.utils = {
		// Adapted from http://indiegamr.com/generate-repeatable-random-numbers-in-js/
		srand: function(seed) {
			this._seed = seed;
		},

		rand: function(min, max) {
			var seed = this._seed;
			min = min === undefined ? 0 : min;
			max = max === undefined ? 1 : max;
			this._seed = (seed * 9301 + 49297) % 233280;
			return min + (this._seed / 233280) * (max - min);
		},

		numbers: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 1;
			var from = cfg.from || [];
			var count = cfg.count || 8;
			var decimals = cfg.decimals || 8;
			var continuity = cfg.continuity || 1;
			var dfactor = Math.pow(10, decimals) || 0;
			var data = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = (from[i] || 0) + this.rand(min, max);
				if (this.rand() <= continuity) {
					data.push(Math.round(dfactor * value) / dfactor);
				} else {
					data.push(null);
				}
			}

			return data;
		},

		labels: function(config) {
			var cfg = config || {};
			var min = cfg.min || 0;
			var max = cfg.max || 100;
			var count = cfg.count || 8;
			var step = (max - min) / count;
			var decimals = cfg.decimals || 8;
			var dfactor = Math.pow(10, decimals) || 0;
			var prefix = cfg.prefix || '';
			var values = [];
			var i;

			for (i = min; i < max; i += step) {
				values.push(prefix + Math.round(dfactor * i) / dfactor);
			}

			return values;
		},

		months: function(config) {
			var cfg = config || {};
			var count = cfg.count || 12;
			var section = cfg.section;
			var values = [];
			var i, value;

			for (i = 0; i < count; ++i) {
				value = MONTHS[Math.ceil(i) % 12];
				values.push(value.substring(0, section));
			}

			return values;
		},

		color: function(index) {
			return COLORS[index % COLORS.length];
		},

		transparentize: function(color, opacity) {
			var alpha = opacity === undefined ? 0.5 : 1 - opacity;
			return Color(color).alpha(alpha).rgbString();
		}
	};

	// DEPRECATED
	window.randomScalingFactor = function() {
		return Math.round(Samples.utils.rand(-100, 100));
	};

	// INITIALIZATION

	Samples.utils.srand(Date.now());

}(this));

</script>

    @if(Auth::user()->type=='admin')

        <script>

var config = {
			type: 'line',
			data: {
				labels: {!! json_encode($chartData['label']) !!},
				datasets: [
				    {
                                    label: "{{ __('Orders') }}",
                                    fill: !0,
                                    backgroundColor: "transparent",
                                    borderColor: "#fa5c7c",
                                    data: {!! json_encode($chartData['data']) !!}
                                }
				]
			},
			options: {
			    maintainAspectRatio:false,
			    scales: {
                    xAxes: [{reverse: !0, gridLines: {color: "rgba(0,0,0,0.05)"}}],
                    yAxes: [{
                        ticks: {stepSize: 10, display: !1},
                        min: 10,
                        max: 100,
                        display: !0,
                        borderDash: [5, 5],
                        gridLines: {color: "rgba(0,0,0,0)", fontColor: "#fff"}
                    }]
                },
				responsive: true,
				title: {
					display: false,
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				legend:{
				    display:false
				}
			}
		};

window.onload = function() {
			var ctx = document.getElementById('task-area-chart').getContext('2d');
			window.myLine = new Chart(ctx, config);
		};

        </script>

    @elseif(isset($currantWorkspace) && $currantWorkspace)
    <!-- demo app -->
    <script>

var config = {
			type: 'line',
			data: {
				labels: {!! json_encode($chartData['label']) !!},
				datasets: [
                    @foreach($chartData['stages'] as $id => $name)
                    {
                        label: "{{ __($name)}}",
                        fill: !0,
                        backgroundColor: "transparent",
                        borderColor: "#fa5c7c",
                        data: {!! json_encode($chartData[$id]) !!}
                    },
                    @endforeach
				]
			},
			options: {
			    maintainAspectRatio:false,
			    scales: {
                    xAxes: [{reverse: !0, gridLines: {color: "rgba(0,0,0,0.05)"}}],
                    yAxes: [{
                        ticks: {stepSize: 10, display: !1},
                        min: 10,
                        max: 100,
                        display: !0,
                        borderDash: [5, 5],
                        gridLines: {color: "rgba(0,0,0,0)", fontColor: "#fff"}
                    }]
                },
				responsive: true,
				title: {
					display: false,
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				legend:{
				    display:false
				}
			}
		};


		var config1 = {
			type: 'doughnut',
			data: {
				datasets: [
				    {
                        data: {!! json_encode($arrProcessPer) !!},
                        backgroundColor: ["#0acf97", "#727cf5", "#fa5c7c"],
                        borderColor: "transparent",
                        borderWidth: "3"
				    }
                ],
				labels: {!! json_encode($arrProcessPer) !!}
			},
			options: {
				responsive: true,
				legend: {
					display: false,
				},
				title: {
					display: false,
					text: 'Chart.js Doughnut Chart'
				},
				animation: {
					animateScale: true,
					animateRotate: true
				}
			}
		};


		 window.onload = function() {
			var ctx = document.getElementById('task-area-chart').getContext('2d');
			window.myLine = new Chart(ctx, config);
			var ctx1 = document.getElementById('project-status-chart').getContext('2d');
			window.myDoughnut = new Chart(ctx1, config1);
		};

    </script>
    <!-- end demo js-->
    @endif
@endpush

