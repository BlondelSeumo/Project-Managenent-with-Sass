@extends('layouts.main')

@section('content')

<section class="section">

    <div class="row mb-2">
        <div class="col-sm-4">
            <h2 class="section-title">
                {{ __('Tasks')}}
            </h2>
        </div>
        <div class="col-sm-8">
            <div class="text-sm-right">
                <div class="mt-4">
                    <select class="form-control form-control-sm w-auto d-inline" size="sm" name="project" id="project">
                        <option value="">{{__('All Projects')}}</option>
                        @foreach($projects as $project)
                            <option value="{{$project->id}}">{{$project->name}}</option>
                        @endforeach
                    </select>
                    @if ($currantWorkspace->permission == 'Owner')
                        <select class="form-control form-control-sm w-auto d-inline" size="sm" name="assign_to" id="assign_to">
                            <option value="">{{__('All Users')}}</option>
                            @foreach($users as $user)
                                <option value="{{$user->id}}">{{$user->name}}</option>
                            @endforeach
                        </select>
                    @endif
                    <select class="form-control form-control-sm w-auto d-inline" size="sm" name="status" id="status">
                        <option value="">{{__('All Status')}}</option>
                        @foreach($stages as $stage)
                            <option value="{{$stage->id}}">{{__($stage->name)}}</option>
                        @endforeach
                    </select>
                    <select class="form-control form-control-sm w-auto d-inline" size="sm" name="priority" id="priority">
                        <option value="">{{__('All Priority')}}</option>
                        <option value="Low">{{ __('Low')}}</option>
                        <option value="Medium">{{ __('Medium')}}</option>
                        <option value="High">{{ __('High')}}</option>
                    </select>
                    <input type="text" class="form-control form-control-sm w-auto d-inline form-control-light" id="duration1" name="duration" value="{{__('Select Date Range')}}">
                    <input type="hidden" name="start_date1" id="start_date1">
                    <input type="hidden" name="due_date1" id="end_date1">
                    <button class="btn btn-primary btn-sm ml-2" id="filter"><i class="mdi mdi-check"></i> {{ __('Apply')}}</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-hover mb-0 animated" id="selection-datatable">
                            <thead>
                                <th>{{__('Task')}}</th>
                                <th>{{__('Project')}}</th>
                                <th>{{__('Milestone')}}</th>
                                <th>{{__('Due in')}}</th>
                                @if($currantWorkspace->permission == 'Owner' || Auth::user()->getGuard() == 'client')
                                    <th>{{__('Assigned to')}}</th>
                                @endif
                                <th>{{__('Status')}}</th>
                                <th>{{__('Priority')}}</th>
                                @if($currantWorkspace->permission == 'Owner')
                                    <th class="text-right" width="150px">{{__('Action')}}</th>
                                @endif
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

@endsection

@push('style')
    <link href="{{asset('assets/css/vendor/dataTables.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/vendor/responsive.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/vendor/buttons.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/vendor/select.bootstrap4.css')}}" rel="stylesheet" type="text/css" />
@endpush
@push('scripts')
    <script src="{{asset('assets/js/vendor/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('assets/js/vendor/dataTables.responsive.min.js')}}"></script>
    <script>
        $(function() {
            // var start = moment().startOf('hour').add(-15,'day');
            // var end = moment().add(45,'day');
            function cb(start, end) {
                $("#duration1").val(start.format('MMM D, YY hh:mm A')+' - '+end.format('MMM D, YY hh:mm A'));
                $('input[name="start_date1"]').val(start.format('YYYY-MM-DD HH:mm:ss'));
                $('input[name="due_date1"]').val(end.format('YYYY-MM-DD HH:mm:ss'));
            }
            $('#duration1').daterangepicker({
                timePicker: true,
                autoUpdateInput: false,
                // startDate: start,
                // endDate: end,
                locale: {
                    format: 'MMM D, YY hh:mm A',
                    applyLabel: "{{__('Apply')}}",
                    cancelLabel: "{{__('Cancel')}}",
                    fromLabel: "{{__('From')}}",
                    toLabel: "{{__('To')}}",
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
            // cb(start,end);
        });
    </script>
    <script>

        $(document).ready(function () {
            var table = $("#selection-datatable").DataTable({
                order: [],
                select: {style: "multi"},
                language: {
                    paginate: {previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>"},
                    lengthMenu: "{{__('Show')}} _MENU_ {{__('entries')}}",
                    zeroRecords: "{{__('No data available in table')}}",
                    info: "{{__('Showing')}} _START_ {{__('to')}} _END_ {{__('of')}} _TOTAL_ {{__('entries')}}",
                    infoEmpty: " ",
                    search:"{{__('Search:')}}"
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded")
                }
            });

            $(document).on("click","#filter",function () {
                getData();
            });

            function getData() {
                table.clear().draw();
                $("#selection-datatable tbody tr").html('<td colspan="11" class="text-center"> Loading ...</td>');

                var data={
                    _token:$('meta[name="csrf-token"]').attr('content'),
                    project:$("#project").val(),
                    assign_to:$("#assign_to").val(),
                    priority:$("#priority").val(),
                    status:$("#status").val(),
                    start_date:$("#start_date1").val(),
                    end_date:$("#end_date1").val(),
                };

                $.ajax({
                    url:'{{route('tasks.ajax',[$currantWorkspace->slug])}}',
                    type:'POST',
                    data:data,
                    success:function(data){
                        table.rows.add(data.data).draw();
                    },
                    error:function (data) {
                        toastr('Info',data.error,'info')
                    }
                })
            }
            getData();

        });
    </script>
@endpush
