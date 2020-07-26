@extends('layouts.main')

@section('content')

    <section class="section">


        <h2 class="section-title">
            <div class="row">
                <div class="col-md-6">
                    {{ __('Invoices') }}
                </div>
                <div class="col-md-6">
                    @auth('client')
                        @if($invoice->getDueAmount()>0 && !empty($currantWorkspace->stripe_key) && !empty($currantWorkspace->stripe_secret))
                        <div class="text-sm-right">
                            <a href="#" data-ajax-popup="true" data-title="{{ __('Add Payment') }}" data-size="lg" data-url="{{route('client.invoice.payment.create',[$currantWorkspace->slug,$invoice->id])}}" class="btn btn-primary" type="button">
                                <i class="mdi mdi-credit-card mr-1"></i> {{__('Add Payment')}}
                            </a>
                        </div>
                        @endif
                    @endauth
                    @auth('web')
                        @if($currantWorkspace->creater->id == Auth::user()->id)
                            <div class="text-sm-right">
                                <a href="#" class="btn btn-primary" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit Invoice') }}" data-url="{{route('invoices.edit',[$currantWorkspace->slug,$invoice->id])}}">
                                    <i class="mdi mdi-pencil mr-1"></i>{{__('Edit Invoice')}}
                                </a>
                                <a href="#" data-ajax-popup="true" data-title="{{ __('Add Item') }}" data-url="{{route('invoice.item.create',[$currantWorkspace->slug,$invoice->id])}}" class="btn btn-primary" type="button">
                                    <i class="mdi mdi-plus mr-1"></i> {{__('Add Item')}}
                                </a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </h2>

        <div class="section-body">
            <div class="invoice">
                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                <h2>{{__('Invoice')}}</h2>
                                <div class="invoice-number">{{Utility::invoiceNumberFormat($invoice->invoice_id)}}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <address>
                                        <strong>{{__('From')}}:</strong><br>
                                        @if($currantWorkspace->company){{$currantWorkspace->company}}@endif
                                        @if($currantWorkspace->address) <br>{{$currantWorkspace->address}}@endif
                                        @if($currantWorkspace->city) <br> {{$currantWorkspace->city}}, @endif @if($currantWorkspace->state){{$currantWorkspace->state}}@endif @if($currantWorkspace->zipcode) - {{$currantWorkspace->zipcode}}@endif
                                        @if($currantWorkspace->country) <br>{{$currantWorkspace->country}}@endif
                                        @if($currantWorkspace->telephone) <br>{{$currantWorkspace->telephone}}@endif
                                    </address>
                                    <address>
                                        <strong>{{__('To')}}:</strong><br>
                                        {{$invoice->client->name}}
                                        @if($invoice->client->address) <br>{{$invoice->client->address}}@endif
                                        @if($invoice->client->city) <br> {{$invoice->client->city}}, @endif @if($invoice->client->state){{$invoice->client->state}}@endif @if($invoice->client->zipcode) - {{$invoice->client->zipcode}}@endif
                                        @if($invoice->client->country) <br>{{$invoice->client->country}}@endif
                                        <br>{{$invoice->client->email}}
                                        @if($invoice->client->telephone) <br>{{$invoice->client->telephone}}@endif
                                    </address>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <address>
                                        <strong>{{__('Project')}}:</strong><br>
                                        {{$invoice->project->name}}
                                    </address>
                                    <address>
                                        <strong>{{__('Status')}}:</strong><br>
                                        <div class="font-weight-bold font-style">
                                            @if($invoice->status == 'sent')
                                                <span class="badge badge-warning">{{__('Sent')}}</span>
                                            @elseif($invoice->status == 'paid')
                                                <span class="badge badge-success">{{__('Paid')}}</span>
                                            @elseif($invoice->status == 'canceled')
                                                <span class="badge badge-danger">{{__('Canceled')}}</span>
                                            @endif
                                        </div>
                                    </address>
                                    <address>
                                        <strong>{{__('Issue Date')}}:</strong><br>
                                        {{Utility::dateFormat($invoice->issue_date)}}
                                    </address>
                                    <address>
                                        <strong>{{__('Due Date')}}:</strong><br>
                                        {{Utility::dateFormat($invoice->due_date)}}
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="section-title">{{__('Order Summary')}}</div>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-md">
                                    <tbody><tr>
                                        <th data-width="40" style="width: 40px;">#</th>
                                        <th>{{__('Item')}}</th>
                                        <th class="text-right">{{__('Totals')}}</th>
                                        @auth('web')
                                        <th class="text-right">{{__('Action')}}</th>
                                        @endauth
                                    </tr>
                                    @if($items = $invoice->items)
                                        @foreach($items as $key => $item)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{$item->task->title}} - {{$item->task->project->name}}</td>
                                                <td class="text-right">{{$currantWorkspace->priceFormat($item->price*$item->qty)}}</td>
                                                @auth('web')
                                                <td class="text-right">
                                                    <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$item->id}}').submit(): '');">
                                                        <i class="mdi mdi-delete"></i></a>
                                                    <form id="delete-form-{{$item->id}}" action="{{ route('invoice.item.destroy',[$currantWorkspace->slug,$invoice->id,$item->id]) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                </td>
                                                @endauth
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody></table>
                            </div>
                            <div class="row mt-4">
                                <div class="col-lg-8">
                                </div>
                                <div class="col-lg-4 text-right">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{__('Subtotal')}}</div>
                                        <div class="invoice-detail-value">{{$currantWorkspace->priceFormat($invoice->getSubTotal())}}</div>
                                    </div>
                                    @if($invoice->discount)
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{__('Discount')}}</div>
                                        <div class="invoice-detail-value">{{$currantWorkspace->priceFormat($invoice->discount)}}</div>
                                    </div>
                                    @endif
                                    @if($invoice->tax)
                                        <div class="invoice-detail-item">
                                            <div class="invoice-detail-name">{{__('Tax')}} {{$invoice->tax->name}} ({{$invoice->tax->rate}}%)</div>
                                            <div class="invoice-detail-value">{{$currantWorkspace->priceFormat($invoice->getTaxTotal())}}</div>
                                        </div>
                                    @endif
                                    <hr class="mt-2 mb-2">
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{__('Total')}}</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">{{$currantWorkspace->priceFormat($invoice->getTotal())}}</div>
                                    </div>
                                    <div class="invoice-detail-item">
                                        <div class="invoice-detail-name">{{__('Due Amount')}}</div>
                                        <div class="invoice-detail-value invoice-detail-value-lg">{{$currantWorkspace->priceFormat($invoice->getDueAmount())}}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right">
                    <a href="@auth('web'){{route('invoice.print',[$currantWorkspace->slug,\Illuminate\Support\Facades\Crypt::encryptString($invoice->id)])}}@elseauth{{route('client.invoice.print',[$currantWorkspace->slug,\Illuminate\Support\Facades\Crypt::encryptString($invoice->id)])}}@endauth" class="btn btn-warning btn-icon icon-left"><i class="dripicons-print"></i> Print</a>
                </div>
            </div>
        </div>
        @if($payments = $invoice->payments)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>{{__('Payments')}}</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-centered table-hover mb-0 animated">
                                <thead>
                                <th>{{__('Id')}}</th>
                                <th>{{__('Amount')}}</th>
                                <th>{{__('Currency')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Date')}}</th>
                                <th>{{__('Receipt')}}</th>
                                </thead>
                                <tbody>
                                @foreach($payments as $key => $payment)
                                    <tr>
                                        <td>{{$payment->order_id}}</td>
                                        <td>{{$currantWorkspace->priceFormat($payment->amount)}}</td>
                                        <td>{{strtoupper($payment->currency)}}</td>
                                        <td>
                                            @if($payment->payment_status == 'succeeded')
                                                <i class="mdi mdi-circle text-success"></i> {{__(ucfirst($payment->payment_status))}}
                                            @else
                                                <i class="mdi mdi-circle text-danger"></i> {{__(ucfirst($payment->payment_status))}}
                                            @endif
                                        </td>
                                        <td>{{Utility::dateFormat($payment->created_at)}}</td>
                                        <td>
                                            @if(!empty($payment->receipt))
                                                <a href="{{$payment->receipt}}" target="_blank" class="btn btn-outline-primary btn-rounded btn-sm"><i class="mdi mdi-printer mr-1"></i> {{__('Receipt')}}</a>
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
        </div>
        @endif

    </section>

@endsection
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
@endpush
