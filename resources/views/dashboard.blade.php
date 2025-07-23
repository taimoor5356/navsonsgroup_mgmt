@extends('layout.app')
@section('_styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style type="text/css">
    .select2-container .select2-selection--single {
        display: block;
        width: 100%;
        height: calc(2.25rem + 2px);
        padding: .375rem .25rem;
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #495057;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #ced4da;
        border-radius: .25rem;
        box-shadow: inset 0 0 0 transparent;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
    }
</style>
@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    @include('_messages')
    <div class="row">
        <div class="col-12 d-flex justify-content-between">
            <div class="breadcrumb-list">
                <h4 class="fw-bold py-3 mb-4">Dashboard</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 order-1">
            <div class="row">
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-car text-primary"></i> Total Car Washed</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalWashedCars) ? number_format($totalWashedCars->count()) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-group text-info"></i> Customers</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalNoOfCustomers) ? number_format($totalNoOfCustomers) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-success"></i> Total Payment <small>(This month)</small></span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($totalPayments) ? number_format($totalPayments, 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Expenses <small>(This month)</small></span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($totalExpenses) ? $totalExpenses : 0}}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('_scripts')
<script>
    $(document).ready(function() {
        $('select').select2();
        {{--
        // $(document).on('change', '#group_id', function() {
        //     var url = "{{route('filter_all_data')}}";
        //     $.ajax({
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             _token: "{{csrf_token()}}",
        //             group_id: $('#group_id').val(),
        //             provider_npi: '',
        //             location: '',
        //         },
        //         success: function(response) {
        //             if (response.status == true) {
        //                 $('#provider_npi').html(response.customers);
        //                 $('#location').html(response.locations);
        //                 $('#mrn_number').html(response.mrn_numbers);
        //             }
        //         }
        //     });
        // });

        // $(document).on('change', '#provider_npi', function() {
        //     var url = "{{route('filter_all_data')}}";
        //     $.ajax({
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             _token: "{{csrf_token()}}",
        //             group_id: $('#group_id').val(),
        //             provider_npi: $('#provider_npi').val(),
        //             location: '',
        //         },
        //         success: function(response) {
        //             if (response.status == true) {
        //                 $('#location').html(response.locations);
        //                 $('#mrn_number').html(response.mrn_numbers);
        //             }
        //         }
        //     });
        // });

        // $(document).on('change', '#location', function() {
        //     var url = "{{route('filter_all_data')}}";
        //     $.ajax({
        //         url: url,
        //         type: 'POST',
        //         data: {
        //             _token: "{{csrf_token()}}",
        //             group_id: '',
        //             provider_npi: $('#provider_npi').val(),
        //             location: $('#location').val(),
        //         },
        //         success: function(response) {
        //             if (response.status == true) {
        //                 $('#mrn_number').html(response.mrn_numbers);
        //             }
        //         }
        //     });
        // });
        --}}

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection