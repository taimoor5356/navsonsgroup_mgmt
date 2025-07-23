<?php

namespace App\Http\Controllers;

use App\Models\Billing;
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
        $data['totalWashedCars'] = $this->totalWashedCars();
        $data['totalNoOfCustomers'] = $this->totalNoOfCustomers();
        $data['totalPayments'] = $this->totalPayments();
        $data['totalExpenses'] = $this->totalExpenses();

        $authUser = Auth::user();
        if ($authUser) {
            return view('dashboard', $data);
        } else {
            Auth::logout();
            return redirect()->back();
        }
    }

    public function totalWashedCars() 
    {
        $data = User::customer()->active()->count();
        return $data;
    }

    public function totalNoOfCustomers() 
    {
        $data = User::customer()->active()->count();
        return $data;
    }

    public function totalPayments() 
    {
        $data = User::customer()->active()->count();
        return $data;
    }

    public function totalExpenses() 
    {
        $data = User::customer()->active()->count();
        return $data;
    }
}
