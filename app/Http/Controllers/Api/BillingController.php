<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function index()
    {
        try {
            $records = Billing::where('id', '<', 10)->get();
            return response()->json([
                'status' => true,
                'data' => $records
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'data' => []
            ], 500);
        }
    }
    public function dashboard(Request $request)
    {
        try {

            // bar chart

            $barChart['paid'] = (string) Billing::where('claim_status', 'Paid')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['rebilled'] = (string) Billing::where('claim_status', 'Rebilled')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['inProcess'] = (string) Billing::where('claim_status', 'In Process')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['infoRequired'] = (string) Billing::where('claim_status', 'Info Required')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['patientResponsibility'] = (string) Billing::where('claim_status', 'Patient Responsibility')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['partiallyPaid'] = (string) Billing::where('claim_status', 'Partially Paid')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['dropped'] = (string) Billing::where('claim_status', 'Dropped')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['duplicate'] = (string) Billing::where('claim_status', 'Duplicate')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $barChart['denied'] = (string) Billing::where('claim_status', 'Denied')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();


            // pie chart
            $pieChart['fully_paid'] = (string) Billing::where('claim_status', 'Paid')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $pieChart['paid_pr'] = (string) Billing::where('claim_status', 'Paid')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('claim_comments', 'Patient Responsibility')
                ->where('user_id', Auth::user()->id)->count();

            $pieChart['paid_sec'] = (string) Billing::where('claim_status', 'Paid')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('claim_comments', 'In Process Secondary')
                ->where('user_id', Auth::user()->id)->count();

            $pieChart['self_pay'] = (string) Billing::where('claim_status', 'Patient Responsibility')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $pieChart['in_process'] = (string) Billing::where('claim_status', 'In Process')
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)->count();

            $data['barChart'] = $barChart;
            $data['pieChart'] = $pieChart;


            // Assume start_date and end_date are provided in the request
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Generate all dates in the range
            $dateRange = [];
            for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
                $dateRange[$date->format('Y-m-d')] = [
                    'fully_paid' => 0,
                    'in_process' => 0,
                    'paid_primary' => 0,
                    'paid_secondary' => 0,
                    'self_pay' => 0
                ];
            }

            // Fetch data from the billings table with conditional aggregation
            $billings = DB::table('billings')
                ->select(
                    DB::raw('DATE(date_of_service) as date'),
                    DB::raw('SUM(CASE WHEN claim_status = "Paid" THEN 1 ELSE 0 END) as fully_paid'),
                    DB::raw('SUM(CASE WHEN claim_status = "In Process" THEN 1 ELSE 0 END) as in_process'),
                    DB::raw('SUM(CASE WHEN claim_status = "Paid" AND claim_comments = "Patient Responsibility" THEN 1 ELSE 0 END) as paid_primary'),
                    DB::raw('SUM(CASE WHEN claim_comments = "In Process Secondary" THEN 1 ELSE 0 END) as paid_secondary'),
                    DB::raw('SUM(CASE WHEN claim_status = "Patient Responsibility" THEN 1 ELSE 0 END) as self_pay')
                )
                ->where('deleted_at', NULL)
                ->whereDate('date_of_service', '>=', $request->start_date)
                ->whereDate('date_of_service', '<=', $request->end_date)
                ->where('user_id', Auth::user()->id)
                ->groupBy(DB::raw('DATE(date_of_service)'))
                ->orderBy('date', 'asc')
                ->get();

            // Merge fetched data with date range
            foreach ($billings as $billing) {
                $date = Carbon::parse($billing->date)->format('Y-m-d');
                if (isset($dateRange[$date])) {
                    $dateRange[$date] = [
                        'fully_paid' => (string)round($billing->fully_paid, 2),
                        'in_process' => (string)round($billing->in_process, 2),
                        'paid_primary' => (string)round($billing->paid_primary, 2),
                        'paid_secondary' => (string)round($billing->paid_secondary, 2),
                        'self_pay' => (string)round($billing->self_pay, 2),
                    ];
                }
            }

            // Convert dateRange to collection or array as needed
            $data['billingTable'] = collect($dateRange)->map(function ($value, $date) {
                return [
                    'date' => $date,
                    'fully_paid' => (string) round($value['fully_paid'], 2),
                    'in_process' => (string) round($value['in_process'], 2),
                    'paid_primary' => (string) round($value['paid_primary'], 2),
                    'paid_secondary' => (string) round($value['paid_secondary'], 2),
                    'self_pay' => (string) round($value['self_pay'], 2)
                ];
            })->values();

            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    public function billingDetails(Request $request)
    {
        try {
            $data = [];
            if ($request->view == 'TC') {
                $data['totalCollection'] = [
                    'currentMonth' => (string) Billing::currentMonthPayment($request), // primary + secondary + selfpay
                    'previousPayment' => (string) Billing::previousPayment($request), // primary + secondary + selfpay
                ];
            }
            if ($request->view == 'W-RVUs') {
                $data['totalWorkedRvus'] = [
                    'pendingRvus' => (string) Billing::pendingRvusApi($request), // sum of rvus pending
                    'paidRvus' => (string) Billing::paidRvusApi($request), // sum of rvus paid
                ];
            }
            if ($request->view == 'IRD') {
                $data['all_data'] = Billing::insuranceReceivableDetails($request)['allData'];
                $data['partially_paid'] = Billing::insuranceReceivableDetails($request)['partiallyPaidData'];
                $data['time_period'] = Billing::insuranceReceivableDetails($request)['timePeriod'];
                    // 'thirty_days' => (string) Billing::insuranceReceivableDetails($request)['totalThirty'], // primary + secondary + selfpay
                    // 'thirty_to_sixty_days' => (string) Billing::insuranceReceivableDetails($request)['totalSixty'], // primary + secondary + selfpay
                    // 'sixty_to_ninty_days' => (string) Billing::insuranceReceivableDetails($request)['totalNinety'], // primary + secondary + selfpay
                    // 'ninty_to_one_twenty_days' => (string) Billing::insuranceReceivableDetails($request)['totalOneTwenty'], // primary + secondary + selfpay
                    // 'hundred_twenty_plus_days' => (string) Billing::insuranceReceivableDetails($request)['totalHundredTwentyPlus'], // primary + secondary + selfpay
                $data['insurance_statuses'] = [
                    'primaryBalance' => [
                        'no_of_patients' => (string) Billing::primaryBalance($request)->uniqueMrns, // primary + secondary + selfpay
                        'total_amount' =>  (string) Billing::primaryBalance($request)->totalAmount, // primary + secondary + selfpay
                    ],
                    'secondaryBalance' => [
                        'no_of_patients' => (string) Billing::secondaryBalance($request)->uniqueMrns, // primary + secondary + selfpay
                        'total_amount' => (string) Billing::secondaryBalance($request)->totalAmount, // primary + secondary + selfpay
                    ],
                ];
            }
            if ($request->view == 'PRD') {
                $data['patientReceivableDetails'] = [
                    'thirty_days' => (string) Billing::patientReceivableDetails($request)['totalThirty'], // primary + secondary + selfpay
                    'thirty_to_sixty_days' => (string) Billing::patientReceivableDetails($request)['totalSixty'], // primary + secondary + selfpay
                    'sixty_to_ninty_days' => (string) Billing::patientReceivableDetails($request)['totalNinety'], // primary + secondary + selfpay
                    'ninty_to_one_twenty_days' => (string) Billing::patientReceivableDetails($request)['totalOneTwenty'], // primary + secondary + selfpay
                    'hundred_twenty_plus_days' => (string) Billing::patientReceivableDetails($request)['totalHundredTwentyPlus'], // primary + secondary + selfpay
                ];
                $data['patient_statuses'] = [
                    'coInsurance' => Billing::statuses($request)['coInsurance'],
                    'coPayments' => Billing::statuses($request)['coPayment'],
                    'deductibles' => Billing::statuses($request)['deductibles'],
                    'selfPay' => Billing::statuses($request)['selfPay'],
                    'overPayment' => Billing::statuses($request)['overPayment'],
                    'coInsurance' => Billing::statuses($request)['coInsurance'],
                ];
            }
            return response()->json([
                'status' => true,
                'message' => 'Success',
                'data' => $data,
            ], 200);
        } catch (\Exception $e) {
            dd($e);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }
}
