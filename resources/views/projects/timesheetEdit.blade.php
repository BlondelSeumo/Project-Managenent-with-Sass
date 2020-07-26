@if($timesheet && $currantWorkspace)
    <form class="pl-3 pr-3" method="post" action="{{ route('timesheet.update',[$currantWorkspace->slug,$timesheet->id]) }}">
        @csrf
        @method('put')

        <div class="form-group">
            <label for="task_id">{{ __('Project')}}</label>
            <select class="form-control form-control-light" name="project_id" id="project_id" required>
                <option value="">{{ __('Select Project')}}</option>
                @foreach($projects as $project)
                    <option value="{{ $project->id }}" @if($timesheet->project_id == $project->id) selected @endif>{{ $project->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="task_id">{{ __('Task')}}</label>
            <select class="form-control form-control-light" name="task_id" id="task_id" required data-selected="{{$timesheet->task_id}}">
                <option value="">{{ __('Select Task')}}</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date">{{ __('Date')}}</label>
            <input type="date" class="form-control form-control-light date" id="date" value="{{ $timesheet->date }}" placeholder="{{ __('Date')}}" name="date" required>
        </div>
        <div class="form-group">
            <label for="time">{{ __('Time')}}</label>
            <input type="time" class="form-control form-control-light" id="time" value="{{ $timesheet->time }}" placeholder="{{ __('Time')}}" name="time" required>
        </div>
        <div class="form-group">
            <label for="description">{{ __('Description')}}</label>
            <textarea class="form-control form-control-light" id="description" rows="3" name="description">{{ $timesheet->description }}</textarea>
        </div>
        <div class="text-right">
            <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel')}}</button>
            <button type="submit" class="btn btn-primary">{{ __('Edit')}}</button>
        </div>

    </form>
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
<script>
    $("body #project_id").trigger('change');
</script>