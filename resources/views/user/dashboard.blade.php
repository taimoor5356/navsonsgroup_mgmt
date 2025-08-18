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
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-success"></i> Total Salary</span>
                            <h3 class="card-title text-nowrap mb-2">Rs 25,000</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Loan</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{ number_format($record->expenses->sum('amount'), 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-warning"></i> Salary Remaining</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{number_format((25000 - $record->expenses->sum('amount')), 2)}}</h3>
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