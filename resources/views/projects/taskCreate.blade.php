
@if($project && $currantWorkspace)

    <form class="pl-3 pr-3" method="post" action="@auth('web'){{ route('tasks.store',[$currantWorkspace->slug,$project->id]) }}@elseauth{{ route('client.tasks.store',[$currantWorkspace->slug,$project->id]) }}@endauth">
        @csrf

        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label>{{ __('Project')}}</label>
                    <select class="form-control form-control-light" name="project_id" required>
                        @foreach($projects as $p)
                            <option value="{{$p->id}}" @if($p->id == $project->id) selected @endif>{{$p->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="task-milestone">{{ __('Milestone')}}</label>
                    <select class="form-control form-control-light" name="milestone_id" id="task-milestone">
                        <option value="">{{__('Select Milestone')}}</option>
                        @foreach($project->milestones as $milestone)
                            <option value="{{$milestone->id}}">{{$milestone->title}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="task-title">{{ __('Title')}}</label>
                    <input type="text" class="form-control form-control-light" id="task-title"
                           placeholder="{{ __('Enter Title')}}" name="title" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="task-priority">{{ __('Priority')}}</label>
                    <select class="form-control form-control-light" name="priority" id="task-priority" required>
                        <option value="Low">{{ __('Low')}}</option>
                        <option value="Medium">{{ __('Medium')}}</option>
                        <option value="High">{{ __('High')}}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="assign_to">{{ __('Assign To')}}</label>
                    <select class="form-control form-control-light" id="assign_to" name="assign_to" required>
                        @foreach($users as $u)
                            <option value="{{$u->id}}">{{$u->name}} - {{$u->email}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="duration">{{ __('Duration')}}</label>
                    <input type="text" class="form-control form-control-light" id="duration" name="duration" required autocomplete="off">
                    <input type="hidden" name="start_date">
                    <input type="hidden" name="due_date">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="task-description">{{ __('Description')}}</label>
            <textarea class="form-control form-control-light" id="task-description" rows="3" name="description"></textarea>
        </div>



        <div class="text-right">
            <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel')}}</button>
            <button type="submit" class="btn btn-primary">{{ __('Create')}}</button>
        </div>

    </form>
    <script>
        $(function() {
            var start = moment().startOf('hour');
            var end = moment().startOf('hour').add(32, 'hour');
            function cb(start, end) {
                $('input[name="start_date"]').val(start.format('YYYY-MM-DD HH:mm:ss'));
                $('input[name="due_date"]').val(end.format('YYYY-MM-DD HH:mm:ss'));
            }
            $('#duration').daterangepicker({
                timePicker: true,
                startDate: start,
                endDate: end,
                locale: {
                    format: 'MMMM D, YYYY hh:mm A',
                    applyLabel: "{{__('Apply')}}",
                    cancelLabel: "{{__('Cancel')}}",
                    fromLabel: "{{__('From')}}",
                    toLabel: "{{__('To')}}",
                    daysOfWeek: [
                        "{{__('Sun')}}",
                        "{{__('Mon')}}",
                        "{{__('Tue')}}",
                        "{{__('Wed')}}",
                        "{{__('Thu')}}",
                        "{{__('Fri')}}",
                        "{{__('Sat')}}"
                    ],
                    monthNames: [
                        "{{__('January')}}",
                        "{{__('February')}}",
                        "{{__('March')}}",
                        "{{__('April')}}",
                        "{{__('May')}}",
                        "{{__('June')}}",
                        "{{__('July')}}",
                        "{{__('August')}}",
                        "{{__('September')}}",
                        "{{__('October')}}",
                        "{{__('November')}}",
                        "{{__('December')}}"
                    ],
                }
            },cb);
            cb(start,end);
        });
    </script>
    <script>
        $(document).on('change',"select[name=project_id]",function () {
            $.get('@auth('web'){{route('home')}}@elseauth{{route('client.home')}}@endauth'+'/userProjectJson/'+$(this).val(),function (data) {
                $('select[name=assign_to]').html('');
                data = JSON.parse(data);
                $(data).each(function(i,d){
                    $('select[name=assign_to]').append('<option value="'+d.id+'">'+d.name+' - '+d.email+'</option>');
                });
            });
            $.get('@auth('web'){{route('home')}}@elseauth{{route('client.home')}}@endauth'+'/projectMilestoneJson/'+$(this).val(),function (data) {
                $('select[name=milestone_id]').html('<option value="">{{__('Select Milestone')}}</option>');
                data = JSON.parse(data);
                $(data).each(function(i,d){
                    $('select[name=milestone_id]').append('<option value="'+d.id+'">'+d.title+'</option>');
                });
            })
        })
    </script>

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