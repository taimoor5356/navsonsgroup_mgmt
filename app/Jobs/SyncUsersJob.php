<?php

namespace App\Jobs;

use App\Models\Billing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class SyncUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $role = Role::where('name', 'customer')->first(); // Fetch the role once

        // Fetch all distinct users at once
        $distinctUsers = Billing::select('service_provider')
            ->distinct()
            ->get();

        // Prepare users for bulk insert
        $newUsers = [];
        $userNames = [];

        foreach ($distinctUsers as $user) {
            if (!empty($user->service_provider)) {
                $userName = $user->service_provider;
                $newEmail = $this->generateNewEmail($userName);

                if (!in_array($newEmail, $userNames) && !User::where('email', $newEmail)->exists()) {
                    $newUsers[] = [
                        'name' => $userName,
                        'email' => $newEmail,
                        'password' => Hash::make('12345678'), // Ensure the password is hashed
                        'user_type' => isset($role) ? $role->id : '',
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    $userNames[] = $newEmail;
                }
            }
        }

        // Bulk insert new users
        if (!empty($newUsers)) {
            User::insert($newUsers);

            // Fetch newly inserted users by email
            $newUserEmails = array_column($newUsers, 'email');
            $insertedUsers = User::whereIn('email', $newUserEmails)->get();

            foreach ($insertedUsers as $user) {
                // Assign role
                $user->assignRole($role);

                // Prepare billing updates
                $bills = Billing::where('service_provider', $user->name)->get();
                foreach ($bills as $bill) {
                    $bill->update(['user_id' => $user->id]);
                }
            }
        }
    }

    function generateNewEmail($userName)
    {
        $userName = trim(str_replace(',', '_', $userName));
        return strtolower(str_replace(' ', '_', $userName)) . "@navsons.com";
    }
}
