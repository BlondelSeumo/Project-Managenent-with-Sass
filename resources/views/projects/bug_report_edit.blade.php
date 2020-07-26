
@if($project && $currantWorkspace && $bug)

    <form class="pl-3 pr-3" method="post" action="@auth('web'){{ route('projects.bug.report.update',[$currantWorkspace->slug,$project->id,$bug->id]) }}@elseauth{{ route('client.projects.bug.report.update',[$currantWorkspace->slug,$project->id,$bug->id]) }}@endauth">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="task-title">{{ __('Title')}}</label>
                    <input type="text" class="form-control form-control-light" id="task-title" placeholder="{{ __('Enter Title')}}" name="title" value="{{$bug->title}}" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label for="task-priority">{{ __('Priority')}}</label>
                    <select class="form-control form-control-light" name="priority" id="task-priority" required>
                        <option value="Low" @if($bug->priority=='Low') selected @endif>{{ __('Low')}}</option>
                        <option value="Medium" @if($bug->priority=='Medium') selected @endif>{{ __('Medium')}}</option>
                        <option value="High" @if($bug->priority=='High') selected @endif>{{ __('High')}}</option>
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
                            <option @if($bug->assign_to==$u->id) selected @endif value="{{$u->id}}">{{$u->name}} - {{$u->email}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="status">{{ __('Status')}}</label>
                    <select class="form-control form-control-light" id="status" name="status" required>
                        @foreach($arrStatus as $id => $status)
                            <option @if($bug->status==$id) selected @endif value="{{$id}}">{{__($status)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="task-description">{{ __('Description')}}</label>
            <textarea class="form-control form-control-light" id="task-description" rows="3" name="description">{{$bug->description}}</textarea>
        </div>

        <div class="text-right">
            <button type="button" class="btn btn-light" data-dismiss="modal">{{ __('Cancel')}}</button>
            <button type="submit" class="btn btn-primary">{{ __('Update')}}</button>
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
