<form method="post" action="{{ route('invoices.update',[$currantWorkspace->slug,$invoice->id]) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="form-group col-md-6">
            <label for="tax_id">{{__('Status')}}</label>
            <select class="form-control" name="status" id="status">
                <option value="sent" @if($invoice->status == 'sent') selected @endif>{{__('Sent')}}</option>
                <option value="paid" @if($invoice->status == 'paid') selected @endif>{{__('Paid')}}</option>
                <option value="canceled" @if($invoice->status == 'canceled') selected @endif>{{__('Canceled')}}</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="issue_date">{{__('Issue Date')}}</label>
            <input class="form-control" type="text" id="issue_date">
            <input required="required" name="issue_date" type="hidden">
        </div>
        <div class="form-group col-md-6">
            <label for="due_date">{{__('Due Date')}}</label>
            <input class="form-control" type="text" id="due_date">
            <input required="required" name="due_date" type="hidden">
        </div>
        <div class="form-group col-md-6">
            <label for="discount">{{__('Discount')}}</label>
            <input class="form-control" required="required" min="0" name="discount" type="number" value="{{$invoice->discount}}" id="discount">
        </div>
        <div class="form-group col-md-6">
            <label for="tax_id">{{__('Tax')}}%</label>
            <select class="form-control" name="tax_id" id="tax_id">
                <option value="">{{__('Select Tax')}}</option>
                @foreach($taxes as $p)
                    <option value="{{$p->id}}"  @if($invoice->tax_id == $p->id) selected @endif>{{$p->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="client_id">{{__('Client')}}</label>
            <select class="form-control" name="client_id" id="client_id">
                <option value="">{{__('Select Client')}}</option>
                @foreach($clients as $p)
                    <option value="{{$p->id}}" @if($invoice->client_id == $p->id) selected @endif>{{$p->name}} - {{$p->email}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group mt-3">
        <button class="btn btn-primary" type="submit">{{ __('Update') }}</button>
    </div>

</form>

<script>
    $(function() {

        var st = moment('{{$invoice->issue_date}}','YYYY-MM-DD');
        function cb(date){
            $('input[name="issue_date"]').val(date.format('YYYY-MM-DD'));
        }
        $('#issue_date').daterangepicker({
            startDate: st,
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'),10),
            locale: {
                format: 'YYYY-MM-DD',
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
        cb(st);

        var dst = moment('{{$invoice->due_date}}','YYYY-MM-DD');
        function dcb(date){
            $('input[name="due_date"]').val(date.format('YYYY-MM-DD'));
        }
        $('#due_date').daterangepicker({
            startDate: dst,
            singleDatePicker: true,
            showDropdowns: true,
            minYear: 1901,
            maxYear: parseInt(moment().format('YYYY'),10),
            locale: {
                format: 'YYYY-MM-DD',
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
        },dcb);
        dcb(dst);
    });
</script>
