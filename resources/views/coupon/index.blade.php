@extends('layouts.main')
@push('scripts')
    <script>
        $(document).on('click', '#code-generate', function () {
            var length = 10;
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            $('#auto-code').val(result);
        });
    </script>
@endpush

@section('content')


    <section class="section">
        <div class="row mb-2">
            <div class="col-sm-4">
                <h2 class="section-title">{{ __('Coupons') }}</h2>
            </div>
            <div class="col-sm-8">
                <div class="text-sm-right">
                    <button type="button" class="btn btn-primary mt-4" data-ajax-popup="true" data-size="lg" data-title="{{ __('Add Coupon') }}" data-url="{{route('coupons.create')}}">
                        <i class="mdi mdi-plus"></i> {{ __('Add Coupon') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="selection-datatable" class="table table-hover" width="100%">
                            <thead class="thead-light">
                            <tr>
                                <th> {{__('Name')}}</th>
                                <th> {{__('Code')}}</th>
                                <th> {{__('Discount (%)')}}</th>
                                <th> {{__('Limit')}}</th>
                                <th> {{__('Used')}}</th>
                                <th class="text-right"> {{__('Action')}}</th>
                            </tr>
                            </thead>

                            <tbody>
                            @foreach ($coupons as $coupon)

                                <tr class="font-style">
                                    <td>{{ $coupon->name }}</td>
                                    <td>{{ $coupon->code }}</td>
                                    <td>{{ $coupon->discount }}</td>
                                    <td>{{ $coupon->limit }}</td>
                                    <td>{{ $coupon->used_coupon() }}</td>
                                    <td class="text-right">

                                        <a href="{{ route('coupons.show',$coupon->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fa fa-eye"></i> {{__('View')}}
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-primary" data-size="lg" data-ajax-popup="true" data-title="{{ __('Edit Coupon') }}" data-url="{{route('coupons.edit',[$coupon->id])}}">
                                            <i class="mdi mdi-pencil mr-1"></i>{{__('Edit')}}</a>
                                        <a href="#" class="btn btn-sm btn-outline-danger" onclick="(confirm('{{__('Are you sure ?')}}')?document.getElementById('delete-form-{{$coupon->id}}').submit(): '');">
                                            <i class="mdi mdi-delete mr-1"></i>{{__('Delete')}}</a>
                                        <form method="post" action="{{route('coupons.destroy', $coupon->id)}}" id="delete-form-{{$coupon->id}}" style="display: none">
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

    </section>
@endsection
