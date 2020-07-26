@extends('layouts.main')

@section('content')

    <div class="container-fluid">
        <div class="row mt-5 mb-1">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="{{route('workspace.settings.store',$currantWorkspace->slug)}}" class="mt-3" enctype="multipart/form-data">
                            @csrf
                            <div class="border p-3 mb-3 rounded">
                                <h4 class="header-title mb-3">{{__('Workspace Settings')}}</h4>
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="name">{{ __('Name') }}</label>
                                            <input type="text" class="form-control" id="name" name="name" value="{{$currantWorkspace->name}}"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{ __('Currency') }}</label>
                                            <input type="text" name="currency" id="currency" class="form-control" value="{{$currantWorkspace->currency}}"/>
                                            @if ($errors->has('currency'))
                                                <span class="invalid-feedback d-block">
                                                    {{ $errors->first('currency') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="form-group">
                                            <label for="name">{{ __('Currency Code') }}</label>
                                            <input type="text" name="currency_code" id="currency_code" class="form-control" value="{{$currantWorkspace->currency_code}}"/>
                                            @if ($errors->has('currency_code'))
                                                <span class="invalid-feedback d-block">
                                                    {{ $errors->first('currency_code') }}
                                                </span>
                                            @endif
                                            <small>Note: Add currancy code from <a href="https://stripe.com/docs/currencies" target="_new">stripe document</a>.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="logo">{{ __('Logo') }}</label>
                                            <input type="file" name="logo" id="logo" class="form-control" accept="image/png"/>
                                            @if ($errors->has('logo'))
                                                <span class="invalid-feedback d-block">
                                                    {{ $errors->first('logo') }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-center pr-5 pl-5 pt-3 pb-3">
                                        <img src="@if($currantWorkspace->logo){{asset(Storage::url('logo/'.$currantWorkspace->logo))}}@else{{asset(Storage::url('logo/logo.png'))}}@endif" style="max-width: 100%"/>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-sm-12">
                                        <div class="text-sm-right">
                                            <button class="btn btn-primary" type="submit">
                                                <i class="mdi mdi-content-save mr-1"></i> {{__('Save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="border p-3 mb-3 rounded">
                            <h4 class="header-title mb-3">
                                {{__('Billing Details')}}
                            </h4>
                            <form method="post" action="{{route('workspace.settings.store',$currantWorkspace->slug)}}">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label for="address">{{__('Name')}}</label>
                                        <input class="form-control font-style" name="company" type="text" value="{{ $currantWorkspace->company }}" id="company">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="address">{{__('Address')}}</label>
                                        <input class="form-control font-style" name="address" type="text" value="{{ $currantWorkspace->address }}" id="address">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="city">{{__('City')}}</label>
                                        <input class="form-control font-style" name="city" type="text" value="{{ $currantWorkspace->city }}" id="city">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="state">{{__('State')}}</label>
                                        <input class="form-control font-style" name="state" type="text" value="{{ $currantWorkspace->state }}" id="state">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="zipcode">{{__('Zip/Post Code')}}</label>
                                        <input class="form-control" name="zipcode" type="text" value="{{ $currantWorkspace->zipcode }}" id="zipcode">
                                    </div>
                                    <div class="form-group  col-md-6">
                                        <label for="country">{{__('Country')}}</label>
                                        <input class="form-control font-style" name="country" type="text" value="{{ $currantWorkspace->country }}" id="country">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="telephone">{{__('Telephone')}}</label>
                                        <input class="form-control" name="telephone" type="text" value="{{ $currantWorkspace->telephone }}" id="telephone">
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-sm-6">
                                        <div class="">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-update mr-1"></i> {{ __('Update')}} </button>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </form>
                        </div>
                        <div class="border p-3 mb-3 rounded">
                            <h4 class="header-title mb-3">
                                {{__('Stripe Payment Details')}}
                            </h4>
                            <form method="post" action="{{route('workspace.settings.store',$currantWorkspace->slug)}}">
                                @csrf
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label for="stripe_key">{{__('Stripe Key')}}</label>
                                        <input class="form-control font-style" name="stripe_key" type="text" value="{{ $currantWorkspace->stripe_key }}" id="stripe_key" required>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="stripe_secret">{{__('Stripe Secret')}}</label>
                                        <input class="form-control font-style" name="stripe_secret" type="text" value="{{ $currantWorkspace->stripe_secret }}" id="stripe_secret" required>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-sm-6">
                                        <div class="">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-update mr-1"></i> {{ __('Update')}} </button>
                                        </div>
                                    </div> <!-- end col -->
                                </div> <!-- end row -->
                            </form>
                        </div>
                    </div>
                </div>


            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="border p-3 mb-3 rounded repeater" data-value="{{json_encode($stages)}}">
                            <h4 class="header-title mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{__('Task Stages')}}
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button data-repeater-create type="button" value="Add" class="btn btn-primary"> <i class="mdi mdi-plus mr-1"></i> {{__('Add')}}</button>
                                    </div>
                                </div>
                            </h4>
                            <small>{{__('System will consider last stage as a completed / done task for get progress on project.')}}</small>
                            <form method="post" action="{{route('stages.store',$currantWorkspace->slug)}}">
                                @csrf
                                <table class="table table-hover" width="100%" data-repeater-list="stages">
                                    <thead>
                                        <th><i class="mdi mdi-drag-variant"></i></th>
                                        <th>{{__('Name')}}</th>
                                        <th class="text-right">{{__('Delete')}}</th>
                                    </thead>
                                    <tbody>
                                        <tr data-repeater-item>
                                            <td><i class="mdi mdi-drag-variant sort-handler"></i></td>
                                            <td>
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="text" name="name" class="form-control" required/>
                                            </td>
                                            <td class="text-right">
                                                <button data-repeater-delete type="button" class="btn btn-danger"><i class="mdi mdi-delete"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-sm-right">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-content-save mr-1"></i> {{__('Save')}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="border p-3 mb-3 rounded repeater-bug" data-value="{{json_encode($bugStages)}}">
                            <h4 class="header-title mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{__('Bug Stages')}}
                                    </div>
                                    <div class="col-md-6 text-right">
                                        <button data-repeater-create type="button" value="Add" class="btn btn-primary"> <i class="mdi mdi-plus mr-1"></i> {{__('Add')}}</button>
                                    </div>
                                </div>
                            </h4>
                            <small>{{__('System will consider last stage as a completed / done task for get progress on project.')}}</small>
                            <form method="post" action="{{route('bug.stages.store',$currantWorkspace->slug)}}">
                                @csrf
                                <table class="table table-hover" width="100%" data-repeater-list="stages">
                                    <thead>
                                    <th><i class="mdi mdi-drag-variant"></i></th>
                                    <th>{{__('Name')}}</th>
                                    <th class="text-right">{{__('Delete')}}</th>
                                    </thead>
                                    <tbody>
                                    <tr data-repeater-item>
                                        <td><i class="mdi mdi-drag-variant sort-handler"></i></td>
                                        <td>
                                            <input type="hidden" name="id" id="id"/>
                                            <input type="text" name="name" class="form-control" required/>
                                        </td>
                                        <td class="text-right">
                                            <button data-repeater-delete type="button" class="btn btn-danger"><i class="mdi mdi-delete"></i></button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <div class="text-sm-right">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="mdi mdi-content-save mr-1"></i> {{__('Save')}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="border p-3 mb-3 rounded">
                            <h4 class="header-title mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{__('Taxes')}}
                                    </div>
                                    <div class="col-md-6">
                                        <div class="text-sm-right">
                                            <button class="btn btn-primary" type="button" data-ajax-popup="true" data-title="{{ __('Add Tax') }}" data-url="{{route('tax.create',$currantWorkspace->slug)}}">
                                                <i class="mdi mdi-plus mr-1"></i> {{__('Create')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </h4>
                            <table id="selection-datatable" class="table table-bordered" width="100%">
                                <thead>
                                <tr>
                                    <th>{{__('Name')}}</th>
                                    <th>{{__('Rate')}}</th>
                                    <th width="200px" class="text-right">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($taxes as $tax)
                                    <tr>
                                        <td>{{$tax->name}}</td>
                                        <td>{{$tax->rate}}%</td>
                                        <td class="text-right">
                                            <a href="#" class="btn btn-sm btn-outline-primary" data-ajax-popup="true" data-title="{{ __('Edit Tax') }}" data-url="{{route('tax.edit',[$currantWorkspace->slug,$tax->id])}}">
                                                <i class="mdi mdi-pencil mr-1"></i>{{ __('Edit') }}
                                            </a>
                                            <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$tax->id}}').submit(): '');">
                                                <i class="mdi mdi-delete mr-1"></i>{{__('Delete')}}</a>
                                            <form id="delete-form-{{$tax->id}}" action="{{ route('tax.destroy',[$currantWorkspace->slug,$tax->id]) }}" method="POST" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="border p-3 mb-3 rounded">
                            <h4 class="header-title mb-3">
                                {{__('Invoice')}}
                            </h4>
                            <div class="row">
                                <div class="col-md-2">
                                    <form action="{{route('workspace.settings.store',$currantWorkspace->slug)}}" method="post">
                                        @csrf
                                        <div class="form-group">
                                            <label for="address">{{__('Invoice Template')}}</label>
                                            <select class="form-control" name="invoice_template">
                                                <option value="template1" @if($currantWorkspace->invoice_template == 'template1') selected @endif>New York</option>
                                                <option value="template2" @if($currantWorkspace->invoice_template == 'template2') selected @endif>Toronto</option>
                                                <option value="template3" @if($currantWorkspace->invoice_template == 'template3') selected @endif>Rio</option>
                                                <option value="template4" @if($currantWorkspace->invoice_template == 'template4') selected @endif>London</option>
                                                <option value="template5" @if($currantWorkspace->invoice_template == 'template5') selected @endif>Istanbul</option>
                                                <option value="template6" @if($currantWorkspace->invoice_template == 'template6') selected @endif>Mumbai</option>
                                                <option value="template7" @if($currantWorkspace->invoice_template == 'template7') selected @endif>Hong Kong</option>
                                                <option value="template8" @if($currantWorkspace->invoice_template == 'template8') selected @endif>Tokyo</option>
                                                <option value="template9" @if($currantWorkspace->invoice_template == 'template9') selected @endif>Sydney</option>
                                                <option value="template10" @if($currantWorkspace->invoice_template == 'template10') selected @endif>Paris</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">{{__('Color')}}</label>
                                            <div class="row gutters-xs">

                                                @foreach($colors as $key => $color)
                                                <div class="col-auto">
                                                    <label class="colorinput">
                                                        <input name="invoice_color" type="radio" value="{{$color}}" class="colorinput-input" @if($currantWorkspace->invoice_color == $color) checked @endif>
                                                        <span class="colorinput-color" style="background: #{{$color}}"></span>
                                                    </label>
                                                </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <button class="btn btn-primary" type="submit">
                                            <i class="mdi mdi-content-save mr-1"></i> {{__('Save')}}
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-10">
                                    <iframe frameborder="0" width="100%" height="500px" src="{{route('invoice.preview',[$currantWorkspace->slug,($currantWorkspace->invoice_template)?$currantWorkspace->invoice_template:'template1',($currantWorkspace->invoice_color)?$currantWorkspace->invoice_color:'fff'])}}"></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{asset('assets/js/repeater.js')}}"></script>

    <script>
        $(document).on("change","select[name='invoice_template'], input[name='invoice_color']",function () {
            var template = $("select[name='invoice_template']").val();
            var color = $("input[name='invoice_color']:checked").val();
            $('iframe').attr('src','{{url($currantWorkspace->slug.'/invoices/preview')}}/'+template+'/'+color);
        });
        $(document).ready(function () {

            var $dragAndDrop = $("body .repeater tbody").sortable({
                handle: '.sort-handler'
            });

            var $repeater = $('.repeater').repeater({
                initEmpty: true,
                defaultValues: {

                },
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                    }
                },
                ready: function (setIndexes) {
                    $dragAndDrop.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });


            var value = $(".repeater").attr('data-value');
            if(typeof value != 'undefined' && value.length != 0)
            {
                value = JSON.parse(value);
                $repeater.setList(value);
            }


            var $dragAndDropBug = $("body .repeater-bug tbody").sortable({
                handle: '.sort-handler'
            });

            var $repeaterBug = $('.repeater-bug').repeater({
                initEmpty: true,
                defaultValues: {

                },
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    if (confirm('Are you sure you want to delete this element?')) {
                        $(this).slideUp(deleteElement);
                    }
                },
                ready: function (setIndexes) {
                    $dragAndDropBug.on('drop', setIndexes);
                },
                isFirstItemUndeletable: true
            });


            var valuebug = $(".repeater-bug").attr('data-value');
            if(typeof valuebug != 'undefined' && valuebug.length != 0)
            {
                valuebug = JSON.parse(valuebug);
                $repeaterBug.setList(valuebug);
            }

        });
    </script>
@endpush
