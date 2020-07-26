
<form method="post" action="{{ route('coupons.store') }}">
    @csrf
    <div class="row">
        <div class="form-group col-md-12">
            <label for="name">{{__('Name')}}</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group col-md-6">
            <label for="discount">{{__('Discount')}}</label>
            <input type="number" name="discount" class="form-control" required step="0.01">
            <span class="small">{{__('Note: Discount in Percentage')}}</span>
        </div>
        <div class="form-group col-md-6">
            <label for="limit">{{__('Limit')}}</label>
            <input type="number" name="limit" class="form-control" required>
        </div>

        <div class="form-group col-md-12" id="auto">
            <label for="code">{{__('Code')}}</label>
            <div class="input-group">
                <input class="form-control" name="code" type="text" id="auto-code">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text" id="code-generate"><i class="fa fa-history pr-1"></i> {{__('Generate')}}</button>
                </div>
            </div>
        </div>
        <div class="form-group col-md-12 text-right">
            <button class="btn btn-primary" type="submit">{{ __('Create') }}</button>
        </div>
    </div>
</form>

