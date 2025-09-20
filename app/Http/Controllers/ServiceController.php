<?php

namespace App\Http\Controllers;

use DataTables;
use App\Models\User;
use App\Models\Service;
use App\Models\Vehicle;
use App\Models\ErrorLog;
use App\Models\UserVehicle;
use App\Models\VehicleBrand;
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
        if (!empty($request->date_range)) {
            $filterDate = explode(' - ', $request->date_range);
            $startDate = $filterDate[0];
            $endDate = $filterDate[1];
            $records = $records->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        }
        if (!empty($request->payment_status)) {
            // Filter by payment status
            if ($request->payment_status == 'all') {
                $records = $records;
            } else if ($request->payment_status == 'paid') {
                $records = $records->where('payment_status', 1);
            } else if ($request->payment_status == 'unpaid') {
                $records = $records->where('payment_status', 0);
            }
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
                return Carbon::parse($row->created_at)->format('d M, Y h:i:s');
            })
            ->editColumn('service_type', function ($row) {
                return ucfirst($row->service_type?->name);
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
                $paymentModes = \App\Models\PaymentMode::all();
                $options = '<option value="" disabled>Payment Mode</option>';
                foreach ($paymentModes as $mode) {
                    $selected = $row->payment_mode_id == $mode->id ? 'selected' : '';
                    $options .= '<option value="'.$mode->id.'" '.$selected.'>'.strtoupper(str_replace('_', ' ', $mode->name)).'</option>';
                }

                return '<select class="payment-mode-update payment-mode" 
                                data-service-id="'.$row->id.'">'.$options.'</select>';
            })
            ->addColumn('vehicle_name', function ($row) {
                return ucwords($row->vehicle?->name);
            })
            ->addColumn('vehicle_registration_number', function ($row) {
                return strtoupper($row->user_vehicle?->registration_number) . ' ('.$row->user_vehicle?->vehicle?->name.')';
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
            ->addColumn('complaint', function ($row) {
                return '<input type="checkbox" class="complain-checkbox" data-service-id="'.$row->id.'" '.($row->complain == 1 ? 'checked' : '').'>';
            })
            ->addColumn('luster', function ($row) {
                return '<input type="checkbox" class="luster luster-service" data-service-id="'.$row->id.'" '.($row->luster == 1 ? 'checked' : '').'>';
            })
            ->addColumn('polish', function ($row) {
                return '<input type="checkbox" class="polish polish-service" data-service-id="'.$row->id.'" '.($row->polish == 1 ? 'checked' : '').'>';
            })
            ->addColumn('vaccum', function ($row) {
                return '<input type="checkbox" class="vaccum vaccum-service" data-service-id="'.$row->id.'" '.($row->vaccum == 1 ? 'checked' : '').'>';
            })
            ->addColumn('diesel', function ($row) {
                return '<input type="checkbox" class="diesel diesel-service" data-service-id="'.$row->id.'" '.($row->diesel == 1 ? 'checked' : '').'>';
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
            ->rawColumns(['sr_no', 'payment_status', 'email', 'payment_mode', 'name', 'role', 'luster', 'polish', 'vaccum', 'diesel', 'complaint', 'actions'])
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
            $data['records'] = Service::with('user_vehicle.user', 'payment_mode', 'service_type')
                                ->orderBy('payment_status', 'asc')   // unpaid (0) first, paid (1) later
                                ->orderBy('created_at', 'desc');     // then latest first
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
            'vehicle_registration_number' => 'required',
            'vehicle_brand_id' => 'required',
            'vehicle_brand_name' => 'required',
            'vehicle_id' => 'required',
            'vehicle_name' => 'required',
            'service_type' => 'required',
            'charges' => 'required',
            'payment_mode_id' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $vehicleRegNo = strtolower($request->vehicle_registration_number);

            $userRegisteredVehicle = UserVehicle::where('registration_number', $vehicleRegNo)->first();
            $user = null;
            $vehicle = null;

            // ========================
            // Step 1: Handle Vehicle Brand
            // ========================
            $vehicleBrandId = $request->vehicle_brand_id;
            if (!$vehicleBrandId && !empty($request->vehicle_brand_name)) {
                $brand = VehicleBrand::firstOrCreate(
                    ['name' => strtolower($request->vehicle_brand_name)]
                );
                $vehicleBrandId = $brand->id;
            }

            // ========================
            // Step 2: Handle Vehicle
            // ========================
            $vehicleId = $request->vehicle_id;
            if (!$vehicleId && !empty($request->vehicle_name)) {
                $vehicleModel = Vehicle::firstOrCreate(
                    ['name' => strtolower($request->vehicle_name)],
                    [
                        'vehicle_brand_id' => $vehicleBrandId
                    ]
                );
                $vehicleId = $vehicleModel->id;
                $vehicleModel->service_count = $vehicleModel->service_count + 1;
                $vehicleModel->save();
            }

            if (isset($userRegisteredVehicle)) {
                // Vehicle exists in UserVehicle
                $user = $userRegisteredVehicle->user;

                if (!$userRegisteredVehicle->user_id) {
                    if (!$user) {
                        $user = User::create([
                            'name'      => !empty($request->customer_name) ? strtolower($request->customer_name) : 'customer',
                            'email'     => !empty($request->customer_email) ? $request->customer_email : (!empty($request->customer_name) ? $request->customer_name . '@test.com' : 'customer' . $vehicleRegNo . '@test.com'),
                            'phone'     => $request->customer_phone ?? null,
                            'address'   => !empty($request->customer_address) ? strtolower($request->customer_address) : null,
                            'user_type' => 3,
                            'is_active' => 1,
                            'password'  => Hash::make('12345678'),
                        ]);
                    }

                    $userRegisteredVehicle->update(['user_id' => $user->id]);
                }

                $user->assignRole('customer');

                $userRegisteredVehicle->service_count = $userRegisteredVehicle->service_count + 1;
                $userRegisteredVehicle->vehicle_id = $vehicleId; // ensure correct vehicle_id assigned
                $userRegisteredVehicle->save();

                $vehicle = $userRegisteredVehicle;
            } else {
                // Vehicle does not exist in UserVehicle
                $user = User::create([
                    'name'      => !empty($request->customer_name) ? strtolower($request->customer_name) : 'customer',
                    'email'     => !empty($request->customer_email) ? $request->customer_email : 'customer_' . $vehicleRegNo . '@test.com',
                    'phone'     => $request->customer_phone ?? null,
                    'address'   => !empty($request->customer_address) ? strtolower($request->customer_address) : null,
                    'user_type' => 3,
                    'is_active' => 1,
                    'password'  => Hash::make('12345678'),
                ]);

                $user->assignRole('customer');

                $vehicle = UserVehicle::create([
                    'user_id'             => $user->id,
                    'vehicle_id'          => $vehicleId,
                    'registration_number' => $vehicleRegNo,
                    'service_count'       => 1,
                ]);
            }

            // ========================
            // Step 3: Always create service entry
            // ========================
            Service::create([
                'user_vehicle_id'       => $vehicle->id,
                'service_type_id'  => $request->service_type,
                'diesel'           => $request->filled('diesel') ? 1 : 0,
                'polish'           => $request->filled('polish') ? 1 : 0,
                'charges'          => $request->charges ?? 0,
                'discount'         => $request->discount ?? 0,
                'discount_reason'  => !empty($request->discount_reason) ? strtolower($request->discount_reason) : null,
                'collected_amount' => $request->collected_amount ?? 0,
                'payment_mode_id'  => $request->payment_mode_id ?? 0,
                'payment_status'   => 0,
                'overtime'   => $request->filled('overtime') ? 1 : 0,
                // 'created_at' => !empty($request->date) ? Carbon::parse($request->date) : now(),
                // 'updated_at' => !empty($request->date) ? Carbon::parse($request->date) : now(),
            ]);

            $vehicle = Vehicle::where('id', $vehicleId)->first();
            if (isset($vehicle)) {
                $vehicle->service_count = $vehicle->service_count + 1;
                $vehicle->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
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
        $data['record'] = Service::with('user_vehicle.vehicle.brand', 'user_vehicle')->where('id', $id)->first();
        return view('admin.services.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        try {
            $service = Service::where('id', $id)->first();
            if (isset($service)) {
                $service->service_type_id = $request->service_type;
                $service->diesel = $request->filled('diesel') ? 1 : 0;
                $service->polish = $request->filled('polish') ? 1 : 0;
                $service->charges = $request->charges ?? 0;
                $service->discount = $request->discount ?? 0;
                $service->discount_reason = strtolower($request->discount_reason) ?? null;
                $service->payment_mode_id = $request->payment_mode_id ?? 0;
                $service->overtime = $request->filled('overtime') ? 1 : 0;
                $service->created_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->created_at)->format('Y-m-d H:i:s');
                $service->updated_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->updated_at)->format('Y-m-d H:i:s');
                $service->save();
            }
            $vehicleBrandData = VehicleBrand::where('id', $request->vehicle_brand_id)->first();
            $vehicleData = Vehicle::where('id', $request->vehicle_id)->first();
            $userData = User::where('id', $service->user_vehicle?->user_id)->first();
            if (isset($userData)) {
                $userData->name = $request->customer_name ? $request->customer_name : 'customer';
                $userData->phone = $request->customer_phone ? $request->customer_phone : '';
                $userData->address = $request->customer_address ? $request->customer_address : 'address';
                $userData->user_address_id = $request->customer_address_id ? $request->customer_address_id : '';
                $customerEmail = '';
                if ($request->customer_email && $userData->email == str_replace(' ', '_', $request->customer_name) . '_' . str_replace('-', '_', $service->user_vehicle?->registration_number) . '@test.com') {
                    $customerEmail = $userData->email;
                } else {
                    $customerEmail = str_replace(' ', '_', $request->customer_name). '_' . str_replace('-', '_', $service->user_vehicle?->registration_number) . '@test.com';
                }
                $userData->email =  $customerEmail;
                $userData->save();
                $userData->assignRole('customer');
            }
            if (!isset($vehicleBrandData)) {
                $vehicleBrandData = new VehicleBrand();
                $vehicleBrandData->name = $request->vehicle_brand_name;
                $vehicleBrandData->save();
            }
            if (!isset($vehicleData)) {
                $vehicleData = new Vehicle();
                $vehicleData->name = $request->vehicle_name;
                $vehicleData->vehicle_brand_id = $vehicleBrandData->id;
                $vehicleData->save();
            }
            $userRegisteredVehicle = UserVehicle::where('registration_number', $request->vehicle_registration_number)->first();
            if (isset($userRegisteredVehicle)) {
                $userRegisteredVehicle->user_id = $userData->id;
                $userRegisteredVehicle->vehicle_id = $vehicleData->id;
                $userRegisteredVehicle->registration_number = $request->vehicle_registration_number;
                $userRegisteredVehicle->save();
            } else {
                UserVehicle::create([
                    'user_id' => $userData->id,
                    'vehicle_id' => $vehicleData->id,
                    'registration_number' => $request->vehicle_registration_number,
                ]);
            }
            return redirect()->back()->with('success', 'Updated successfully');
        } catch (\Exception $e) {
            dd($e->getMessage());
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
                    if ($service->overtime == 1) {
                        // 50% of charges minus discount
                        $service->collected_amount = $service->charges - $service->discount;
                        $service->overtime_amount = (int) round(($service->charges - $service->discount) * 0.5);
                    } else {
                        // Full charges minus discount
                        $service->collected_amount = $service->charges - $service->discount;
                    }
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

    public function updatePaymentMode(Request $request) {
        $service = Service::where('id', $request->service_id)->first();
        if (isset($service)) {
            if ($service->collected_amount == 0 && $service->payment_status == 0) {
                $service->payment_mode_id = $request->payment_mode_id;
                $service->save();
                return response()->json([
                    'status' => true
                ]);
            } else {
                return response()->json([
                    'status' => false
                ]);
            }
        } else {
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function complaint(Request $request) {
        $service = Service::where('id', $request->service_id)->first();
        if (isset($service)) {
            $service->complain = !$service->complain;
            $service->save();
            return response()->json([
                'status' => true
            ]);
        } else {
            return response()->json([
                'status' => false
            ]);
        }
    }

    public function updateAdditionalServices(Request $request)
    {
        $service = Service::findOrFail($request->service_id);

        $field = $request->input('field');
        $value = $request->input('value');

        if (in_array($field, ['luster','vaccum','diesel','complain','payment_mode_id'])) {
            $service->{$field} = $value;
            $service->save();
        }

        return response()->json(['status' => true]);
    }
}
