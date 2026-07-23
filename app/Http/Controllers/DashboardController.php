<?php

namespace App\Http\Controllers;

use App\Models\AmountTransaction;
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
        if (Auth::user()->role?->name == 'employee') {
            $data['record'] = Auth::user();
            return view('user.dashboard', $data);
        }
        $date = $request->input('date_range');
        if ($date) {
            [$start, $end] = explode(' - ', $date);

            // use range for summaries
            $data['totalWashedCars']    = $this->totalWashedCars($start, $end);
            $data['totalNoOfCustomers'] = $this->totalNoOfCustomers($start, $end);
            $data['salesSummary']       = $this->salesSummary($start, $end);
            $data['expensesSummary']    = $this->expensesSummary($start, $end);
            $data['dayWiseSale']    = $this->dayWiseSale($start, $end);
            $data['dailyCashInHandReceived']    = $this->dailyCashInHandReceived($start, $end);

        } else {
            // No date filter applied: Total Vehicles Washed / Total Customers show
            // all-time totals (not just the current month) — the other cards keep
            // their current-month default.
            $startDay = Carbon::now()->startOfMonth()->format('Y-m-d');
            $currentDay = Carbon::now()->format('Y-m-d');

            $data['totalWashedCars']    = $this->totalWashedCars();
            $data['totalNoOfCustomers'] = $this->totalNoOfCustomers();
            $data['salesSummary']       = $this->salesSummary($startDay, $currentDay);
            $data['expensesSummary']    = $this->expensesSummary($startDay, $currentDay);
            $data['dayWiseSale']    = $this->dayWiseSale($startDay, $currentDay);
            $data['dailyCashInHandReceived']    = $this->dailyCashInHandReceived($startDay, $currentDay);
        }

        $authUser = Auth::user();
        if ($authUser) {
            return view('dashboard', $data);
        } else {
            Auth::logout();
            return redirect()->back();
        }
    }

    public function totalWashedCars($startDate = null, $endDate = null)
    {
        $query = Service::query()->whereNull('deleted_at');

        if ($startDate && $endDate) {
            // Range filter
            $query->whereBetween('created_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ]);
        } elseif ($startDate) {
            // Single day filter
            $query->whereDate('created_at', $startDate);
        }
        // No dates at all: no filter applied, return all-time data.

        return $query->get();
    }

    public function totalNoOfCustomers($startDate = null, $endDate = null)
    {
        $query = User::customer()->active();

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        } elseif ($startDate) {
            $query->whereDate('created_at', $startDate);
        }
        // No dates at all: no filter applied, return all-time data.

        return $query->count();
    }

    // Daily sale
    public function dayWiseSale($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? $startDate . ' 00:00:00' : now()->startOfMonth()->format('Y-m-d 00:00:00');
        $endDate   = $endDate ? $endDate . ' 23:59:59' : now()->endOfDay()->format('Y-m-d H:i:s');

        return Service::selectRaw("
                    DATE(created_at) as sale_date,
                    COALESCE(SUM(collected_amount), 0) as total_sales
                ")
                ->whereNull('deleted_at')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('sale_date')
                ->orderBy('sale_date', 'desc')
                ->get();
    }

    // Daily CIH Received
    public function dailyCashInHandReceived($startDate = null, $endDate = null)
    {
        $startDate = $startDate ? $startDate . ' 00:00:00' : now()->startOfMonth()->format('Y-m-d 00:00:00');
        $endDate   = $endDate ? $endDate . ' 23:59:59' : now()->endOfDay()->format('Y-m-d H:i:s');

        return AmountTransaction::selectRaw("
                    DATE(created_at) as amount_received_date,
                    COALESCE(SUM(amount), 0) as total_received
                ")
                ->where('received_by', 1)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('amount_received_date')
                ->orderBy('amount_received_date', 'desc')
                ->get();
    }

    // Sales / Payments / Discounts
    public function salesSummary($startDate, $endDate = null)
    {
        $query = Service::selectRaw("
            SUM(collected_amount) as total_sales,
            SUM(CASE WHEN payment_mode_id = 1 THEN (collected_amount) ELSE 0 END) as total_cash_received,
            SUM(CASE WHEN payment_mode_id != 1 THEN (collected_amount) ELSE 0 END) as total_online_payment_received,
            SUM(CASE WHEN overtime = 1 THEN (overtime_amount) ELSE 0 END) as total_overtime_payment,
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
