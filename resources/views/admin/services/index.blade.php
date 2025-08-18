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
                <h4 class="fw-bold py-3 mb-4"><span class="text-dark fw-light">{{$header_title}}</span></h4>
            </div>
            @if (Auth::user()->hasRole(['admin', 'manager']))
            <div class="butns">
                <a href="{{url('admin/services/create')}}" class="btn btn-primary ms-2">Add New</a>
            </div>
            @endif
        </div>
    </div>
    <!-- Responsive Table -->
    <div class="card p-0">
        <div class="card-body p-2">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display responsive nowrap" width="100%">
                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>Payment Status</th>
                            <th>Vehicle Registration Number</th>
                            <th>Charges</th>
                            <th>Collected Amount</th>
                            <th>Discount</th>
                            <th>Discount Reason</th>
                            <th>Payment Mode</th>
                            <th>Phone</th>
                            <th>Actions</th>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('table'); // Select the table element

        // Check if DataTable is already initialized
        if (!$.fn.dataTable.isDataTable(table)) {
            // Initialize the DataTable
            var table = table.DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: "{{route('admin.services.list')}}",
                },
                pageLength: 100,       // show 100 records
                lengthChange: false,   // hide "Show X entries" dropdown
                // paging: false,         // disable pagination
                // info: false,            // hide "Showing X of Y entries"
                columns: [{
                        name: 'sr_no',
                        data: 'sr_no'
                    },
                    {
                        name: 'payment_status',
                        data: 'payment_status',
                    },
                    {
                        name: 'vehicle_registration_number',
                        data: 'vehicle_registration_number'
                    },
                    {
                        name: 'charges',
                        data: 'charges'
                    },
                    {
                        name: 'collected_amount',
                        data: 'collected_amount'
                    },
                    {
                        name: 'discount',
                        data: 'discount'
                    },
                    {
                        name: 'discount_reason',
                        data: 'discount_reason'
                    },
                    {
                        name: 'payment_mode',
                        data: 'payment_mode'
                    },
                    {
                        name: 'phone',
                        data: 'phone'
                    },
                    {
                        className: 'text-center',
                        name: 'actions',
                        data: 'actions'
                    }
                ],
                order: [[0, 'desc']], // 👈 Default order: first column descending
                createdRow: function(row, data, dataIndex) {
                    var index = dataIndex + 1; // Start from 1
                    $('td', row).eq(0).text(index); // Update the first cell of the row
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


        $(document).on('change', '.payment-toggle', function() {
            let checkbox = $(this);
            let id = checkbox.data('id');
            let newStatus = checkbox.is(':checked') ? 1 : 0;

            // Show confirm alert
            let confirmed = false;
            if (newStatus === 1) {
                confirmed = confirm("Do you want to set as Paid?");
            } else {
                confirmed = confirm("Do you want to set as Un-Paid?");
            }

            // If user cancelled → revert toggle and exit
            if (!confirmed) {
                checkbox.prop('checked', !newStatus); 
                return;
            }

            // If confirmed → send AJAX
            $.ajax({
                url: 'update-payment-status/' + id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: newStatus
                },
                success: function(response) {
                    if (response.status == true) {
                        checkbox.prop('checked', newStatus); // keep as user intended
                    } else {
                        checkbox.prop('checked', !newStatus); // revert if backend rejected
                        toastr.error('Failed to update status.');
                    }
                },
                error: function() {
                    checkbox.prop('checked', !newStatus); // revert on error
                    toastr.error('Something went wrong.');
                }
            });
            setTimeout(() => {
                table.draw(false);
            }, 1000);
        });


    });
</script>
@endsection