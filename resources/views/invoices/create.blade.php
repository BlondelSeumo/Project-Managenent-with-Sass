<form method="post" action="{{ route('invoices.store',[$currantWorkspace->slug]) }}">
    @csrf
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="type">{{ __('Projects') }}</label>
                <select class="form-control" name="project_id" id="project_id" required>
                    <option value="">{{__('Select Project')}}</option>
                    @foreach($projects as $p)
                        <option value="{{$p->id}}">{{$p->name}}</option>
                    @endforeach
                </select>
            </div>
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
            <input class="form-control" required="required" min="0" name="discount" type="number" value="0" id="discount">
        </div>
        <div class="form-group col-md-6">
            <label for="tax_id">{{__('Tax')}}%</label>
            <select class="form-control" name="tax_id" id="tax_id">
                <option value="">{{__('Select Tax')}}</option>
                @foreach($taxes as $p)
                    <option value="{{$p->id}}">{{$p->name}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="client_id">{{__('Client')}}</label>
            <select class="form-control" name="client_id" id="client_id" required="required">
                <option value="">{{__('Select Client')}}</option>
                @foreach($clients as $p)
                    <option value="{{$p->id}}">{{$p->name}} - {{$p->email}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="form-group mt-3">
        <button class="btn btn-primary" type="submit">{{ __('Create') }}</button>
    </div>

</form>

<script>
    $(function() {

        var st = moment();
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

        function dcb(date){
            $('input[name="due_date"]').val(date.format('YYYY-MM-DD'));
        }
        $('#due_date').daterangepicker({
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
        dcb(st);
    });
</script>
