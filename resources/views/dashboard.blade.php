@extends('layout.app')
@section('_styles')

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
                            <input type="submit" class="btn btn-primary btn-sm">
                        </div>
                    </div>
                </form>
            </div>
            <div class="row">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body d-flex justify-content-between align-items-start">
                            <div>
                                <span class="d-block mb-1">
                                    <i class="menu-icon tf-icons bx bx-money text-success"></i> Total Sale
                                </span>
                                <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{ !empty($salesSummary) ? number_format($salesSummary->total_sales, 2) : 0 }}">
                                    Rs {{ !empty($salesSummary) ? number_format($salesSummary->total_sales, 2) : 0 }}
                                </h3>
                                @php
                                    // Calculate average sales per day
                                    $dateRange = request('date_range');
                                    $query = \App\Models\Service::query();
                                    if ($dateRange) {
                                        [$start, $end] = explode(' - ', $dateRange);
                                        $start = \Carbon\Carbon::parse($start)->startOfDay();
                                        $end   = \Carbon\Carbon::parse($end)->endOfDay();
                                        $daysDiff = $start->diffInDays($end) + 1;
                                    } else {
                                        $firstService = \App\Models\Service::orderBy('created_at')->first();
                                        $start = $firstService ? \Carbon\Carbon::parse($firstService->created_at)->startOfDay() : now()->startOfDay();
                                        $end = now()->endOfDay();
                                        $daysDiff = $start->diffInDays($end) + 1;
                                    }
                                    $totalSales = $query->when($dateRange, fn($q) => $q->whereBetween('created_at', [$start, $end]))->sum('collected_amount');
                                    $avgPerDay = $daysDiff > 0 ? number_format($totalSales / $daysDiff, 2) : 0;
                                @endphp
                                <small>(Avg/Day: {{ $avgPerDay }})</small>
                                <br>
                                <a href="{{ route('admin.services.list') }}" class="btn btn-success rounded btn-xs">View More</a>
                            </div>

                            <!-- Scrollable text at right side -->
                            <div style="padding: 5px; max-height:120px; overflow-y:auto; font-size:9px; margin-left:15px; white-space:nowrap; scrollbar-width: thin; scrollbar-color: #ccc transparent;">
                                @foreach ($dayWiseSale as $day)
                                    <div>{{ \Carbon\Carbon::parse($day->sale_date)->format('d, M') }}) Rs {{ number_format($day->total_sales) }}</div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Expenses</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($expensesSummary) ? number_format($expensesSummary->total_expenses, 2) : 0}}">Rs {{!empty($expensesSummary) ? number_format($expensesSummary->total_expenses, 2) : 0}}</h3>
                            @php
                                $dateRange = request('date_range');
                                $query = \App\Models\Expense::query();

                                if ($dateRange) {
                                    [$start, $end] = explode(' - ', $dateRange);
                                    $start = \Carbon\Carbon::parse($start)->startOfDay();
                                    $end   = \Carbon\Carbon::parse($end)->endOfDay();
                                }
                            @endphp

                            <small>
                                (Salaries: 
                                {{ number_format(
                                    \App\Models\Expense::where('expense_type_id', 2)
                                        ->when($dateRange, fn($q) => $q->whereBetween('created_at', [$start, $end]))
                                        ->sum('amount'), 2)
                                }})
                            </small><br>

                            <small>
                                (Carwash Expenses: 
                                {{ number_format(
                                    \App\Models\Expense::where('expense_type_id', '!=', 2)
                                        ->when($dateRange, fn($q) => $q->whereBetween('created_at', [$start, $end]))
                                        ->sum('amount'), 2)
                                }})
                            </small><br>
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-warning"></i> Total Payment Received</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($expensesSummary) ? number_format(($salesSummary->total_sales - $expensesSummary->total_expenses), 2) : 0}}">Rs {{!empty($expensesSummary) ? number_format(($salesSummary->total_sales - $expensesSummary->total_expenses), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-primary"></i> Online Payment Received</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($salesSummary) ? number_format(($salesSummary->total_online_payment_received), 2) : 0}}">Rs {{!empty($salesSummary) ? number_format(($salesSummary->total_online_payment_received), 2) : 0}}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Cash Expense</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_cash_expenses), 2) : 0}}">Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_cash_expenses), 2) : 0}}</h3>
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Online Expense</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_online_expenses), 2) : 0}}">Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_online_expenses), 2) : 0}}</h3>
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Discounts</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="Rs {{!empty($salesSummary) ? number_format($salesSummary->total_discounts, 2) : 0}}">Rs {{!empty($salesSummary) ? number_format($salesSummary->total_discounts, 2) : 0}}</h3>
                            <a href="{{route('admin.services.list')}}" class="btn btn-warning rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-car text-primary"></i> Total Vehicles Washed</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="{{!empty($totalWashedCars) ? $totalWashedCars->count() : 0}}">{{!empty($totalWashedCars) ? $totalWashedCars->count() : 0}}</h3>
                            <a href="{{route('admin.services.list')}}" class="btn btn-primary rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-group text-info"></i> Customers</span>
                            <h3 class="card-title text-nowrap mb-2 toggle-amount" data-value="{{!empty($totalNoOfCustomers) ? $totalNoOfCustomers : 0}}">{{!empty($totalNoOfCustomers) ? $totalNoOfCustomers : 0}}</h3>
                            <a href="{{route('admin.users.list', ['customers'])}}" class="btn btn-info rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                        <h5 class="text-decoration-underline text-white bg-danger rounded p-2">Expenses</h5>
                            <div class="table-responsive" style="height: 400px">
                                <table class="table table-striped table-bordered table-hover w-100">
                                    <thead>
                                        <th>Date</th>
                                        <th>Expense Name</th>
                                        <th>Detail</th>
                                        <th>Amount</th>
                                    </thead>
                                    <tbody>
                                        @foreach(\App\Models\Expense::orderBy('created_at', 'desc')->get() as $expense)
                                        <tr>
                                            <td>{{\Carbon\Carbon::parse($expense->created_at)->format('d M, Y')}}</td>
                                            <td>{{$expense->name}}</td>
                                            <td>{{$expense->description}}</td>
                                            <td>{{$expense->amount}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                        <h5 class="text-decoration-underline text-white bg-primary rounded p-2">Top 10 Returning Customers</h5>
                            <div class="table-responsive" style="height: 400px">
                                <table class="table table-striped table-bordered table-hover w-100">
                                    <thead>
                                        <th>Customer Name</th>
                                        <th>Vehicle (Reg/Name)</th>
                                        <th>Services</th>
                                    </thead>
                                    <tbody>
                                        @php  
                                            $users = \App\Models\User::where('user_type', 3)
                                            ->with(['vehicles.services']) // load vehicles and their services
                                            ->get()
                                            ->map(function ($user) {
                                                // count total services across all vehicles
                                                $user->total_services = $user->vehicles->sum(fn($v) => $v->services->count());
                                                return $user;
                                            })
                                            ->sortByDesc('total_services') // sort collection by total services
                                            ->take(10); // get top 10
                                        @endphp
                                        @foreach($users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>
                                                    @if($user->vehicles->isNotEmpty())
                                                        {{ $user->vehicles->first()->registration_number }}
                                                        ({{ $user->vehicles->first()->name }})
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $user->total_services }}</td>
                                            </tr>
                                        @endforeach
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
<script>
    $(document).ready(function() {
        $(".toggle-amount").each(function () {
            let originalValue = $(this).data("value");
            $(this).html(`* * * * * &nbsp;&nbsp;<i class="bx bx-show toggle-icon"></i>`);
            $(this).data("hidden", true);
            $(this).data("original-value", originalValue);
        });

        $(document).on("click", ".toggle-icon", function () {
            let h3 = $(this).closest(".toggle-amount");

            if (h3.data("hidden")) {
                // ask for password
                let password = prompt("Enter password to view this value:");

                if (password === "1111") { // 👈 set your own password here
                    h3.html(h3.data("original-value") + `&nbsp;&nbsp;<i class="bx bx-hide toggle-icon"></i>`);
                    h3.data("hidden", false);
                } else {
                    alert("Wrong password!");
                }
            } else {
                h3.html(`* * * * * &nbsp;&nbsp;<i class="bx bx-show toggle-icon"></i>`);
                h3.data("hidden", true);
            }
        });
    });
</script>
@endsection