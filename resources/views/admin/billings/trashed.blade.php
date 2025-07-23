@extends('layout.app')
@section('_styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/jquery-toast-plugin@1.3.2/dist/jquery.toast.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
    .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropdown-button {
        padding: 10px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #f9f9f9;
        min-width: 300px;
        max-height: 220px;
        /* Adjust height as needed */
        overflow-y: auto;
        box-shadow: 0px 8px 16px 0px rgba(0, 0, 0, 0.2);
        z-index: 1;
        padding: 12px 16px;
    }

    .dropdown-content label {
        display: block;
        padding: 8px 0;
    }

    .dropdown.open .dropdown-content {
        display: block;
    }

    #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.75) url("{{ asset('assets/img/icons/loader.gif') }}") no-repeat center center;
        z-index: 99999;
    }
    .butns {
        width: auto !important;
        display: flex !important;
        align-items: baseline;
    }
</style>
@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    <div class="toaster-badge">

    </div>
    <div id='loader'></div>
    <div class="row">
        <div class="col-12 d-flex justify-content-between">
            <div class="breadcrumb-list">
                <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Billings /</span> Trashed</h4>
            </div>
            <div class="butns">
            </div>
            <div class="butns">
                <form action="{{url('admin/billings/export')}}" method="POST" id="export-excel-form">
                    @csrf
                    <input type="hidden" name="input_received_date" id="input_received_date">
                    <input type="hidden" name="input_created_date" id="input_created_date">
                    <input type="hidden" name="input_claim_status" id="input_claim_status">
                    <a href="{{url('admin/billings')}}" class="btn btn-primary">Back</a>
                    <a href="#" class="btn btn-warning" id="delete-selected-bills">Delete</a>
                    <a href="#" class="btn btn-danger" id="delete-all-bills">Delete All</a>
                </form>
            </div>
        </div>
    </div>
    <!-- Responsive Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display responsive nowrap row-border order-column" width="100%">
                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th><input type="checkbox" id="check-all"></th>
                            <th>SNo</th>
                            <th>User</th>
                            <th>Received Date</th>
                            <th>Guarantor Account</th>
                            <th>Guarantor Name</th>
                            <th>Guarantor Address Line 1</th>
                            <th>Guarantor Address Line 2</th>
                            <th>Guarantor Address Line 3</th>
                            <th>Guarantor City</th>
                            <th>Guarantor State</th>
                            <th>Guarantor Zip</th>
                            <th>MRN</th>
                            <th>Patient Name</th>
                            <!-- <th>Patient Sex</th> -->
                            <th>Date of Birth</th>
                            <th>Department</th>
                            <th>POS</th>
                            <th>POS Type</th>
                            <th>Service Provider</th>
                            <!-- <th>Billing Provider</th> -->
                            <th>Provider NPI</th>
                            <th>Date of Service</th>
                            <th>Diagnosis Codes</th>
                            <th>Procedure Code</th>
                            <th>Procedure Name</th>
                            <th>RVU</th>
                            <th>Charge Amount</th>
                            <th>Procedure Qty</th>
                            <th>Modifiers</th>
                            <th>Payor Name</th>
                            <th>Plan Name</th>
                            <th>Subscriber Number</th>
                            <th>Subscriber Name</th>
                            <th>Subscriber DOB</th>
                            <th>Subscriber SSN</th>
                            <th>Group Number</th>
                            <th>Coverage Address</th>
                            <th>Coverage City</th>
                            <th>Coverage State</th>
                            <th>Coverage Zip</th>
                            <th>Coverage Phone 1</th>
                            <th>Coverage Phone 2</th>
                            <th>Submission Date</th>
                            <!-- <th class="text-center">Status</th>
                            <th class="text-center">Sub Status</th> -->
                            <th class="text-center">Claim Status</th>
                            <th>Primary Ins Received Date</th>
                            <!-- <th>Primary Received Date</th> -->
                            <th>Primary Amount</th>
                            <th>Primary Payment No</th>
                            <th>Primary Ins Payment Date</th>
                            <th>Primary Ins Payment Cleared</th>
                            <th>Primary Payment Type</th>
                            <th>AR Days</th>
                            <!-- <th>Secondary Ins Received Date</th> -->
                            <th>Secondary Payer Status</th>
                            <th>Secondary Amount</th>
                            <th>Secondary Payment No</th>
                            <th>Secondary Payment Date</th>
                            <th>Secondary Payment Cleared</th>
                            <th>Secondary Payment Type</th>
                            <th>Selfpay Status</th>
                            <th>Selfpay Amount</th>
                            <th>Selfpay Payment No</th>
                            <th>Selfpay Payment Date</th>
                            <th>Selfpay Payment Type</th>
                            <th>Claim Comments</th>
                            <th>Actions</th>
                        </tr>
                        </tr>
                    </thead>
                    <tbody>

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
<script src="https://cdn.jsdelivr.net/npm/jquery-toast-plugin@1.3.2/dist/jquery.toast.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    $(document).ready(function() {
        @if(session('success'))
        $.toast('Successfull');
        @elseif(session('error'))
        $.toast('Something went wrong');
        @endif
        
        var table = $('table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            fixedColumns: {
                start: 2
            },
            scrollCollapse: true,
            ajax: {
                url: "{{url('admin/billings/get-trashed-data')}}",
                method: 'POST',
                data: function(d) {
                    d._token = "{{csrf_token()}}";
                    d.received_date = $('#received_date').val();
                    d.claim_status = $('#claim_status').val();
                    d.created_at = $('#created_at').val();
                }
            },
            columns: [{
                    name: 'sr_no',
                    data: 'sr_no'
                },
                {
                    name: 'checkbox',
                    data: 'checkbox',
                    orderable: false,
                },
                {
                    name: 'sno',
                    data: 'sno'
                },
                {
                    name: 'users',
                    data: 'users'
                },
                {
                    name: 'received_date',
                    data: 'received_date'
                },
                {
                    name: 'guarantor_account',
                    data: 'guarantor_account'
                },
                {
                    name: 'guarantor_name',
                    data: 'guarantor_name'
                },
                {
                    name: 'guarantor_address_line_1',
                    data: 'guarantor_address_line_1'
                },
                {
                    name: 'guarantor_address_line_2',
                    data: 'guarantor_address_line_2'
                },
                {
                    name: 'guarantor_address_line_3',
                    data: 'guarantor_address_line_3'
                },
                {
                    name: 'guarantor_city',
                    data: 'guarantor_city'
                },
                {
                    name: 'guarantor_state',
                    data: 'guarantor_state'
                },
                {
                    name: 'guarantor_zip',
                    data: 'guarantor_zip'
                },
                {
                    name: 'mrn',
                    data: 'mrn'
                },
                {
                    name: 'patient_name',
                    data: 'patient_name'
                },
                // {
                //     name: 'patient_sex',
                //     data: 'patient_sex'
                // },
                {
                    name: 'date_of_birth',
                    data: 'date_of_birth'
                },
                {
                    name: 'department',
                    data: 'department'
                },
                {
                    name: 'pos',
                    data: 'pos'
                },
                {
                    name: 'pos_type',
                    data: 'pos_type'
                },
                {
                    name: 'service_provider',
                    data: 'service_provider'
                },
                {
                    name: 'provider_npi',
                    data: 'provider_npi'
                },
                // {
                //     name: 'billing_provider',
                //     data: 'billing_provider'
                // },
                {
                    name: 'date_of_service',
                    data: 'date_of_service'
                },
                {
                    name: 'diagnosis_codes',
                    data: 'diagnosis_codes'
                },
                {
                    name: 'procedure_code',
                    data: 'procedure_code'
                },
                {
                    name: 'procedure_name',
                    data: 'procedure_name'
                },
                {
                    name: 'rvu',
                    data: 'rvu'
                },
                {
                    name: 'charge_amount',
                    data: 'charge_amount'
                },
                {
                    name: 'procedure_qty',
                    data: 'procedure_qty'
                },
                {
                    name: 'modifiers',
                    data: 'modifiers'
                },
                {
                    name: 'payor_name',
                    data: 'payor_name'
                },
                {
                    name: 'plan_name',
                    data: 'plan_name'
                },
                {
                    name: 'subscriber_number',
                    data: 'subscriber_number'
                },
                {
                    name: 'subscriber_name',
                    data: 'subscriber_name'
                },
                {
                    name: 'subscriber_dob',
                    data: 'subscriber_dob'
                },
                {
                    name: 'subscriber_ssn',
                    data: 'subscriber_ssn'
                },
                {
                    name: 'group_number',
                    data: 'group_number'
                },
                {
                    name: 'coverage_address',
                    data: 'coverage_address'
                },
                {
                    name: 'coverage_city',
                    data: 'coverage_city'
                },
                {
                    name: 'coverage_state',
                    data: 'coverage_state'
                },
                {
                    name: 'coverage_zip',
                    data: 'coverage_zip'
                },
                {
                    name: 'coverage_phone1',
                    data: 'coverage_phone1'
                },
                {
                    name: 'coverage_phone2',
                    data: 'coverage_phone2'
                },
                {
                    name: 'submission_date',
                    data: 'submission_date'
                },
                // {
                //     name: 'status',
                //     data: 'status'
                // },
                // {
                //     name: 'sub_status',
                //     data: 'sub_status'
                // },
                {
                    name: 'claim_status',
                    data: 'claim_status'
                },
                {
                    name: 'primary_ins_received_date',
                    data: 'primary_ins_received_date'
                },
                // {
                //     name: 'primary_received_date',
                //     data: 'primary_received_date'
                // },
                {
                    name: 'primary_amount',
                    data: 'primary_amount'
                },
                {
                    name: 'primary_payment_no',
                    data: 'primary_payment_no'
                },
                {
                    name: 'primary_ins_payment_date',
                    data: 'primary_ins_payment_date'
                },
                {
                    name: 'primary_ins_payment_cleared',
                    data: 'primary_ins_payment_cleared'
                },
                {
                    name: 'primary_payment_type',
                    data: 'primary_payment_type'
                },
                {
                    name: 'ar_days',
                    data: 'ar_days'
                },
                // {
                //     name: 'secondary_ins_received_date',
                //     data: 'secondary_ins_received_date'
                // },
                {
                    name: 'secondary_payer_status',
                    data: 'secondary_payer_status'
                },
                {
                    name: 'secondary_amount',
                    data: 'secondary_amount'
                },
                {
                    name: 'secondary_payment_no',
                    data: 'secondary_payment_no'
                },
                {
                    name: 'secondary_payment_date',
                    data: 'secondary_payment_date'
                },
                {
                    name: 'secondary_payment_cleared',
                    data: 'secondary_payment_cleared'
                },
                {
                    name: 'secondary_payment_type',
                    data: 'secondary_payment_type'
                },
                {
                    name: 'selfpay_status',
                    data: 'selfpay_status'
                },
                {
                    name: 'selfpay_amount',
                    data: 'selfpay_amount'
                },
                {
                    name: 'selfpay_payment_no',
                    data: 'selfpay_payment_no'
                },
                {
                    name: 'selfpay_payment_date',
                    data: 'selfpay_payment_date'
                },
                {
                    name: 'selfpay_payment_type',
                    data: 'selfpay_payment_type'
                },
                {
                    name: 'claim_comments',
                    data: 'claim_comments'
                },
                {
                    className: 'text-center',
                    name: 'actions',
                    data: 'actions'
                },
            ],
            createdRow: function(row, data, dataIndex) {
                var index = dataIndex + 1; // Start from 1
                $('td', row).eq(0).text(index); // Update the first cell of the row
            }
        });

        window._table = table;

        // After initializing DataTables, call feather.replace()
        table.on('draw', function() {
            feather.replace();
        });

        $('#received_date, #claim_status, #created_at').on('change', function() {
            table.draw(false);
        });

        $(document).on('click', '#export-excel', function() {
            $(this).addClass('disabled');
        });


        $(document).on('click', '#check-all', function() {
            $('.bill-checkbox').each(function() {
                $(this).prop('checked', $(this).is(':checked') ? false : true);
                $('#delete-selected-bills').addClass('disabled');
                $('.bill-checkbox:checked').each(function() {
                    $('#delete-selected-bills').removeClass('disabled');
                });
            });
        });

        $(document).on('click', '.bill-checkbox', function() {
            if ($(this).prop('checked')) {
                $('#delete-selected-bills').removeClass('disabled');
            } else {
                var checkedRemaining = 0;
                $('.bill-checkbox').each(function() {
                    if ($(this).prop('checked')) {
                        checkedRemaining = 1;
                    }
                });
                if (checkedRemaining === 0) {
                    $('#delete-selected-bills').addClass('disabled');
                }
            }
        });

        $(document).on('click', '#delete-all-bills', function() {
            Swal.fire({
                title: 'Are you sure?',
                title: 'Are you sure to DELETE all?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: 'green',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('permanent_delete_multiple_bills')}}",
                        method: 'POST',
                        data: {
                            _token: "{{csrf_token()}}",
                            bill_ids: [],
                            delete_all: "delete_all"
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
                                    'Failed to delete selected bills.',
                                    'error'
                                );
                            }
                        }
                    });
                }
            });
        });

        $(document).on('click', '#delete-selected-bills', function() {
            var selectedBills = [];
            $('.bill-checkbox:checked').each(function() {
                selectedBills.push($(this).attr('data-bill-id'));
            });
            if (selectedBills.length > 0) {
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
                            url: "{{route('permanent_delete_multiple_bills')}}",
                            method: 'POST',
                            data: {
                                _token: "{{csrf_token()}}",
                                bill_ids: selectedBills,
                                delete_all: ""
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
                                        'Failed to delete selected bills.',
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                });
            } else {
                Swal.fire(
                    'No bill selected',
                    'Please select at least one bill to delete.',
                    'info'
                );
            }
        });

        $(document).on('click', '.delete-bill', function() {
            var billId = $(this).attr('data-bill-id');
            if (billId != '') {
                Swal.fire({
                    title: 'Are you sure to DELETE?',
                    text: "",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: 'green',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{route('admin.bill.permanent_delete')}}",
                            method: 'POST',
                            data: {
                                _token: "{{csrf_token()}}",
                                bill_id: billId
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
                    'No bill selected',
                    'Please select at least one bill to delete.',
                    'info'
                );
            }
        });

        setTimeout(() => {
            $('#export-excel').removeClass('disabled');
        }, 30000);
    });
</script>
@include('admin.billings._js')
@endsection