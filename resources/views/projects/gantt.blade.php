@extends('layouts.main')

@section('content')

    @auth('client')
        <?php
        $permisions = Auth::user()->getPermission($project->id);
        ?>
    @endauth

    <section class="section">

        <div class="row mb-2">
            <div class="col-sm-4">
                <h2 class="section-title">
                    {{ __('Gantt Chart')}}
                </h2>
            </div>
            <div class="col-sm-8">
                <div class="text-sm-right">
                    <div class="mt-4">
                        <a href="{{route('projects.show',[$currantWorkspace->slug,$project->id])}}" class="btn btn-primary ml-3"><i class="mdi mdi-arrow-left"></i> {{ __('Back')}}</a>
                    </div>
                </div>
            </div>
        </div>

        @if($project && $currantWorkspace)
            <div class="row">
                <div class="col-12">
                    <div class="btn-group mb-3 mx-auto" id="change_view" role="group">
                        <a href="{{route('projects.gantt',[$currantWorkspace->slug,$project->id,'Quarter Day'])}}" class="btn btn-primary @if($duration == 'Quarter Day')active @endif" data-value="Quarter Day">{{__('Quarter Day')}}</a>
                        <a href="{{route('projects.gantt',[$currantWorkspace->slug,$project->id,'Half Day'])}}" class="btn btn-primary @if($duration == 'Half Day')active @endif" data-value="Half Day">{{__('Half Day')}}</a>
                        <a href="{{route('projects.gantt',[$currantWorkspace->slug,$project->id,'Day'])}}" class="btn btn-primary @if($duration == 'Day')active @endif" data-value="Day">{{__('Day')}}</a>
                        <a href="{{route('projects.gantt',[$currantWorkspace->slug,$project->id,'Week'])}}" class="btn btn-primary @if($duration == 'Week')active @endif" data-value="Week">{{__('Week')}}</a>
                        <a href="{{route('projects.gantt',[$currantWorkspace->slug,$project->id,'Month'])}}" class="btn btn-primary @if($duration == 'Month')active @endif" data-value="Month">{{__('Month')}}</a>
                    </div>
                    <div class="gantt-target"></div>
                </div>
            </div>
        @else
            <div class="container mt-5">
                <div class="page-error">
                    <div class="page-inner">
                        <h1>404</h1>
                        <div class="page-description">
                            {{ __('Page Not Found') }}
                        </div>
                        <div class="page-search">
                            <p class="text-muted mt-3">{{ __('It\'s looking like you may have taken a wrong turn. Don\'t worry... it happens to the best of us. Here\'s a little tip that might help you get back on track.')}}</p>
                            <div class="mt-3">
                                <a class="btn btn-info mt-3" href="{{route('home')}}"><i class="mdi mdi-reply"></i> {{ __('Return Home')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


    </section>

@endsection
@if($project && $currantWorkspace)
    @push('style')
        <link rel="stylesheet" href="{{asset('assets/css/frappe-gantt.css')}}" />
    @endpush
    @push('scripts')
        @php
            $currantLang = basename(App::getLocale());
        @endphp
        <script>
            const month_names = {
                "{{$currantLang}}": [
                    '{{__('January')}}',
                    '{{__('February')}}',
                    '{{__('March')}}',
                    '{{__('April')}}',
                    '{{__('May')}}',
                    '{{__('June')}}',
                    '{{__('July')}}',
                    '{{__('August')}}',
                    '{{__('September')}}',
                    '{{__('October')}}',
                    '{{__('November')}}',
                    '{{__('December')}}'
                ],
                "en": [
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
                ],
            };
        </script>
        <script src="{{asset('assets/js/frappe-gantt.js')}}"></script>
        <script>
            var tasks = JSON.parse('{!! json_encode($tasks) !!}');
            var gantt_chart = new Gantt(".gantt-target", tasks, {
                custom_popup_html: function(task) {
                    var status_class = 'success';
                    if(task.custom_class == 'medium'){
                        status_class = 'info'
                    }else if(task.custom_class == 'high'){
                        status_class = 'danger'
                    }
                    return `
                            <div class="details-container">
                                <div class="title">${task.name} <span class="badge badge-${status_class} float-right">${task.extra.priority}</span></div>
                                <div class="subtitle">
                                    <b>${task.extra.comments}</b> {{ __('Comments')}} <br>
                                    <b>{{ __('Duration')}}</b> ${task.extra.duration}
                                </div>
                            </div>
                          `;
                },
                on_click: function (task) {
                    //console.log(task);
                },
                on_date_change: function(task, start, end) {
                    task_id = task.id;
                    start = moment(start);
                    end = moment(end);
                    $.ajax({
                        url:"@auth('client'){{route('client.projects.gantt.post',[$currantWorkspace->slug,$project->id])}}@else{{route('projects.gantt.post',[$currantWorkspace->slug,$project->id])}}@endif",
                        data:{
                            start:start.format('YYYY-MM-DD HH:mm:ss'),
                            end:end.format('YYYY-MM-DD HH:mm:ss'),
                            task_id:task_id,
                            _token:$('meta[name="csrf-token"]').attr('content')
                        },
                        type:'POST',
                        success:function (data) {

                        },
                        error:function (data) {
                            toastr('Error', '{{ __("Some Thing Is Wrong!")}}', 'error');
                        }
                    });
                },
                view_mode: '{{$duration}}',
                language: '{{$currantLang}}'
            });
        </script>
    @endpush
@endif
