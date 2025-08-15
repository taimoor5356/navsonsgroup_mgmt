<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Expense;
use App\Models\Service;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserHasGroup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Request as InputRequest;
use DataTables;

class DashboardController extends Controller
{
    //
    public function index(Request $request)
    {
        $data['header_title'] = 'Dashboard';

        $currentMonth = Carbon::now()->format('m');

        $data['totalWashedCars'] = $this->totalWashedCars($currentMonth);
        $data['totalNoOfCustomers'] = $this->totalNoOfCustomers($currentMonth);

        $data['salesSummary'] = $this->salesSummary($currentMonth);
        $data['expensesSummary'] = $this->expensesSummary($currentMonth);

        $authUser = Auth::user();
        if ($authUser) {
            return view('dashboard', $data);
        } else {
            Auth::logout();
            return redirect()->back();
        }
    }

    public function totalWashedCars($currentMonth) 
    {
        $data = Service::whereMonth('created_at', $currentMonth)->get();
        return $data;
    }

    public function totalNoOfCustomers($currentMonth) 
    {
        $data = User::customer()->active()->whereMonth('created_at', $currentMonth)->count();
        return $data;
    }

    // Sales / Payments / Discounts
    public function salesSummary($currentMonth)
    {
        return Service::selectRaw("
            SUM(collected_amount) as total_sales,
            SUM(CASE WHEN payment_mode_id = 1 THEN collected_amount ELSE 0 END) as total_cash_received,
            SUM(CASE WHEN payment_mode_id != 1 THEN collected_amount ELSE 0 END) as total_online_payment_received,
            SUM(discount) as total_discounts
        ")->whereMonth('created_at', $currentMonth)->first();
    }

    // Expenses
    public function expensesSummary($currentMonth)
    {
        return Expense::selectRaw("
            SUM(amount) as total_expenses,
            SUM(CASE WHEN payment_mode_id = 1 THEN amount ELSE 0 END) as total_cash_expenses,
            SUM(CASE WHEN payment_mode_id != 1 THEN amount ELSE 0 END) as total_online_expenses
        ")->whereMonth('created_at', $currentMonth)->first();
    }
}
