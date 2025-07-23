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
                <h4 class="fw-bold py-3 mb-4">Detailed Report</h4>
            </div>
        </div>
    </div>
    <!-- Responsive Table -->
    <div class="card">
        <div class="px-4 py-2">
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
        <h5 class="card-header">Users</h5>
        <div class="card-body">
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-total-collection" role="tabpanel" aria-labelledby="pills-total-collection-tab">
                    <div class="table-responsive text-nowrap">
                        <table class="table total-collection-data-table display responsive nowrap" width="100%">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>#</th>
                                    <th>Current Month</th>
                                    <th>Previous Payment</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-total-worked-rvus" role="tabpanel" aria-labelledby="pills-total-worked-rvus-tab">
                    <div class="table-responsive text-nowrap">
                        <table class="table total-worked-rvus-data-table display responsive nowrap" width="100%">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>#</th>
                                    <th>Paid</th>
                                    <th>Pending</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-insurance-receivable-details" role="tabpanel" aria-labelledby="pills-insurance-receivable-details-tab">
                    <div class="row">
                        <div class="row">
                            <div class="table-responsive text-nowrap">
                                <table class="table insurance-receivable-details-data-table display responsive nowrap table-bordered" width="100%">
                                    <thead>
                                        <tr class="text-nowrap">
                                            <th>#</th>
                                            <th>Account Receivable</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-insurance-statuses" role="tabpanel" aria-labelledby="pills-insurance-statuses-tab">
                    <div class="table-responsive text-nowrap">
                        <table class="table insurance-statuses-data-table display responsive nowrap table-bordered" width="100%">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>#</th>
                                    <th>Statuses</th>
                                    <th>No. of Pt</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-patient-receivable-details" role="tabpanel" aria-labelledby="pills-patient-receivable-details-tab">
                    <div class="table-responsive text-nowrap">
                        <table class="table patient-receivable-details-data-table display responsive nowrap table-bordered" width="100%">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>#</th>
                                    <th>Account Receivable Details</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="tab-pane fade" id="pills-patient-statuses" role="tabpanel" aria-labelledby="pills-patient-statuses-tab">
                    <div class="table-responsive text-nowrap">
                        <table class="table patient-statuses-data-table display responsive nowrap table-bordered" width="100%">
                            <thead>
                                <tr class="text-nowrap">
                                    <th>#</th>
                                    <th>Status</th>
                                    <th>No. of Pt</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
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
        var table = $('table'); // Select the table element
        // Check if DataTable is already initialized
        if (!$.fn.dataTable.isDataTable(table)) {
            // Initialize the DataTable
            $(document).on('click', '.tab', function () {
                var _this = $(this);
                if (_this.attr('data-tab-name') == 'total_collection') {
                    tabs(_this);
                    var table = $('.total-collection-data-table').DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/post-detailed-report')}}",
                            method: "POST",
                            data: {
                                _token: "{{csrf_token()}}",
                                tab: 'total_collection'
                            }
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                } else if (_this.attr('data-tab-name') == 'total_worked_rvus') {
                    tabs(_this);
                    var table = table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/detailed-report')}}",
                            tab: 'total_worked_rvus'
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                } else if (_this.attr('data-tab-name') == 'insurance_receivable_details') {
                    tabs(_this);
                    var table = table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/detailed-report')}}",
                            tab: 'insurance_receivable_details'
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                } else if (_this.attr('data-tab-name') == 'insurance_statuses') {
                    tabs(_this);
                    var table = table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/detailed-report')}}",
                            tab: 'insurance_statuses'
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                } else if (_this.attr('data-tab-name') == 'patient_receivable_details') {
                    tabs(_this);
                    var table = table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/detailed-report')}}",
                            tab: 'patient_receivable_details'
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                } else if (_this.attr('data-tab-name') == 'patient_statuses') {
                    tabs(_this);
                    var table = table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        ajax: {
                            url: "{{url('customer/detailed-report')}}",
                            tab: 'patient_statuses'
                        },
                        columns: [{
                                name: 'sr_no',
                                data: 'sr_no'
                            },
                            {
                                name: 'name',
                                data: 'name'
                            },
                            {
                                name: 'email',
                                data: 'email'
                            },
                            {
                                name: 'role',
                                data: 'role'
                            },
                            {
                                name: 'group',
                                data: 'group'
                            },
                            {
                                name: 'provider_npi',
                                data: 'provider_npi'
                            },
                            {
                                className: 'text-center',
                                name: 'actions',
                                data: 'actions'
                            }
                        ],
                        createdRow: function(row, data, dataIndex) {
                            var index = dataIndex + 1; // Start from 1
                            $('td', row).eq(0).text(index); // Update the first cell of the row
                        }
                    });
                }
            });
        } else {
            console.log("DataTable is already initialized.");
        }
        // After initializing DataTables, call feather.replace()
        table.on('draw', function() {
            feather.replace();
        });

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
                                    table.draw(false);
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
                                    table.draw(false);
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

        function tabs(_this) {
            $('.tab').each(function () {
                if ($(this).attr('data-tab-name') == _this.attr('data-tab-name')) {
                    _this.addClass('active');
                } else {
                    $(this).removeClass('active');
                }
            });
        }
    });
</script>
@endsection