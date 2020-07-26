@extends('layouts.main')

@section('content')

    <section class="section">


        <h2 class="section-title">
            <div class="row">
                <div class="col-md-6">
                    {{ __('Invoices') }}
                </div>
                <div class="col-md-6">
                    @auth('web')
                        @if($currantWorkspace->creater->id == Auth::user()->id)
                            <div class="text-sm-right">
                                <a href="#" data-ajax-popup="true" data-size="lg" data-title="{{ __('Create New Invoice') }}" data-url="{{route('invoices.create',$currantWorkspace->slug)}}" class="btn btn-primary" type="button">
                                    <i class="mdi mdi-plus mr-1"></i> {{__('Create')}}
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </h2>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-centered table-hover mb-0 animated" id="selection-datatable">
                                <thead>
                                <th>{{__('Invoice')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Issue Date')}}</th>
                                <th>{{__('Due Date')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Status')}}</th>
                                @auth('web')
                                    <th class="text-right" width="200px">{{__('Action')}}</th>
                                @endauth
                                </thead>
                                <tbody>
                                @foreach($invoices as $key => $invoice)
                                    <tr>
                                        <td>
                                            <a href="@auth('web'){{ route('invoices.show',[$currantWorkspace->slug,$invoice->id]) }}@elseauth{{ route('client.invoices.show',[$currantWorkspace->slug,$invoice->id]) }}@endauth" class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-printer mr-1"></i>{{Utility::invoiceNumberFormat($invoice->invoice_id)}}
                                            </a>
                                        </td>
                                        <td>{{$invoice->project->name}}</td>
                                        <td>{{Utility::dateFormat($invoice->issue_date)}}</td>
                                        <td>{{Utility::dateFormat($invoice->due_date)}}</td>
                                        <td>{{$currantWorkspace->priceFormat($invoice->getTotal())}}</td>
                                        <td>
                                            @if($invoice->status == 'sent')
                                                <span class="badge badge-warning">{{__('Sent')}}</span>
                                            @elseif($invoice->status == 'paid')
                                                <span class="badge badge-success">{{__('Paid')}}</span>
                                            @elseif($invoice->status == 'canceled')
                                                <span class="badge badge-danger">{{__('Canceled')}}</span>
                                            @endif
                                        </td>
                                        @auth('web')
                                        <td class="text-right">
                                            <a href="#" class="btn btn-sm btn-outline-primary" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit Invoice') }}" data-url="{{route('invoices.edit',[$currantWorkspace->slug,$invoice->id])}}">
                                                <i class="mdi mdi-pencil mr-1"></i>{{__('Edit')}}</a>
                                            <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$invoice->id}}').submit(): '');">
                                                <i class="mdi mdi-delete mr-1"></i>{{__('Delete')}}</a>
                                            <form id="delete-form-{{$invoice->id}}" action="{{ route('invoices.destroy',[$currantWorkspace->slug,$invoice->id]) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
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
