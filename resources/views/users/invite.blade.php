<link href="{{ asset('assets/css/vendor/bootstrap-tagsinput.css') }}" rel="stylesheet">
<form class="pl-3 pr-3" method="post" action="{{ route('users.invite.update',[$currantWorkspace->slug]) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="users_list">{{ __('Users') }}</label>
        <input type="email" class="form-control" id="users_list" name="users_list" value=""/>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Invite Users') }}</button>
    </div>
</form>
