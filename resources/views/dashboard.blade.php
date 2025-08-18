@extends('layout.app')
@section('_styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<!-- Moment.js (required for formatting) -->
<script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

<!-- Date Range Picker CSS + JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

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
            <div class="mb-4">
                <form action="{{ route('admin.dashboard') }}" method="GET">
                    <div class="row mb-2">
                        <div class="col-md-4">
                            <label for="dateRangePicker">Filter Date</label>
                            <input 
                                type="text" 
                                name="date_range" 
                                id="dateRangePicker" 
                                class="form-control" 
                                placeholder="Select Date Range"
                                value="{{ request('date_range') }}"
                            >
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <input type="submit" class="btn btn-primary">
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-success"></i> Total Sale</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($salesSummary) ? number_format($salesSummary->total_sales, 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Expenses</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format($expensesSummary->total_expenses, 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-warning"></i> Total Payment Received</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format(($salesSummary->total_sales - $expensesSummary->total_expenses), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-primary"></i> Online Payment Received</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($salesSummary) ? number_format(($salesSummary->total_online_payment_received), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Cash Expense</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_cash_expenses), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Online Expense</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_online_expenses), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Discounts</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($salesSummary) ? number_format($salesSummary->total_discounts) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-car text-primary"></i> Total Car Washed</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalWashedCars) ? number_format($totalWashedCars->count()) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-group text-info"></i> Customers</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalNoOfCustomers) ? number_format($totalNoOfCustomers) : 0}}</h3>
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
        $(function() {
            $('#dateRangePicker').daterangepicker({
                autoUpdateInput: false,     // don’t fill until user selects
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD'    // format for backend
                }
            });

            // Set the selected value into the input
            $('#dateRangePicker').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
            });

            // Clear on cancel
            $('#dateRangePicker').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
        });
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection