@extends('layouts.main')

@section('content')

<section class="section">


    <h2 class="section-title">{{ __('Orders') }}</h2>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="selection-datatable" class="table" width="100%">
                        <thead>
                            <tr>
                                <th>{{__('Order Id')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Plan Name')}}</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Invoice')}}</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>{{$order->order_id}}</td>
                                <td>{{$order->user_name}}</td>
                                <td>{{$order->plan_name}}</td>
                                <td>${{number_format($order->price)}}</td>
                                <td>
                                    @if($order->payment_status == 'succeeded')
                                        <i class="mdi mdi-circle text-success"></i> {{__(ucfirst($order->payment_status))}}
                                    @else
                                        <i class="mdi mdi-circle text-danger"></i> {{__(ucfirst($order->payment_status))}}
                                    @endif
                                </td>
                                <td>{{Utility::dateFormat($order->created_at)}}</td>
                                <td>
                                    @if(!empty($order->receipt))
                                        <a href="{{$order->receipt}}" target="_blank" class="btn btn-outline-primary btn-rounded btn-sm"><i class="mdi mdi-printer mr-1"></i> {{__('Invoice')}}</a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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
        });
    </script>
@endpush
