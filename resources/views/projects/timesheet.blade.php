@extends('layouts.main')
@section('content')
    <section class="section">
        @if($currantWorkspace && $timesheets)
            <div class="row mb-2">
                <div class="col-sm-4">
                    <h2 class="section-title">{{ __('Timesheet Detail') }}</h2>
                </div>
                @auth('web')
                <div class="col-sm-8">
                    <div class="text-sm-right">
                        <div class="mt-4">
                            <a href="#" class="btn btn-primary" data-ajax-popup="true" data-title="{{ __('Create Timesheet') }}" data-url="{{route('timesheet.create',[$currantWorkspace->slug])}}">{{__('Create Timesheet')}}</a>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
            <div class="row">
                <div class="col-md-12 animated">
                    <div class="card author-box card-primary">
                        <div class="card-body">
                            <table id="selection-datatable" class="table" width="100%">
                                <thead>
                                <tr>
                                    <th>{{__('#')}}</th>
                                    <th>{{__('Project')}}</th>
                                    <th>{{__('Task')}}</th>
                                    <th>{{__('Date')}}</th>
                                    <th>{{__('Time')}}</th>
                                    @auth('web')
                                    <th class="text-right">{{__('Action')}}</th>
                                    @endauth
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($timesheets as $key => $timesheet)
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{$timesheet->project->name}}</td>
                                        <td>{{$timesheet->task->title}}</td>
                                        <td>{{Utility::dateFormat($timesheet->date)}}</td>
                                        <td>{{$timesheet->time}}</td>
                                        @auth('web')
                                        <td class="text-right">
                                            <small>
                                                <a href="#" class="btn btn-sm btn-outline-primary" data-ajax-popup="true" data-title="{{ __('Edit Milestone') }}" data-url="{{route('timesheet.edit',[$currantWorkspace->slug,$timesheet->id])}}"><i class="mdi mdi-pencil"></i> {{__('Edit')}}</a>
                                                <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form1-{{$timesheet->id}}').submit(): '');"><i class="mdi mdi-delete"></i> {{__('Delete')}}</a>
                                                <form id="delete-form1-{{$timesheet->id}}" action="{{ route('timesheet.destroy',[$currantWorkspace->slug,$timesheet->id]) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </small>
                                        </td>
                                        @endauth
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="container mt-5">
                <div class="page-error">
                    <div class="page-inner">
                        <h1>404</h1>
                        <div class="page-description">
                            {{ __('Page Not Found') }}
                        </div>
                        <div class="page-search">
                            <p class="text-muted mt-3">{{ __('It\'s looking like you may have taken a wrong turn. Don\'t worry... it happens to the best of us. Here\'s a little tip that might help you get back on track.')}}</p>
                            <div class="mt-3">
                                <a class="btn btn-info mt-3" href="{{route('home')}}"><i class="mdi mdi-reply"></i> {{ __('Return Home')}}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </section>
@endsection

@push('style')
    <link href="{{asset('assets/css/vendor/dataTables.bootstrap4.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/vendor/responsive.bootstrap4.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/vendor/buttons.bootstrap4.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('assets/css/vendor/select.bootstrap4.css')}}" rel="stylesheet" type="text/css"/>
@endpush
@push('scripts')
    <script src="{{asset('assets/js/vendor/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('assets/js/vendor/dataTables.bootstrap4.js')}}"></script>
    <script src="{{asset('assets/js/vendor/dataTables.responsive.min.js')}}"></script>
    <script>
        $(document).ready(function () {
            $("#selection-datatable").DataTable({
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

            $(document).on('change','#project_id',function () {
                $("#task_id").html('');
                var selected = $("#task_id").attr('data-selected');
                $.ajax({
                    url:'{{route('tasks.ajax',$currantWorkspace->slug)}}/'+$(this).val(),
                    success:function (data) {

                        $.each(data,function (i,item) {
                            $("#task_id").append('<option value="'+item.id+'">'+item.title+'</option>');
                        })
                        if(typeof selected != 'undefined') {
                            $("#task_id").val(selected);
                        }
                    }
                })
            });
        });
    </script>
@endpush
