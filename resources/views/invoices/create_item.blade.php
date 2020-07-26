<form method="post" action="{{ route('invoice.item.store',[$currantWorkspace->slug,$invoice->id]) }}">
    @csrf
    <div class="form-group">
        <label for="task">{{__('Tasks')}}</label>
        <select class="form-control" name="task" id="task" required>
            <option value="">{{__('Select Task')}}</option>
            @foreach($invoice->project->tasks() as $task)
                <option value="{{$task->id}}">{{$task->title}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="price">{{__('Price')}}</label>
        <input class="form-control" type="number" min="0" value="0" id="price" name="price" required>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Add') }}</button>
    </div>
</form>