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
        <div class="card-header">
            <div class="row">
                <div class="col-md-4 col-sm-6 col-6 mb-2">
                    <label for="dateRangePicker">Date Filter</label>
                    <input 
                        type="text" 
                        name="date_range" 
                        id="dateRangePicker" 
                        class="form-control" 
                        placeholder="Filter Dates"
                        value="{{ request('date_range') }}"
                    >
                </div>
                <div class="col-md-4 col-sm-6 col-6 mb-2">
                    <label for="payment-status">Payment Filter</label>
                    <select class="form-control" id="payment-status">
                        <option value="all" selected>Show All</option>
                        <option value="paid">Paid</option>
                        <option value="unpaid">Un-Paid</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 col-sm-2 col-12 mb-2">
                    <button class="btn btn-xs btn-primary rounded refresh-table p-2"><i class="text-white" data-feather="refresh-cw"></i></button>
                </div>
            </div>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display responsive nowrap" width="100%">
                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>Date</th>
                            <th>Payment</th>
                            <th>Vehicle Number</th>
                            <th>Service Type</th>
                            <th>Charges</th>
                            <th>Collected Amount</th>
                            <th>Discount</th>
                            <th>Description</th>
                            <th>Payment Mode</th>
                            <th>Phone</th>
                            <th>Luster</th>
                            <th>Polish</th>
                            <th>Vaccum</th>
                            <th>Diesel</th>
                            <th>Complain</th>
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
                    data: function (d) {
                        d.date_range = $('#dateRangePicker').val();
                        d.payment_status = $('#payment-status').val();
                    }
                },
                pageLength: 25,       // show 100 records
                lengthChange: false,   // hide "Show X entries" dropdown
                order: [], // 👈 important: disable client-side ordering
                columns: [{
                        name: 'sr_no',
                        data: 'sr_no',
                        orderable: false
                    },
                    {
                        name: 'date',
                        data: 'date',
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
                        name: 'service_type',
                        data: 'service_type'
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
                        name: 'luster',
                        data: 'luster'
                    },
                    {
                        name: 'polish',
                        data: 'polish'
                    },
                    {
                        name: 'vaccum',
                        data: 'vaccum'
                    },
                    {
                        name: 'diesel',
                        data: 'diesel'
                    },
                    {
                        name: 'complaint',
                        data: 'complaint'
                    },
                    {
                        className: 'text-center',
                        name: 'actions',
                        data: 'actions',
                        orderable: false
                    }
                ],
                createdRow: function(row, data, dataIndex) {
                    var info = table.page.info(); 
                    var index = info.start + dataIndex + 1; // 👈 offset by current page
                    $('td', row).eq(0).text(index);
                }
            });
        } else {
            console.log("DataTable is already initialized.");
        }
        // After initializing DataTables, call feather.replace()
        table.on('draw', function() {
            feather.replace();
        });

        $('.refresh-table').on('click', function() {
            table.draw(false); // redraw the table without resetting pagination
        });

        $(document).on('change', '#payment-status', function() {
            table.draw(false); // redraw the table with new filter
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

        @if (Auth::user()->hasRole(['admin', 'manager']))
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
            @if (Auth::user()->hasRole(['admin', 'manager']))
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
            @endif
            setTimeout(() => {
                table.draw(false);
            }, 1000);
        });
        @endif

        $(document).on('click', '.complain-checkbox', function() {
            var _this = $(this);
            var _serviceId = _this.attr('data-service-id');
            @if (Auth::user()->hasRole(['admin', 'manager']))
            $.ajax({
                url: "{{ route('admin.services.complaint') }}",
                method: "POST",
                data: {
                    _token: "{{csrf_token()}}",
                    service_id: _serviceId
                },
                success:function(response) {
                    if (response.status == true) {
                        table.draw(false);
                    }
                }
            });
            @endif
        });

        $(document).on('change', '.luster-service, .vaccum-service, .diesel-service, .complain, .payment-mode', function() {
            var _this = $(this);
            var _serviceId = _this.data('service-id');

            // detect which field triggered
            var fieldName = '';
            if (_this.hasClass('luster-service')) fieldName = 'luster';
            else if (_this.hasClass('vaccum-service')) fieldName = 'vaccum';
            else if (_this.hasClass('diesel-service')) fieldName = 'diesel';
            else if (_this.hasClass('complain')) fieldName = 'complain';
            else if (_this.hasClass('payment-mode') || _this.hasClass('payment-mode-update')) fieldName = 'payment_mode_id';

            // value (for checkbox toggle use checked, for select use val)
            var fieldValue = (_this.is(':checkbox')) ? (_this.is(':checked') ? 1 : 0) : _this.val();

            @if (Auth::user()->hasRole(['admin', 'manager']))
            $.ajax({
                url: "{{ route('admin.services.update_additional_services') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    service_id: _serviceId,
                    field: fieldName,
                    value: fieldValue
                },
                success: function(response) {
                    if (response.status === true) {
                        table.draw(false);
                    }
                }
            });
            @endif
        });
    });
</script>
@endsection