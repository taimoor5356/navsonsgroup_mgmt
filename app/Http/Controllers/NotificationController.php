<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\File;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function sendNotification()
    {
        //
    }

    public function getNotifications()
    {
        $bill = Billing::orderBy('id', 'DESC')->take(1)->first();
        if (isset($bill)) {
            $importStatus = $bill->import_notification;
            $userSyncStatus = $bill->user_sync_notification;

            
            $bill->import_notification = 0;
            $bill->user_sync_notification = 0;

            $bill->save();

            return response()->json([
                'status' => true,
                'import_notification' => $importStatus,
                'user_sync_notification' => $userSyncStatus,
            ]);
        }
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
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
