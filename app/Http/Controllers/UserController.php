<?php

namespace App\Http\Controllers;

use App\Exports\UserExport;
use App\Jobs\SyncUsersJob;
use App\Models\Billing;
use App\Models\ErrorLog;
use App\Models\Service;
use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserHasGroup;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use DataTables;

class UserController extends Controller
{
    public function datatables($request, $records, $trashed = null)
    {
        $records = $records['records'];
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $records = $records->where(function ($query) use ($searchValue) {
                $query->where('name', 'LIKE', "%$searchValue%")
                    ->orWhere('email', 'LIKE', "%$searchValue%");
                $query->orWhereHas('role', function ($query) use ($searchValue) {
                    $query->where('name', 'LIKE', "%$searchValue%");
                });
            });
        }
        $totalRecords = $records->count(); // Get the total number of records for pagination
        $data = $records->skip($request->start)
            ->take($request->length)
            ->get();
        return DataTables::of($data)
            ->addIndexColumn()
            ->editColumn('sr_no', function ($row) {
                return $row->id;
            })
            ->addColumn('name', function ($row) {
                return ucwords(str_replace('_', ' ', $row->name));
            })
            ->addColumn('email', function ($row) {
                return ucwords($row->email);
            })
            ->addColumn('total_salary', function ($row) {
                return number_format(25000, 2);
            })
            ->addColumn('loan', function ($row) {
                return ucwords($row->expenses?->sum('amount'));
            })
            ->addColumn('salary_left', function ($row) {
                return number_format((25000 - $row->expenses?->sum('amount')), 2);
            })
            ->addColumn('total_services', function ($row) {
                return ucwords($row->vehicles?->first()->services?->count());
            })
            ->addColumn('phone', function ($row) {
                return $row->phone;
            })
            ->addColumn('actions', function ($row) use ($trashed) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">';
                    if ($trashed == null) {
                        $btns .= '<a class="btn btns m-0 p-1" data-user-id="'.$row->id.'" href="customers/edit/">
                                <i class="align-middle text-primary" data-feather="edit">
                                </i>
                            </a>
                            <a class="btn btns m-0 p-1 delete-user" data-user-id="'.$row->id.'" href="#">
                                <i class="align-middle text-danger" data-feather="trash-2">
                                </i>
                            </a>
                        </div>';
                    } else {
                        $btns.= '<a class="btn btns m-0 p-1" href="restore/' . $row->id . '">
                                <i class="align-middle text-success" data-feather="refresh-cw">
                                </i>
                            </a>
                        </div>';
                    }
                return $btns;
            })
            ->rawColumns(['sr_no', 'name', 'total_services', 'phone', 'actions'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($totalRecords) // For simplicity, same as totalRecords
            ->skipPaging()
            ->make(true);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $type = 'customers')
    {
        //
        $data['header_title'] = ucfirst($type). ' List';
        $data['userType'] = $type;
        if ($request->ajax()) {
            $data['records'] = User::with('role', 'vehicles', 'expenses')
                                ->when($type == 'customers', fn($q) => $q->customer())
                                ->when($type == 'users', fn($q) => $q->user())
                                ->orderBy('id', 'desc');
            return $this->datatables($request, $data);
        }
        return view('admin.users.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New';
        $data['roles'] = Role::where('name', 'employee')->get();
        return view('admin.users.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_name' => 'required',
            'user_email' => 'required',
            'user_phone' => 'required',
            'user_address' => 'required',
            'role_id' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => !empty($request->user_name) ? $request->user_name : 'test_user_name',
                'email' => $request->user_email,
                'phone' => !empty($request->user_phone) ? $request->user_phone : 'test_user_phone',
                'address' => !empty($request->user_address) ? $request->user_address : 'test_user_address',
                'user_type' => !empty($request->role_id) ? $request->role_id : '',
                'password' => Hash::make('12345678')
            ]);
            $user->assignRole('employee');

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLog::create([
                'error' => $e->getMessage()
            ]);
            return redirect()->route('admin.users.list')->with('error', 'Something went wrong');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
        $data['header_title'] = 'Edit User Details';
        $data['record'] = User::with('vehicles.services')->where('id', $id)->first();
        return view('admin.users.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $request->validate([
            // 'vehicle_name' => 'required|string',
            'vehicle_registration_number' => 'required|string',
            'service_type' => 'required|integer',
            'charges' => 'required|integer'
        ]);

        DB::beginTransaction();

        try {
            $vehicleRegNo = $request->vehicle_registration_number;
            $vehicle = Vehicle::where('registration_number', $vehicleRegNo)->first();
            $user = null;
            if ($vehicle) {
                // Vehicle exists
                // Vehicle has no user assigned, try to find user by email
                $user = User::where('id', $vehicle->user_id)->first();
                if (!$vehicle->user_id) {
                    if (!$user) {
                        $user = User::create([
                            'name' => !empty($request->customer_name) ? strtolower($request->customer_name) : 'new_user',
                            'email' => !empty($request->customer_email) ? $request->customer_email : (!empty($request->customer_name) ? $request->customer_name.'@test.com' : 'new_user'.$vehicleRegNo.'@test.com'),
                            'phone' => !empty($request->customer_phone) ? $request->customer_phone : null,
                            'address' => !empty($request->customer_address) ? strtolower($request->customer_address) : null,
                            'user_type' => 3,
                            'is_active' => 1,
                            'password' => Hash::make('12345678'),
                        ]);
                    }
                    $vehicle->update(['user_id' => $user->id]);
                }
                $user->assignRole('customer');
            } else {
                // Vehicle does not exist, create user and vehicle
                $user = User::create([
                    'name' => !empty($request->customer_name) ? strtolower($request->customer_name) : 'new_user',
                    'email' => !empty($request->customer_email) ? $request->customer_email : 'new_user_' . $vehicleRegNo . '@test.com',
                    'phone' => !empty($request->customer_phone) ? $request->customer_phone : null,
                    'address' => !empty($request->customer_address) ? strtolower($request->customer_address) : null,
                    'user_type' => 3,
                    'is_active' => 1,
                    'password' => Hash::make('12345678'),
                ]);
                $user->assignRole('customer');
                $vehicle = Vehicle::create([
                    'name' => strtolower($request->vehicle_name),
                    'user_id' => $user->id,
                    'registration_number' => strtolower($vehicleRegNo),
                ]);
            }

            // Always create car wash service entry
            Service::create([
                'vehicle_id' => $vehicle->id,
                'service_type_id' => $request->service_type,
                'diesel' => $request->filled('diesel') ? 1 : 0,
                'polish' => $request->filled('polish') ? 1 : 0,
                'charges' => $request->charges ?? 0,
                'discount' => $request->discount ?? 0,
                'discount_reason' => strtolower($request->discount_reason) ?? null,
                'collected_amount' => $request->collected_amount ?? 0,
                'payment_mode_id' => $request->payment_mode_id ?? 0,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLog::create([
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        if (!empty($request->user_id)) {
            if ($request->user_id == 1) {
                return response()->json(['status' => false, 'message' => 'Cannot delete admin user']);
            }
            User::where('id', $request->user_id)->where('id', '!=', 1)->delete();
            return response()->json(['status' => true, 'message' => 'Deleted successfully']);
        } else {
            return response()->json(['status' => false,'message' => 'User not found']);
        }
    }

    public function sync()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '-1');
        try {
            $role = Role::where('name', 'customer')->first(); // Fetch the role once
    
            // Fetch all distinct users at once
            $distinctUsers = User::where('provider_npi', '!=', NULL)->where('user_type', 2)
                ->get();
            // Prepare users for bulk insert
            $newUsers = [];
            $userNames = [];
    
            foreach ($distinctUsers as $user) {
                $billings = Billing::where('provider_npi', $user->provider_npi)->get();
                foreach ($billings as $billing) {
                    $billing->user_id = $user->id;
                    $billing->save();
                }
            }
            return redirect()->back()->with('success', 'Synchronization completed successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    public function export(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', '0');
        $receivedDate = $request->input('received_date');
        $fileName = 'user_' . time() . '.xlsx';
        $storagePath = 'exports/' . $fileName;

        // Ensure the directory exists
        if (!Storage::exists('exports')) {
            Storage::makeDirectory('exports');
        }

        // Store the file
        Excel::store(new UserExport($receivedDate), $storagePath, 'public');

        // Return the file download response
        return Excel::download(new UserExport($receivedDate), $fileName);
    }

    function generateNewEmail($userName)
    {
        $userName = trim(str_replace(',', '_', $userName));
        return strtolower(str_replace(' ', '_', $userName)) . "@navsons.com";
    }

    public function deleteMultipleUsers(Request $request)
    {
        $userIds = $request->user_ids;
        if (!empty($userIds)) {
            User::whereIn('id', $userIds)->where('id', '!=', 1)->delete();
            return response()->json(['status' => true, 'message' => 'Deleted successfully']);
        }
    }

    public function trashed(Request $request)
    {
        if ($request->ajax()) {
            $records = User::with('groups', 'role')->onlyTrashed()->orderBy('id', 'asc');
            return $this->datatables($request, $records, 'trashed');
        }
        $data['header_title'] = 'Trashed Users List';
        return view('admin.users.trashed', $data);
    }

    public function restore(Request $request, $userId)
    {
        if (!empty($userId)) {
            User::onlyTrashed()->whereIn('id', [$userId])->restore();
            return redirect()->back()->with('success', 'User restored successfully');
        } else {
            return redirect()->back()->with('error', 'User not found');
        }
    }
}
