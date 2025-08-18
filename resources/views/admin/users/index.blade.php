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
            @if (request()->route('type') == 'users')
            @if (Auth::user()->hasRole(['admin']))
            <div class="butns">
                <a href="{{url('admin/users/create')}}" class="btn btn-primary ms-2">Add New</a>
            </div>
            @endif
            @endif
        </div>
    </div>
    <!-- Responsive Table -->
    <div class="card">
        <div class="card-body p-2">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display responsive nowrap" width="100%">
                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            @if(request()->route('type') == 'users')<th>Total Salary</th>@endif
                            @if(request()->route('type') == 'users')<th>Loan Amount</th>@endif
                            @if(request()->route('type') == 'users')<th>Salary Left</th>@endif
                            @if(request()->route('type') == 'customers')<th>Total Services</th>@endif
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
        $(document).ready(function() {
            var table = $('table'); // Select the table element

            // Check if DataTable is already initialized
            if (!$.fn.dataTable.isDataTable(table)) {
                // Initialize the DataTable
                var url = "{{route('admin.users.list', [':usertype'])}}";
                url = url.replace(':usertype', "{{request()->route('type')}}");
                
                var table = table.DataTable({
                    processing: true,
                    serverSide: true,
                    scrollX: true,
                    ajax: {
                        url: url,
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
                            name: 'name',
                            data: 'name'
                        },
                        {
                            name: 'email',
                            data: 'email'
                        },
                        @if(request()->route('type') == 'users')
                        {
                            name: 'total_salary',
                            data: 'total_salary'
                        },
                        @endif
                        @if(request()->route('type') == 'users')
                        {
                            name: 'loan',
                            data: 'loan'
                        },
                        @endif
                        @if(request()->route('type') == 'users')
                        {
                            name: 'salary_left',
                            data: 'salary_left'
                        },
                        @endif
                        @if(request()->route('type') == 'customers')
                        {
                            name: 'total_services',
                            data: 'total_services'
                        },
                        @endif
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
        });
    });
</script>
@endsection