<form class="pl-3 pr-3" method="post" action="{{ route('tax.update',[$currantWorkspace->slug,$tax->id]) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">{{ __('Name') }}</label>
        <input type="text" class="form-control" id="name" name="name" value="{{$tax->name}}" required/>
    </div>
    <div class="form-group">
        <label for="name">{{ __('Rate') }}</label>
        <input type="number" class="form-control" id="rate" name="rate" min="0" step=".01" value="{{$tax->rate}}" required/>
    </div>
    <div class="form-group">
        <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
    </div>
</form>
