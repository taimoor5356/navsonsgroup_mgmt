<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AmountTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AmountTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AmountTransaction $amountTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AmountTransaction $amountTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AmountTransaction $amountTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AmountTransaction $amountTransaction)
    {
        //
    }

    public function addCashInHandAmount(Request $request)
    {
        if (Auth::user()->hasRole('manager')) {
            AmountTransaction::create([
                'amount_type' => 'cash_in_hand',
                'amount' => $request->amount ?? 0.00,
                'sent_from' => Auth::user()->id
            ]);
        } elseif (Auth::user()->hasRole('admin')) {
            $data = AmountTransaction::whereDate('created_at', Carbon::now())->where('amount', $request->amount)->where('received_by', null);
            if ($data->exists()) {
                $data->update([
                    'received_by' => Auth::user()->id,
                ]);
            } else {
                return redirect()->back()->with('error', 'No data found');
            }
        }
        return redirect()->back()->with('success', 'Successfull');
    }
}
