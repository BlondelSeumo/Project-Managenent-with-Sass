@extends('layouts.main')
@section('page-title')
    {{__('Coupon Detail')}}
@endsection

@section('content')
    <section class="section">
        <div class="row mb-2">
            <h2 class="section-title">{{ __('Coupon Detail') }}</h2>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between w-100">
                            <h4>{{$coupon->code}}</h4>
                        </div>
                    </div>
                    <div class="card-body p-10">
                        <div class="dataTables_wrapper">
                            <div class="table-responsive">
                                <table class="table table-hover" id="dataTable">
                                    <thead class="thead-light">
                                    <tr>
                                        <th> {{__('User')}}</th>
                                        <th> {{__('Date')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($userCoupons as $userCoupon)
                                        <tr class="font-style">
                                            <td>{{ !empty($userCoupon->userDetail)?$userCoupon->userDetail->name:'' }}</td>
                                            <td>{{ $userCoupon->created_at }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
