<form class="pl-3 pr-3" method="post" action="{{ route('tax.store',[$currantWorkspace->slug]) }}">
    @csrf
    <div class="form-group">
        <label for="name">{{ __('Name') }}</label>
        <input type="text" class="form-control" id="name" name="name" required/>
    </div>
    <div class="form-group">
        <label for="name">{{ __('Rate') }}</label>
        <input type="number" class="form-control" id="rate" name="rate" min="0" step=".01" required/>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Create') }}</button>
    </div>
</form>
