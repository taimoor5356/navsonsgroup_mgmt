@extends('layout.app')
@section('_styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
@endsection
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-12 d-flex justify-content-between">
            <div class="breadcrumb-list">
                <h4 class="fw-bold py-3 mb-4"><span class="text-dark fw-light">{{$header_title}}</span></h4>
            </div>
            <div class="butns">
                <a href="{{url('admin/expenses/create')}}" class="btn btn-primary">Add New</a>
                <a href="{{url('admin/expenses/expense-names/create')}}" class="btn btn-danger">Add New Expense Names</a>
            </div>
        </div>
    </div>
    <!-- Responsive Table -->
    <div class="card">
        <div class="card-header">
            <a href="{{url('admin/expenses/expense-names/list')}}" class="btn btn-primary">View Expense Names</a>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive text-nowrap">
                <table class="table data-table display responsive nowrap" width="100%">
                    <thead>
                        <tr class="text-nowrap">
                            <th>#</th>
                            <th>Date</th>
                            <th>Expense Type</th>
                            <th>Expense Name</th>
                            <th>Total Expense</th>
                            <th>Description</th>
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
<script>

$(document).ready(function() {
    $(document).ready(function() {
        var table = $('table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            ajax: {
                url: "{{url('admin/accounts/expenses/list')}}",
            },
            pageLength: 10,       // show 10 records
            lengthChange: false,   // hide "Show X entries" dropdown
            order: [], // 👈 important: disable client-side ordering
            // paging: false,         // disable pagination
            // info: false,            // hide "Showing X of Y entries"
            columns: [{
                    name: 'sr_no',
                    data: 'sr_no',
                    orderable: false
                },
                {
                    name: 'date',
                    data: 'date'
                },
                {
                    name: 'expense_type',
                    data: 'expense_type'
                },
                {
                    name: 'expense_name',
                    data: 'expense_name'
                },
                {
                    name: 'expense_amount',
                    data: 'expense_amount'
                },
                {
                    name: 'description',
                    data: 'description'
                },
                {
                    className: 'text-center',
                    name: 'actions',
                    data: 'actions',
                    orderable: false
                },
            ],
            createdRow: function(row, data, dataIndex) {
                var info = table.page.info(); 
                var index = info.start + dataIndex + 1; // 👈 offset by current page
                $('td', row).eq(0).text(index);
            }
        });
        // After initializing DataTables, call feather.replace()
        table.on('draw', function() {
            feather.replace();
        });
    });
});
</script>
@endsection