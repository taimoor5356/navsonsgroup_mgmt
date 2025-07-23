@extends('layout.app')
@section('_styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
        <h5 class="card-header">
            <form method="POST" action="">
                @csrf
                <div class="row mb-3">
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                        <label for="">Select Dates</label>
                        <br>
                        <input type="text" name="date_range" value="{{Request::get('date_range')}}" class="form-control date_range_picker">
                    </div>
                    <!-- <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                        <label for="">End Date</label>
                        <br>
                        <input type="date" name="end_date" value="{{Request::get('end_date')}}" class="form-control">
                    </div> -->
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                        <label for=""></label>
                        <br>
                        <input type="submit" name="submit" class="form-control btn btn-primary w-50">
                    </div>
                </div>
            </form>
        </h5>
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display table-bordered responsive nowrap" width="100%">
                    <thead>
                    </thead>
                    <tbody>
                        @php $totalAmount = 0; @endphp
                        @foreach($totalCollection as $key => $collection)
                            <tr>
                                <td>{{$key=='currentMonth'?'Current Month Payments':($key=='previousPayment'?'Previous Payments':'')}}</td>
                                <td class=" text-center">${{$collection}}</td>
                            </tr>
                            @php $totalAmount += $collection; @endphp
                        @endforeach
                        <tr>
                            <td class="fw-bold">Total Amount</td>
                            <td class="fw-bold text-center">${{$totalAmount}}</td>
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
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    $(document).ready(function() {
        $(document).ready(function() {
            $(document).on('click', '#check-all', function() {
                $('.user-checkbox').each(function() {
                    $(this).prop('checked', $(this).is(':checked')? false : true);
                    $('#delete-selected-users').addClass('disabled');
                    $('.user-checkbox:checked').each(function() {
                        $('#delete-selected-users').removeClass('disabled');
                    });
                });
            });

            $(document).on('click', '.user-checkbox', function() {
                if ($(this).prop('checked')) {
                    $('#delete-selected-users').removeClass('disabled');
                } else {
                    var checkedRemaining = 0;
                    $('.user-checkbox').each(function() {
                        if ($(this).prop('checked')) {
                            checkedRemaining = 1;
                        }
                    });
                    if (checkedRemaining === 0) {
                        $('#delete-selected-users').addClass('disabled');
                    }
                }
            });

            $(document).on('click', '#delete-selected-users', function () {
                var selectedUsers = [];
                $('.user-checkbox:checked').each(function() {
                    selectedUsers.push($(this).attr('data-user-id'));
                });
                if (selectedUsers.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        title: 'Are you sure to DELETE?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: 'green',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{route('delete_multiple_users')}}",
                                method: 'POST',
                                data: {
                                    _token: "{{csrf_token()}}",
                                    user_ids: selectedUsers 
                                },
                                success: function(response) {
                                    if (response.status) {
                                        Swal.fire(
                                            'Deleted!',
                                            response.message,
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Failed!',
                                            'Failed to delete selected users.',
                                            'error'
                                        );
                                    }
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire(
                        'No user selected',
                        'Please select at least one user to delete.',
                        'info'
                    );
                }
            });

            $(document).on('click', '.delete-user', function () {
                var selectedUser = $(this).attr('data-user-id');
                if (selectedUser.length > 0) {
                    Swal.fire({
                        title: 'Are you sure?',
                        title: 'Are you sure to DELETE?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: 'green',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'No, cancel!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: "{{route('admin.users.destroy')}}",
                                method: 'POST',
                                data: {
                                    _token: "{{csrf_token()}}",
                                    user_id: selectedUser 
                                },
                                success: function(response) {
                                    if (response.status) {
                                        Swal.fire(
                                            'Deleted!',
                                            response.message,
                                            'success'
                                        );
                                    } else {
                                        Swal.fire(
                                            'Failed!',
                                            response.message,
                                            'error'
                                        );
                                    }
                                }
                            });
                        }
                    });
                } else {
                    Swal.fire(
                        'No user selected',
                        'Please select at least one user to delete.',
                        'info'
                    );
                }
            });

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
                if (diffDays > 30) {
                    alert("Please select a range of 30 days.");
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
    });
</script>
@endsection