<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\ErrorLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ServiceController extends Controller
{
    public function datatables($request, $records, $trashed = null)
    {
        $records = $records['records'];
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $records = $records->where(function ($query) use ($searchValue) {
                // Search in vehicle (registration_number or name)
                $query->whereHas('vehicle', function($q) use ($searchValue) {
                    $q->where('registration_number', 'LIKE', "%{$searchValue}%")
                    ->orWhere('name', 'LIKE', "%{$searchValue}%");
                })
                // OR search in vehicle.user (phone)
                ->orWhereHas('vehicle.user', function($q) use ($searchValue) {
                    $q->where('phone', 'LIKE', "%{$searchValue}%");
                })
                // OR search in charges column directly
                ->orWhere(function($q) use ($searchValue) {
                    $q->where('charges', 'LIKE', "%{$searchValue}%");
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
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->created_at)->format('d M, Y');
            })
            ->editColumn('payment_status', function ($row) {
                if ($row->payment_status == 1 && $row->collected_amount > 0) {
                    // Show only Paid badge
                    return '<small class="bg-success text-white px-3 py-1 rounded-pill">Paid</small>';
                } else {
                    // Show checkbox + Un-Paid badge
                    return '
                        <div class="form-check form-switch d-flex justify-content-start align-items-center gap-2">
                            <input 
                                class="form-check-input payment-toggle border border-secondary px-3" 
                                type="checkbox" 
                                role="switch" 
                                data-id="'.$row->id.'"
                            >
                            <small class="bg-danger text-white px-3 py-1 rounded-pill">Un-Paid</small>
                        </div>
                    ';
                }
            })
            // ->editColumn('payment_status', function ($row) {
            //     $status  = $row->payment_status;
            //     $checked = $status == 1 ? 'checked' : '';
            //     $label   = $status == 1 
            //         ? '<small class="bg-success text-white px-3 py-1 rounded-pill">Paid</small>' 
            //         : '<small class="bg-danger text-white px-3 py-1 rounded-pill">Un-Paid</small>';

            //     return '
            //         <div class="form-check form-switch d-flex justify-content-start align-items-center gap-2">
            //             <input 
            //                 class="form-check-input payment-toggle border border-secondary px-3" 
            //                 type="checkbox" 
            //                 role="switch" 
            //                 data-id="'.$row->id.'" 
            //                 '.$checked.'
            //             >
            //             '.$label.'
            //         </div>
            //     ';
            // })
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" data-user-id="'.$row->id.'" class="user-checkbox">';
            })
            ->addColumn('payment_mode', function ($row) {
                return ucfirst($row->payment_mode?->name);
            })
            ->addColumn('vehicle_name', function ($row) {
                return ucwords($row->vehicle?->name);
            })
            ->addColumn('vehicle_registration_number', function ($row) {
                return strtoupper($row->vehicle?->registration_number) . ' ('.$row->vehicle?->name.')';
            })
            ->addColumn('charges', function ($row) {
                return $row->charges;
            })
            ->addColumn('collected_amount', function ($row) {
                return $row->collected_amount;
            })
            ->addColumn('discount', function ($row) {
                return $row->discount;
            })
            ->addColumn('discount_reason', function ($row) {
                return ucfirst($row->discount_reason);
            })
            ->addColumn('email', function ($row) {
                return $row->user?->email;
            })
            ->addColumn('phone', function ($row) {
                return $row->vehicle?->user?->phone;
            })
            ->addColumn('actions', function ($row) use ($trashed) {
                $btns = '
                    <div class="actionb-btns-menu d-flex justify-content-center">';
                    if ($trashed == null) {
                        // if ($row->payment_status == 0 && $row->collected_amount == 0) {
                            $btns .= '<a class="btn btns m-0 p-1" data-user-id="'.$row->id.'" href="edit/'.$row->id.'">
                                    <i class="align-middle text-primary" data-feather="edit">
                                    </i>
                                </a>';
                        // }
                        // $btns .='<a class="btn btns m-0 p-1 delete-user" data-user-id="'.$row->id.'" href="#">
                        //         <i class="align-middle text-danger" data-feather="trash-2">
                        //         </i>
                        //     </a>
                        // </div>';
                    } else {
                        // $btns.= '<a class="btn btns m-0 p-1" href="restore/' . $row->id . '">
                        //         <i class="align-middle text-success" data-feather="refresh-cw">
                        //         </i>
                        //     </a>
                        // </div>';
                    }
                return $btns;
            })
            ->rawColumns(['sr_no', 'payment_status', 'email', 'name', 'role', 'actions'])
            ->setTotalRecords($totalRecords)
            ->setFilteredRecords($totalRecords) // For simplicity, same as totalRecords
            ->skipPaging()
            ->make(true);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $data['header_title'] = 'Services List';
        if ($request->ajax()) {
            $data['records'] = Service::with('vehicle.user', 'payment_mode')
                                ->orderBy('id', 'desc');
            return $this->datatables($request, $data);
        }
        return view('admin.services.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        $data['header_title'] = 'Add New';
        return view('admin.services.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_registration_number' => 'required|string',
            'service_type' => 'required|integer',
            'charges' => 'required|integer',
            'payment_mode_id' => 'required'
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
                'payment_status' => 0,
                'created_at' => !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::now(),
                'updated_at' => !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::now(),
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
            // return redirect()->route('admin.services.list')->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            ErrorLog::create([
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $id)
    {
        //
        $data['header_title'] = 'Edit Details';
        $data['record'] = Service::with('vehicle.user')->where('id', $id)->first();
        return view('admin.services.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $service = Service::with('vehicle.user')->where('id', $id)->first();
            if (isset($service)) {
                $service->service_type_id = $request->service_type;
                $service->diesel = $request->filled('diesel') ? 1 : 0;
                $service->polish = $request->filled('polish') ? 1 : 0;
                $service->charges = $request->charges ?? 0;
                $service->discount = $request->discount ?? 0;
                $service->discount_reason = strtolower($request->discount_reason) ?? null;
                $service->payment_mode_id = $request->payment_mode_id ?? 0;
                $service->created_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->created_at)->format('Y-m-d H:i:s');
                $service->updated_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->updated_at)->format('Y-m-d H:i:s');
                $service->save();
            }
            $vehicle = Vehicle::where('id', $service->vehicle_id)->first();
            if (isset($vehicle)) {
                $vehicle->name = $request->vehicle_name;
                $vehicle->registration_number = $request->vehicle_registration_number;
                $vehicle->save();
            }
            $user = User::where('id', $vehicle->user_id)->first();
            if (isset($user)) {
                $user->name = $request->customer_name;
                $user->phone = $request->customer_phone;
                $user->address = $request->customer_address;
                $user->save();
            }
            return redirect()->back()->with('success', 'Updated successfully');
            // return redirect()->route('admin.services.list')->with('success', 'Updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        //
    }

    public function updatePaymentStatus(Request $request, $id) 
    {
        try {
            $service = Service::where('id', $id)->first();
            if (isset($service)) {
                if ($service->payment_status == 0 && $service->collected_amount == 0) {
                    $service->collected_amount = $service->charges - $service->discount;
                }
                $service->payment_status = $request->status;
            }
            $service->save();
            return response()->json([
                'status' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false
            ]);
        }
    }
}
