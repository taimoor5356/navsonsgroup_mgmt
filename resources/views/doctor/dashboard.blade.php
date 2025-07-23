@extends('layout.app')
@section('_styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.dataTables.min.css">
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
        <div class="col-lg-12 mb-4">
            <div class="card p-4">
                <form action="">
                    <div class="row mb-3">
                        @role('admin customer')
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                            <label for="">Select Customer</label>
                            <br>
                            <select name="provider_npi" id="provider_npi" value="{{Request::get('provider_npi')}}" class="form-control">
                                <option value="all" selected>All</option>
                                @if (!empty($customers))
                                    @foreach ($customers as $customer)
                                        <option value="{{$customer->provider_npi}}" {{(!empty(Request::get('provider_npi')) && Request::get('provider_npi') == $customer->provider_npi) ? 'selected' : ''}}>{{$customer->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        @endrole
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                            <label for="">Start Date</label>
                            <br>
                            <input type="text" name="date_range" value="{{Request::get('date_range')}}" id="date-range" class="form-control date_range_picker">
                        </div>
                        <!-- <div class="col-xl-3 col-lg-3 col-md-3 col-sm-12 col-12">
                            <label for="">End Date</label>
                            <br>
                            <input type="date" name="end_date" value="{{Request::get('end_date')}}" id="end_date" class="form-control">
                        </div> -->
                    </div>
                    <!-- <div class="row">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
                            <button type="submit" class="btn btn-primary">Apply</button>
                            <a href="{{url('admin/dashboard')}}" type="submit" class="btn btn-danger">Clear All</a>
                        </div>
                    </div> -->
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 order-1">
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1">Donut</span>
                            <canvas id="myDonutChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1">Bar</span>
                            <canvas id="myBarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 order-1">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive text-nowrap">
                                <table class="table data-table display responsive nowrap" width="100%">
                                    <thead>
                                        <tr class="text-nowrap">
                                            <th>#</th>
                                            <th class="text-start">Date</th>
                                            <th class="text-start">Total</th>
                                            <th class="text-start">Fully Paid</th>
                                            <th class="text-start">In Process</th>
                                            <th class="text-start">Paid PR</th>
                                            <th class="text-start">Secondary In-Process</th>
                                            <th class="text-start">Selfpay</th>
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
        </div>
    </div>
</div>

@endsection

@section('_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script>
    var ctxDonutChart = document.getElementById('myDonutChart').getContext('2d');
    var myDonutChart;

    // Function to create or update the chart
    function createOrUpdateDonutChart(data) {
        // If the chart already exists, destroy it first
        if (myDonutChart) {
            myDonutChart.destroy();
        }

        // Extract data for the chart
        var fullyPaid = data.fully_paid;
        var paidPrimary = data.paid_pr;
        var paidSecondary = data.paid_sec;
        var selfPay = data.self_pay;

        // Create a new chart
        myDonutChart = new Chart(ctxDonutChart, {
            type: 'doughnut',
            data: {
                labels: ['Fully Paid', 'PaidPR', 'Secondary In-Process', 'SelfPay'],
                datasets: [{
                    label: 'Billing Data',
                    data: [
                        fullyPaid,
                        paidPrimary,
                        paidSecondary,
                        selfPay,
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    }

    // Function to handle date change and fetch new data
    function fetchDataAndUpdateDonutChart() {
        var startDate = $('#date-range').val();
        // var endDate = $('#end_date').val();

        // Send AJAX request to server
        $.ajax({
            url: "{{route('customer.table_data')}}", // Replace with your server endpoint
            method: 'GET',
            data: {
                date_range: startDate,
                // end_date: endDate, 
                graph_data: 'true'
            },
            success: function(response) {
                // Assuming response contains an array of data for the chart
                var newData = response.data.pieChart; // Adapt according to your response structure
                createOrUpdateDonutChart(newData);
            },
            error: function(error) {
                console.error('Error fetching data:', error);
            }
        });
    }

    var ctxBarChart = document.getElementById('myBarChart').getContext('2d');
    var myBarChart;

    // Function to create or update the chart
    function createOrUpdateBarChart(data) {
        // If the chart already exists, destroy it first
        if (myBarChart) {
            myBarChart.destroy();
        }

        // Extract data for the chart
        var fullyPaid = data.paid;
        var rebilled = data.rebilled;
        var inProcess = data.inProcess;
        var infoRequired = data.infoRequired;
        var patient = data.patientResponsibility;
        var partiallyPaid = data.partiallyPaid;
        var dropped = data.dropped;
        var duplicate = data.duplicate;
        var denied = data.denied;

        // Create a new chart
        myBarChart = new Chart(ctxBarChart, {
            type: 'bar', // The type of chart we want to create
            data: {
                labels: ['Paid', 'Rebilled', 'In Process', 'Info Required', 'Patient', 'Partially Paid', 'Dropped', 'Dupliicate', 'Denied'],
                datasets: [{
                    label: '',
                    data: [
                        fullyPaid,
                        rebilled,
                        inProcess,
                        infoRequired,
                        patient,
                        partiallyPaid,
                        dropped,
                        duplicate,
                        denied
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });
    }
    // Function to handle date change and fetch new data
    function fetchDataAndUpdateBarChart() {
        var startDate = $('#date-range').val();
        // var endDate = $('#end_date').val();

        // Send AJAX request to server
        $.ajax({
            url: "{{route('customer.table_data')}}", // Replace with your server endpoint
            method: 'GET',
            data: {
                date_range: startDate,
                // end_date: endDate, 
                graph_data: 'true'
            },
            success: function(response) {
                // Assuming response contains an array of data for the chart
                var newData = response.data.barChart; // Adapt according to your response structure
                console.log(newData);
                
                createOrUpdateBarChart(newData);
            },
            error: function(error) {
                console.error('Error fetching data:', error);
            }
        });
    }
    
    $(document).ready(function() {
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            paging: false,
            bLengthChange: false,
            info: false,
            ajax: {
                url: "{{route('customer.table_data')}}",
                data: function (d) {
                    d.date_range = $('#date-range').val();
                    // d.end_date = $('#end_date').val();
                }
            },
            columns: [
                {
                    name: 'sno',
                    data: 'sno'
                },
                {
                    name: 'date',
                    data: 'date'
                },
                {
                    name: 'total',
                    data: 'total'
                },
                {
                    name: 'fully_paid',
                    data: 'fully_paid'
                },
                {
                    name: 'in_process',
                    data: 'in_process'
                },
                {
                    name: 'paid_primary',
                    data: 'paid_primary'
                },
                {
                    name: 'paid_secondary',
                    data: 'paid_secondary'
                },
                {
                    name: 'self_pay',
                    data: 'self_pay'
                }
            ],
            createdRow: function(row, data, dataIndex) {
                var index = dataIndex + 1; // Start from 1
                $('td', row).eq(0).text(index); // Update the first cell of the row
            }
        });

        $(document).on('change', '#date-range', function() {
            table.draw(false);
            fetchDataAndUpdateDonutChart();
            fetchDataAndUpdateBarChart();
        });

        $('.dateSelect').select2();

        $(document).on('change', '#group_id', function() {
            var url = "{{route('filter_all_data')}}";
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{csrf_token()}}",
                    group_id: $('#group_id').val(),
                    provider_npi: '',
                    location: '',
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#provider_npi').html(response.customers);
                        $('#location').html(response.locations);
                        $('#mrn_number').html(response.mrn_numbers);
                    }
                }
            });
        });

        $(document).on('change', '#provider_npi', function() {
            var url = "{{route('filter_all_data')}}";
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{csrf_token()}}",
                    group_id: $('#group_id').val(),
                    provider_npi: $('#provider_npi').val(),
                    location: '',
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#location').html(response.locations);
                        $('#mrn_number').html(response.mrn_numbers);
                    }
                }
            });
        });

        $(document).on('change', '#location', function() {
            var url = "{{route('filter_all_data')}}";
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: "{{csrf_token()}}",
                    group_id: '',
                    provider_npi: $('#provider_npi').val(),
                    location: $('#location').val(),
                },
                success: function(response) {
                    if (response.status == true) {
                        $('#mrn_number').html(response.mrn_numbers);
                    }
                }
            });
        });

        var today = new Date();
        var last30Days = new Date();
        last30Days.setDate(today.getDate() - 30);

        $('.date_range_picker').daterangepicker({
          autoUpdateInput: false,      
          locale: {
              cancelLabel: 'Clear'
          }
        }).on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate ? picker.startDate.format('YYYY/MM/DD') : '';
            var endDate = picker.endDate ? picker.endDate.format('YYYY/MM/DD') : '';
            $(this).val(startDate + ' - ' + endDate).trigger('change');
        }).on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('').trigger('change');
        });
        $('.date_range_picker').val('');
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endsection