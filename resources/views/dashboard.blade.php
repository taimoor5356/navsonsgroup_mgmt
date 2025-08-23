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
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-success"></i> Total Sale</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($salesSummary) ? number_format($salesSummary->total_sales, 2) : 0}}</h3>
                            <a href="{{route('admin.services.list')}}" class="btn btn-success rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Expenses</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format($expensesSummary->total_expenses, 2) : 0}}</h3>
                            <small>(Advance Salaries: {{number_format(\App\Models\Expense::where('expense_type_id', 2)->whereDate('created_at', request('date_range'))->sum('amount'), 2)}})</small><br>
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
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
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Online Expense</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($expensesSummary) ? number_format(($expensesSummary->total_online_expenses), 2) : 0}}</h3>
                            <a href="{{route('admin.expenses.list')}}" class="btn btn-danger rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-money text-danger"></i> Total Discounts</span>
                            <h3 class="card-title text-nowrap mb-2">Rs {{!empty($salesSummary) ? number_format($salesSummary->total_discounts, 2) : 0}}</h3>
                            <a href="{{route('admin.services.list')}}" class="btn btn-warning rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-car text-primary"></i> Total Vehicles Washed</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalWashedCars) ? $totalWashedCars->count() : 0}}</h3>
                            <a href="{{route('admin.services.list')}}" class="btn btn-primary rounded btn-xs">View More</a>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <span class="d-block mb-1"><i class="menu-icon tf-icons bx bx-group text-info"></i> Customers</span>
                            <h3 class="card-title text-nowrap mb-2">{{!empty($totalNoOfCustomers) ? $totalNoOfCustomers : 0}}</h3>
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
</script>
@endsection