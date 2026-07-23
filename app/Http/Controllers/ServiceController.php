<?php

namespace App\Http\Controllers;

use Closure;
use DataTables;
use App\Models\User;
use App\Models\Service;
use App\Models\ServiceAddonRate;
use App\Models\ServiceCategoryRate;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleCategory;
use App\Models\ErrorLog;
use App\Models\UserVehicle;
use App\Models\VehicleBrand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function datatables($request, $records, $trashed = null)
    {
        $records = $records['records'];
        if (!empty($request->search['value'])) {
            $searchValue = $request->search['value'];
            $records = $records->where(function ($query) use ($searchValue) {
                // Search in vehicle (registration_number or name)
                $query->whereHas('user_vehicle', function($q) use ($searchValue) {
                    $q->where('registration_number', 'LIKE', "%{$searchValue}%")
                    ->orWhereHas('vehicle', function($vq) use ($searchValue) {
                        $vq->where('name', 'LIKE', "%{$searchValue}%");
                    });
                })
                // OR search in user_vehicle.user (phone)
                ->orWhereHas('user_vehicle.user', function($q) use ($searchValue) {
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
                return ucwords($row->user_vehicle?->vehicle?->name);
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
                return $row->user_vehicle?->user?->email;
            })
            ->addColumn('phone', function ($row) {
                return $row->user_vehicle?->user?->phone;
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
            $data['records'] = Service::with('user_vehicle.user', 'user_vehicle.vehicle', 'payment_mode', 'service_type')
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
        $data['serviceTypes'] = ServiceType::orderBy('name')->get(['id', 'name']);
        $data['categories'] = VehicleCategory::orderBy('id')->get();
        $data['rateMatrix'] = ServiceCategoryRate::all();
        $data['addonRates'] = ServiceAddonRate::all()->keyBy('key');
        return view('admin.services.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vehicle_registration_number' => 'required|string|max:32',
            'vehicle_brand_name'          => 'required|string',
            'vehicle_name'                => 'required|string',
            'vehicle_category_id'         => 'required|exists:vehicle_categories,id',
            'service_type'                => 'required|exists:service_types,id',
            'payment_mode_id'             => 'required|exists:payment_modes,id',
            'customer_name'               => 'required|string|min:2',
            'customer_phone'              => ['required', 'digits:11', Closure::fromCallable([$this, 'validatePhoneNotFake'])],
            'customer_address'            => 'required|string',
            'discount_type'               => 'required|in:amount,percent',
            'discount_value'              => ['nullable', $this->discountValueRule($request)],
            'date'                        => 'required|date',
            'color'                       => 'nullable|string|max:32',
            'model_year'                  => 'nullable|string|max:8',
            'cnic_number'                 => 'nullable|string|max:20',
            'vehicle_pic'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_pic'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cnic_pic'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'customer_phone.digits' => 'Phone number must be exactly 11 digits.',
        ]);

        $phone = preg_replace('/\D/', '', $request->customer_phone);
        // If the registration-number search already matched an existing customer,
        // their own phone number legitimately "already exists" — exclude them from
        // the duplicate check instead of blocking a new service for their own record.
        $duplicateUser = User::where('phone', $phone)
            ->when($request->current_user_id, fn ($q) => $q->where('id', '!=', $request->current_user_id))
            ->first();

        if ($duplicateUser && !$request->boolean('allow_duplicate_phone')) {
            $message = 'This phone number is already registered to "' . $duplicateUser->name . '". Check "Allow anyway" to proceed if this is correct.';
            return $this->jsonOrRedirectError($request, $message, ['customer_phone' => [$message]]);
        }

        DB::beginTransaction();

        try {
            $vehicleRegNo = strtolower(trim($request->vehicle_registration_number));

            $userRegisteredVehicle = UserVehicle::where('registration_number', $vehicleRegNo)->first();
            $user = null;
            $vehicle = null;

            // Uploaded photos: keep whatever was already on file for this registration
            // unless a new file was actually submitted.
            $vehiclePicPath = $this->resolveUploadedPath($request, 'vehicle_pic', $userRegisteredVehicle?->vehicle_pic, 'user_vehicles/vehicle_pics');
            $userPicPath = $this->resolveUploadedPath($request, 'user_pic', $userRegisteredVehicle?->user_pic, 'user_vehicles/user_pics');
            $cnicPicPath = $this->resolveUploadedPath($request, 'cnic_pic', $userRegisteredVehicle?->cnic_pic, 'user_vehicles/cnic_pics');

            // ========================
            // Step 1: Handle Vehicle Brand
            // ========================
            // vehicle_brand_id/vehicle_id are hidden fields set by autofill (registration
            // search or the brand/model autocomplete). If staff then corrects a typo in the
            // visible text (e.g. autofill showed "toyta", they fix it to "Toyota") while that
            // hidden id stays the same, propagate the correction onto the shared catalog row
            // instead of silently keeping the old, wrong name — this is master data shared by
            // every customer with that brand/model, not something specific to this service.
            $vehicleBrandId = $request->vehicle_brand_id;
            $submittedBrandName = !empty($request->vehicle_brand_name) ? strtolower(trim($request->vehicle_brand_name)) : null;
            if ($vehicleBrandId) {
                $brandRow = VehicleBrand::find($vehicleBrandId);
                if ($brandRow && $submittedBrandName && $brandRow->name !== $submittedBrandName) {
                    $brandRow->name = $submittedBrandName;
                    $brandRow->save();
                }
            } elseif ($submittedBrandName) {
                $brand = VehicleBrand::firstOrCreate(['name' => $submittedBrandName]);
                $vehicleBrandId = $brand->id;
            }

            // ========================
            // Step 2: Handle Vehicle (same correction-propagation logic as the brand above)
            // ========================
            $vehicleId = $request->vehicle_id;
            $submittedVehicleName = !empty($request->vehicle_name) ? strtolower(trim($request->vehicle_name)) : null;
            if ($vehicleId) {
                $vehicleCatalogRow = Vehicle::find($vehicleId);
                if ($vehicleCatalogRow) {
                    $dirty = false;
                    if ($submittedVehicleName && $vehicleCatalogRow->name !== $submittedVehicleName) {
                        $vehicleCatalogRow->name = $submittedVehicleName;
                        $dirty = true;
                    }
                    if ($vehicleBrandId && $vehicleCatalogRow->vehicle_brand_id != $vehicleBrandId) {
                        $vehicleCatalogRow->vehicle_brand_id = $vehicleBrandId;
                        $dirty = true;
                    }
                    if ($request->vehicle_category_id && $vehicleCatalogRow->vehicle_category_id != $request->vehicle_category_id) {
                        $vehicleCatalogRow->vehicle_category_id = $request->vehicle_category_id;
                        $dirty = true;
                    }
                    if ($dirty) {
                        $vehicleCatalogRow->save();
                    }
                }
            } elseif ($submittedVehicleName) {
                $vehicleCatalogRow = Vehicle::firstOrCreate(
                    ['name' => $submittedVehicleName],
                    ['vehicle_brand_id' => $vehicleBrandId, 'vehicle_category_id' => $request->vehicle_category_id]
                );
                $vehicleId = $vehicleCatalogRow->id;
            }

            $categoryId = $vehicleCatalogRow->vehicle_category_id ?? $request->vehicle_category_id;

            if (isset($userRegisteredVehicle)) {
                // Vehicle exists in UserVehicle
                $user = $userRegisteredVehicle->user;

                if (!$userRegisteredVehicle->user_id || !$user) {
                    $user = $user ?: ($duplicateUser ?: User::create([
                        'name'            => strtolower($request->customer_name),
                        'email'           => 'customer_' . $vehicleRegNo . '@test.com',
                        'phone'           => $phone,
                        'address'         => strtolower($request->customer_address),
                        'user_address_id' => $request->customer_address_id ?: null,
                        'user_type'       => $this->resolveCustomerRoleId(),
                        'is_active'       => 1,
                        'password'        => Hash::make('12345678'),
                    ]));

                    $userRegisteredVehicle->update(['user_id' => $user->id]);
                }

                $user->assignRole('customer');

                // Propagate corrections to the shared customer record (name/phone/address),
                // same reasoning as the vehicle brand/model sync above — explicitly excludes
                // pricing, which stays freshly computed per-service.
                $user->name = strtolower($request->customer_name);
                $user->phone = $phone;
                $user->address = strtolower($request->customer_address);
                $user->user_address_id = $request->customer_address_id ?: $user->user_address_id;
                $user->save();

                $userRegisteredVehicle->service_count = $userRegisteredVehicle->service_count + 1;
                $userRegisteredVehicle->vehicle_id = $vehicleId; // ensure correct vehicle_id assigned
                $userRegisteredVehicle->color = $request->filled('color') ? $request->color : $userRegisteredVehicle->color;
                $userRegisteredVehicle->model_year = $request->filled('model_year') ? $request->model_year : $userRegisteredVehicle->model_year;
                $userRegisteredVehicle->cnic_number = $request->filled('cnic_number') ? $request->cnic_number : $userRegisteredVehicle->cnic_number;
                $userRegisteredVehicle->vehicle_pic = $vehiclePicPath;
                $userRegisteredVehicle->user_pic = $userPicPath;
                $userRegisteredVehicle->cnic_pic = $cnicPicPath;
                $userRegisteredVehicle->save();

                $vehicle = $userRegisteredVehicle;
            } else {
                // Vehicle does not exist in UserVehicle — reuse an existing customer by phone
                // instead of always creating a new (possibly duplicate/fake) user record.
                $user = $duplicateUser ?: User::create([
                    'name'            => strtolower($request->customer_name),
                    'email'           => 'customer_' . $vehicleRegNo . '@test.com',
                    'phone'           => $phone,
                    'address'         => strtolower($request->customer_address),
                    'user_address_id' => $request->customer_address_id ?: null,
                    'user_type'       => $this->resolveCustomerRoleId(),
                    'is_active'       => 1,
                    'password'        => Hash::make('12345678'),
                ]);

                $user->assignRole('customer');

                if ($duplicateUser) {
                    $user->name = strtolower($request->customer_name);
                    $user->phone = $phone;
                    $user->address = strtolower($request->customer_address);
                    $user->user_address_id = $request->customer_address_id ?: $user->user_address_id;
                    $user->save();
                }

                $vehicle = UserVehicle::create([
                    'user_id'             => $user->id,
                    'vehicle_id'          => $vehicleId,
                    'registration_number' => $vehicleRegNo,
                    'service_count'       => 1,
                    'color'               => $request->color ?: null,
                    'model_year'          => $request->model_year ?: null,
                    'cnic_number'         => $request->cnic_number ?: null,
                    'vehicle_pic'         => $vehiclePicPath,
                    'user_pic'            => $userPicPath,
                    'cnic_pic'            => $cnicPicPath,
                ]);
            }

            // ========================
            // Step 3: Recompute money server-side from the rates table
            // (never trust client-supplied charges/discount amounts)
            // ========================
            $rate = ServiceCategoryRate::where('service_type_id', $request->service_type)
                ->where('vehicle_category_id', $categoryId)
                ->value('price') ?? 0;
            $addonRates = ServiceAddonRate::all()->keyBy('key');

            $charges = (float) $rate
                + ($request->filled('diesel') ? (float) ($addonRates['diesel']->price ?? 0) : 0)
                + ($request->filled('polish') ? (float) ($addonRates['polish']->price ?? 0) : 0);

            // discount_type decides how discount_value is interpreted: a fixed percentage
            // (validated against the preset list) or a direct Rs. amount.
            $discountValue = (float) ($request->discount_value ?? 0);
            $discount = $request->discount_type === 'percent'
                ? round($charges * $discountValue / 100, 2)
                : $discountValue;
            $discount = max(0, min($discount, $charges));

            // ========================
            // Step 4: Always create service entry
            // ========================
            Service::create([
                'user_vehicle_id'  => $vehicle->id,
                'service_type_id'  => $request->service_type,
                'diesel'           => $request->filled('diesel') ? 1 : 0,
                'polish'           => $request->filled('polish') ? 1 : 0,
                'charges'          => $charges,
                'discount'         => $discount,
                'discount_type'    => $request->discount_type,
                'discount_value'   => $discountValue,
                'discount_reason'  => !empty($request->discount_reason) ? strtolower($request->discount_reason) : null,
                'collected_amount' => 0,
                'payment_mode_id'  => $request->payment_mode_id,
                'payment_status'   => 0,
                'overtime'   => $request->filled('overtime') ? 1 : 0,
                'created_at' => !empty($request->date) ? Carbon::parse($request->date) : now(),
                'updated_at' => !empty($request->date) ? Carbon::parse($request->date) : now(),
            ]);

            $vehicleModelRow = Vehicle::where('id', $vehicleId)->first();
            if (isset($vehicleModelRow)) {
                $vehicleModelRow->service_count = $vehicleModelRow->service_count + 1;
                $vehicleModelRow->save();
            }

            DB::commit();

            return $this->jsonOrRedirectSuccess($request, 'Entry added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonOrRedirectError($request, 'Something went wrong: ' . $e->getMessage());
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
        $data['record'] = Service::with('user_vehicle.vehicle.brand', 'user_vehicle.vehicle.category', 'user_vehicle.user.user_address')->where('id', $id)->first();
        $data['serviceTypes'] = ServiceType::orderBy('name')->get(['id', 'name']);
        $data['categories'] = VehicleCategory::orderBy('id')->get();
        $data['rateMatrix'] = ServiceCategoryRate::all();
        $data['addonRates'] = ServiceAddonRate::all()->keyBy('key');
        return view('admin.services.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $service = Service::with('user_vehicle')->where('id', $id)->first();
        if (!$service) {
            return $this->jsonOrRedirectError($request, 'Service not found');
        }

        $request->validate([
            'vehicle_registration_number' => 'required|string|max:32',
            'vehicle_brand_name'          => 'required|string',
            'vehicle_name'                => 'required|string',
            'vehicle_category_id'         => 'required|exists:vehicle_categories,id',
            'service_type'                => 'required|exists:service_types,id',
            'payment_mode_id'             => 'required|exists:payment_modes,id',
            'customer_name'               => 'required|string|min:2',
            'customer_phone'              => ['required', 'digits:11', Closure::fromCallable([$this, 'validatePhoneNotFake'])],
            'customer_address'            => 'required|string',
            'discount_type'               => 'required|in:amount,percent',
            'discount_value'              => ['nullable', $this->discountValueRule($request)],
            'date'                        => 'required|date',
            'color'                       => 'nullable|string|max:32',
            'model_year'                  => 'nullable|string|max:8',
            'cnic_number'                 => 'nullable|string|max:20',
            'vehicle_pic'                 => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_pic'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cnic_pic'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'customer_phone.digits' => 'Phone number must be exactly 11 digits.',
        ]);

        $phone = preg_replace('/\D/', '', $request->customer_phone);
        $currentUserId = $service->user_vehicle?->user_id;

        $duplicateUser = User::where('phone', $phone)->where('id', '!=', $currentUserId)->first();
        if ($duplicateUser && !$request->boolean('allow_duplicate_phone')) {
            $message = 'This phone number is already registered to "' . $duplicateUser->name . '". Check "Allow anyway" to proceed if this is correct.';
            return $this->jsonOrRedirectError($request, $message, ['customer_phone' => [$message]]);
        }

        $vehicleRegNo = strtolower(trim($request->vehicle_registration_number));
        $userRegisteredVehicle = $service->user_vehicle;

        if ($userRegisteredVehicle && $userRegisteredVehicle->registration_number !== $vehicleRegNo) {
            $ownedByOther = UserVehicle::where('registration_number', $vehicleRegNo)
                ->where('id', '!=', $userRegisteredVehicle->id)
                ->first();
            if ($ownedByOther) {
                $message = 'This registration number is already assigned to a different vehicle/customer.';
                return $this->jsonOrRedirectError($request, $message, ['vehicle_registration_number' => [$message]]);
            }
        }

        DB::beginTransaction();

        try {
            // Uploaded photos: keep whatever was already on file for this registration
            // unless a new file was actually submitted.
            $vehiclePicPath = $this->resolveUploadedPath($request, 'vehicle_pic', $userRegisteredVehicle?->vehicle_pic, 'user_vehicles/vehicle_pics');
            $userPicPath = $this->resolveUploadedPath($request, 'user_pic', $userRegisteredVehicle?->user_pic, 'user_vehicles/user_pics');
            $cnicPicPath = $this->resolveUploadedPath($request, 'cnic_pic', $userRegisteredVehicle?->cnic_pic, 'user_vehicles/cnic_pics');

            // Resolve the vehicle brand/model catalog rows first — charges now depend on
            // the vehicle's category, so this must happen before the money computation.
            // As in store(), if the hidden id is already set but staff corrected the visible
            // text (typo fix), propagate that correction onto the shared catalog row rather
            // than silently discarding it.
            $submittedBrandName = !empty($request->vehicle_brand_name) ? strtolower(trim($request->vehicle_brand_name)) : null;
            $vehicleBrandData = VehicleBrand::find($request->vehicle_brand_id);
            if ($vehicleBrandData) {
                if ($submittedBrandName && $vehicleBrandData->name !== $submittedBrandName) {
                    $vehicleBrandData->name = $submittedBrandName;
                    $vehicleBrandData->save();
                }
            } else {
                $vehicleBrandData = VehicleBrand::firstOrCreate(['name' => $submittedBrandName]);
            }

            $submittedVehicleName = !empty($request->vehicle_name) ? strtolower(trim($request->vehicle_name)) : null;
            $vehicleData = Vehicle::find($request->vehicle_id);
            if ($vehicleData) {
                $dirty = false;
                if ($submittedVehicleName && $vehicleData->name !== $submittedVehicleName) {
                    $vehicleData->name = $submittedVehicleName;
                    $dirty = true;
                }
                if ($vehicleBrandData->id && $vehicleData->vehicle_brand_id != $vehicleBrandData->id) {
                    $vehicleData->vehicle_brand_id = $vehicleBrandData->id;
                    $dirty = true;
                }
                if ($request->vehicle_category_id && $vehicleData->vehicle_category_id != $request->vehicle_category_id) {
                    $vehicleData->vehicle_category_id = $request->vehicle_category_id;
                    $dirty = true;
                }
                if ($dirty) {
                    $vehicleData->save();
                }
            } else {
                $vehicleData = Vehicle::firstOrCreate(
                    ['name' => $submittedVehicleName],
                    ['vehicle_brand_id' => $vehicleBrandData->id, 'vehicle_category_id' => $request->vehicle_category_id]
                );
            }
            $categoryId = $vehicleData->vehicle_category_id ?? $request->vehicle_category_id;

            // Recompute money server-side from the rates table (never trust the client).
            $rate = ServiceCategoryRate::where('service_type_id', $request->service_type)
                ->where('vehicle_category_id', $categoryId)
                ->value('price') ?? 0;
            $addonRates = ServiceAddonRate::all()->keyBy('key');

            $charges = (float) $rate
                + ($request->filled('diesel') ? (float) ($addonRates['diesel']->price ?? 0) : 0)
                + ($request->filled('polish') ? (float) ($addonRates['polish']->price ?? 0) : 0);

            // discount_type decides how discount_value is interpreted: a fixed percentage
            // (validated against the preset list) or a direct Rs. amount.
            $discountValue = (float) ($request->discount_value ?? 0);
            $discount = $request->discount_type === 'percent'
                ? round($charges * $discountValue / 100, 2)
                : $discountValue;
            $discount = max(0, min($discount, $charges));

            $service->service_type_id = $request->service_type;
            $service->diesel = $request->filled('diesel') ? 1 : 0;
            $service->polish = $request->filled('polish') ? 1 : 0;
            $service->charges = $charges;
            $service->discount = $discount;
            $service->discount_type = $request->discount_type;
            $service->discount_value = $discountValue;
            $service->discount_reason = !empty($request->discount_reason) ? strtolower($request->discount_reason) : null;
            $service->payment_mode_id = $request->payment_mode_id;
            $service->overtime = $request->filled('overtime') ? 1 : 0;
            $service->created_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->created_at)->format('Y-m-d H:i:s');
            $service->updated_at = !empty($request->date) ? Carbon::parse($request->date)->format('Y-m-d H:i:s') : Carbon::parse($service->updated_at)->format('Y-m-d H:i:s');
            $service->save();

            $userData = User::find($currentUserId);
            if (!$userData) {
                $userData = $duplicateUser ?: new User([
                    'user_type' => $this->resolveCustomerRoleId(),
                    'is_active' => 1,
                    'password'  => Hash::make('12345678'),
                ]);
            }
            $userData->name = strtolower($request->customer_name);
            $userData->phone = $phone;
            $userData->address = strtolower($request->customer_address);
            $userData->user_address_id = $request->customer_address_id ?: null;
            if (empty($userData->email)) {
                $userData->email = 'customer_' . str_replace('-', '_', $vehicleRegNo) . '@test.com';
            }
            $userData->save();
            $userData->assignRole('customer');

            if ($userRegisteredVehicle) {
                $userRegisteredVehicle->user_id = $userData->id;
                $userRegisteredVehicle->vehicle_id = $vehicleData->id;
                $userRegisteredVehicle->registration_number = $vehicleRegNo;
                $userRegisteredVehicle->color = $request->filled('color') ? $request->color : $userRegisteredVehicle->color;
                $userRegisteredVehicle->model_year = $request->filled('model_year') ? $request->model_year : $userRegisteredVehicle->model_year;
                $userRegisteredVehicle->cnic_number = $request->filled('cnic_number') ? $request->cnic_number : $userRegisteredVehicle->cnic_number;
                $userRegisteredVehicle->vehicle_pic = $vehiclePicPath;
                $userRegisteredVehicle->user_pic = $userPicPath;
                $userRegisteredVehicle->cnic_pic = $cnicPicPath;
                $userRegisteredVehicle->save();
            } else {
                $userRegisteredVehicle = UserVehicle::create([
                    'user_id'             => $userData->id,
                    'vehicle_id'          => $vehicleData->id,
                    'registration_number' => $vehicleRegNo,
                    'service_count'       => 1,
                    'color'               => $request->color ?: null,
                    'model_year'          => $request->model_year ?: null,
                    'cnic_number'         => $request->cnic_number ?: null,
                    'vehicle_pic'         => $vehiclePicPath,
                    'user_pic'            => $userPicPath,
                    'cnic_pic'            => $cnicPicPath,
                ]);
                $service->user_vehicle_id = $userRegisteredVehicle->id;
                $service->save();
            }

            DB::commit();

            return $this->jsonOrRedirectSuccess($request, 'Updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->jsonOrRedirectError($request, 'Something went wrong: ' . $e->getMessage());
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
                'status' => false,
                'msg' => $e->getMessage()
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

    /**
     * AJAX: check whether a phone number is already registered to a different customer.
     * Used by the add/edit service form to gate the submit button.
     */
    public function checkPhoneDuplicate(Request $request)
    {
        $phone = preg_replace('/\D/', '', (string) $request->phone);

        if (strlen($phone) !== 11) {
            return response()->json(['status' => true, 'exists' => false]);
        }

        $user = User::where('phone', $phone)
            ->when($request->exclude_user_id, function ($query) use ($request) {
                $query->where('id', '!=', $request->exclude_user_id);
            })
            ->first();

        return response()->json([
            'status' => true,
            'exists' => (bool) $user,
            'name'   => $user?->name,
        ]);
    }

    /**
     * Respond with a validation-style error: JSON for AJAX submissions (so the front-end
     * can show it inline without a page reload / losing what the user typed), a classic
     * redirect-with-flash for plain form posts (e.g. JS disabled).
     */
    private function jsonOrRedirectError(Request $request, string $message, array $errors = [])
    {
        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => $message,
                'errors' => $errors ?: ['error' => [$message]],
            ], 422);
        }

        $redirect = redirect()->back()->withInput();
        return $errors ? $redirect->withErrors($errors) : $redirect->with('error', $message);
    }

    /**
     * Respond with success: JSON for AJAX submissions, redirect-with-flash otherwise.
     */
    private function jsonOrRedirectSuccess(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['status' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * discount_value means different things depending on discount_type: a fixed
     * percentage (must be one of the preset options) or a free-form Rs. amount.
     */
    private function discountValueRule(Request $request)
    {
        return function ($attribute, $value, $fail) use ($request) {
            if ($value === null || $value === '') {
                return;
            }
            if ($request->discount_type === 'percent') {
                if (!in_array((int) $value, [0, 10, 20, 25, 30, 35, 40, 45, 50], true)) {
                    $fail('The discount must be one of the fixed percentages (10, 20, 25, 30, 35, 40, 45, 50).');
                }
            } elseif (!is_numeric($value) || $value < 0) {
                $fail('The discount amount must be a valid positive number.');
            }
        };
    }

    /**
     * Store a newly uploaded file (if one was submitted for $field), deleting whatever
     * was previously stored at $existingPath. If no new file was submitted, the existing
     * path is returned unchanged — editing a service must never blank out a photo that
     * was already on file just because the field was left empty on this submission.
     */
    private function resolveUploadedPath(Request $request, string $field, ?string $existingPath, string $directory)
    {
        if ($request->hasFile($field)) {
            if ($existingPath) {
                Storage::disk('public')->delete($existingPath);
            }
            return $request->file($field)->store($directory, 'public');
        }

        return $existingPath;
    }

    /**
     * Resolve the "customer" role id used for User::user_type. This must never be
     * hardcoded (the id depends on seeding order — AclSeeder creates admin/manager/
     * user first) or new customers silently get miscategorized as staff.
     */
    private function resolveCustomerRoleId()
    {
        return \Spatie\Permission\Models\Role::firstOrCreate(
            ['name' => 'customer', 'guard_name' => 'web']
        )->id;
    }

    /**
     * Validation rule callback: rejects obviously fake phone numbers
     * (all repeated digits, or a straight ascending/descending sequence).
     */
    public function validatePhoneNotFake($attribute, $value, $fail)
    {
        if (preg_match('/^(\d)\1*$/', $value)) {
            $fail('Please enter a real phone number.');
            return;
        }

        $ascending = true;
        $descending = true;
        for ($i = 1; $i < strlen($value); $i++) {
            if ((int) $value[$i] - (int) $value[$i - 1] !== 1) {
                $ascending = false;
            }
            if ((int) $value[$i - 1] - (int) $value[$i] !== 1) {
                $descending = false;
            }
        }

        if ($ascending || $descending) {
            $fail('Please enter a real phone number.');
        }
    }
}
