@extends('layout.app')
@section('_styles')
<style>
    .butns {
        width: auto !important;
        display: flex !important;
        align-items: baseline;
    }
</style>
@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    @include('_messages')
    <div class="row">
        <div class="col-12 d-flex justify-content-between">
            <div class="breadcrumb-list">
                <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Report /</span> Total Collection</h4>
            </div>
        </div>
    </div>
    <!-- Responsive Table -->
        <div class="py-4">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/total-collection-report')) active @endif tab" href="{{route('customer.total_collection_report')}}">Total Collection</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/total-worked-rvus-report')) active @endif tab" href="{{route('customer.total_worked_rvus_report')}}">Total Worked RVUs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/insurance-receivable-details-report')) active @endif tab" href="{{route('customer.insurance_receivable_details_report')}}">Insurance Receivable Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/insurance-statuses-report')) active @endif tab" href="{{route('customer.insurance_statuses_report')}}">Insurance Statuses</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/patient-receivable-details-report')) active @endif tab" href="{{route('customer.patient_receivable_details_report')}}">Patient Receivable Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if(Request::url() == url('customer/patient-statuses-report')) active @endif tab" href="{{route('customer.patient_statuses_report')}}">Patient Statuses</a>
                </li>
            </ul>
        </div>
    <div class="card">
        <!-- <h5 class="card-header">
            <form method="POST" action="">
                @csrf
                <div class="row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                        <label for="">Select Dates</label>
                        <br>
                        <input type="text" name="date_range" value="{{Request::get('date_range')}}" class="form-control date_range_picker">
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                        <label for=""></label>
                        <br>
                        <input type="submit" name="submit" class="form-control btn btn-primary w-50">
                    </div>
                </div>
            </form>
        </h5> -->
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display table-bordered responsive nowrap" width="100%">
                    <thead>
                        <th>Account Receivable</th>
                        <th class="text-center">Amount</th>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($patientReceivableDetails as $key => $collection)
                            <tr>
                                <td>{{$key}}</td>
                                <td class="text-center">$ {{$collection}}</td>
                                @php $total = $total + $collection; @endphp
                            </tr>
                        @endforeach
                        <tr>
                            <td class="fw-bold">Total</td>
                            <td class="fw-bold text-center">$ {{$total}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!--/ Responsive Table -->
</div>

@endsection

@section('_scripts')
<script>
    $(document).ready(function () {
        var today = new Date();
        var last30Days = new Date();
        last30Days.setDate(today.getDate() - 30);
        $('.date_range_picker').daterangepicker({
            autoUpdateInput: false,
            maxDate: today,
            locale: {
                cancelLabel: 'Clear'
            }
        }).on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate;
            var endDate = picker.endDate;
            // Calculate the difference in days
            var diffDays = endDate.diff(startDate, 'days');
            // Check if the selected range is less than 30 days
            if (diffDays < 29) {
                alert("Please select a range of at least 30 days.");
                $(this).val(''); // Clear the input field
            } else {
                var startDateFormatted = startDate.format('YYYY/MM/DD');
                var endDateFormatted = endDate.format('YYYY/MM/DD');
                $(this).val(startDateFormatted + ' - ' + endDateFormatted).trigger('change');
            }
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('').trigger('change');
        });
        $('.date_range_picker').val('');
    });
</script>
@endsection