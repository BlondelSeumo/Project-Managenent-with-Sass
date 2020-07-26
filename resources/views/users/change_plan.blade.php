<form class="pl-3 pr-3" method="post" action="{{ route('users.change.plan',[$user->id]) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">{{ __('Plan') }}</label>
        <select class="form-control" name="plan">
            @foreach($plans as $plan)
                <option value="{{$plan->id}}" @if($plan->id == $user->plan) selected @endif>{{$plan->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Change Plan') }}</button>
    </div>
</form>
