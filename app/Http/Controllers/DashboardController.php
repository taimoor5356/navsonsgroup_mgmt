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
        $date = $request->input('date_range');
        $data['header_title'] = 'Dashboard';
        if ($date) {
            [$start, $end] = explode(' - ', $date);

            // use range for summaries
            $data['totalWashedCars']    = $this->totalWashedCars($start, $end);
            $data['totalNoOfCustomers'] = $this->totalNoOfCustomers($start, $end);
            $data['salesSummary']       = $this->salesSummary($start, $end);
            $data['expensesSummary']    = $this->expensesSummary($start, $end);

        } else {
            // default: today only
            $currentDay = Carbon::now()->month;

            $data['totalWashedCars']    = $this->totalWashedCars($currentDay, $currentDay);
            $data['totalNoOfCustomers'] = $this->totalNoOfCustomers($currentDay, $currentDay);
            $data['salesSummary']       = $this->salesSummary($currentDay, $currentDay);
            $data['expensesSummary']    = $this->expensesSummary($currentDay, $currentDay);
        }

        $authUser = Auth::user();
        if ($authUser) {
            return view('dashboard', $data);
        } else {
            Auth::logout();
            return redirect()->back();
        }
    }

    public function totalWashedCars($startDate, $endDate = null) 
    {
        $query = Service::query();

        if ($endDate) {
            // Range filter
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } else {
            // Single day filter
            $query->whereDate('created_at', $startDate);
        }

        return $query->get();
    }

    public function totalNoOfCustomers($startDate, $endDate = null) 
    {
        $query = User::customer()->active();

        if ($endDate) {
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } else {
            $query->whereDate('created_at', $startDate);
        }

        return $query->count();
    }

    // Sales / Payments / Discounts
    public function salesSummary($startDate, $endDate = null)
    {
        $query = Service::selectRaw("
            SUM(collected_amount) as total_sales,
            SUM(CASE WHEN payment_mode_id = 1 THEN collected_amount ELSE 0 END) as total_cash_received,
            SUM(CASE WHEN payment_mode_id != 1 THEN collected_amount ELSE 0 END) as total_online_payment_received,
            SUM(discount) as total_discounts
        ");

        if ($endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            $query->whereDate('created_at', $startDate);
        }

        return $query->first();
    }

    // Expenses
    public function expensesSummary($startDate, $endDate = null)
    {
        $query = Expense::selectRaw("
            SUM(amount) as total_expenses,
            SUM(CASE WHEN payment_mode_id = 1 THEN amount ELSE 0 END) as total_cash_expenses,
            SUM(CASE WHEN payment_mode_id != 1 THEN amount ELSE 0 END) as total_online_expenses
        ");

        if ($endDate) {
            $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
        } else {
            $query->whereDate('created_at', $startDate);
        }

        return $query->first();
    }
}
