<form class="pl-3 pr-3" method="post" action="{{ route('test.email.send') }}" id="test_email">
    @csrf
    <div class="form-group">
        <label for="email">{{ __('E-Mail Address') }}</label>
        <input type="email" class="form-control" id="email" name="email" required/>
    </div>
    <div class="form-group">
        <input type="hidden" name="mail_driver" value="{{$data['mail_driver']}}" />
        <input type="hidden" name="mail_host" value="{{$data['mail_host']}}" />
        <input type="hidden" name="mail_port" value="{{$data['mail_port']}}" />
        <input type="hidden" name="mail_username" value="{{$data['mail_username']}}" />
        <input type="hidden" name="mail_password" value="{{$data['mail_password']}}" />
        <input type="hidden" name="mail_encryption" value="{{$data['mail_encryption']}}" />
        <button class="btn btn-primary" type="submit">{{ __('Send Test Mail') }}</button>
        <label id="email_sanding" style="display: none"><i class="fa fa-clock-o"></i> Sending ... </label>
    </div>
</form>
