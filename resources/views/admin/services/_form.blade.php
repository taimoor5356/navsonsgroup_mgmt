@section('_styles')
    <style>
        #brandList {
            border-radius: 0 0 .375rem .375rem; /* rounded bottom corners */
        }
        #brandList .list-group-item {
            cursor: pointer;
        }
        #brandList .list-group-item:hover {
            background-color: #f1f1f1;
        }
        #vehicle-brand-name {
            text-align: left !important;
        }
        .form-control {
            text-align: left;
        }
        .readonly-field {
            background-color: #f5f5f9;
        }
        .form-section-title {
            text-transform: uppercase;
            font-size: .8125rem;
            font-weight: 600;
            letter-spacing: .02em;
            color: #6c757d;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: .5rem;
            margin-bottom: .25rem;
        }
        .form-section-title:not(:first-child) {
            margin-top: .5rem;
        }
        .existing-pic-preview {
            display: block;
            max-height: 90px;
            border-radius: .375rem;
            margin-bottom: .5rem;
            border: 1px solid #e0e0e0;
        }
    </style>
@endsection
<div class="row">
    @php
        $diesel = 0;
        $polish = 0;
        $serviceTypeId = 0;
        $charges = 0;
        $discountType = 'amount';
        $discountValue = 0;
        $discountAmount = 0;
        $discountReason = '';
        $collectedAmount = 0;
        $overtime = 0;
        $paymentModeId = 0;
        $vehicleBrandId = '';
        $vehicleBrandName = '';
        $vehicleId = '';
        $vehicleName = '';
        $vehicleCategoryId = '';
        $registrationNumber = '';
        $currentUserId = '';
        $color = '';
        $modelYear = '';
        $cnicNumber = '';
        $vehiclePicPath = '';
        $userPicPath = '';
        $cnicPicPath = '';
        if (isset($record)) {
            // These come straight off the service row and must populate regardless of
            // whether the linked vehicle catalog entry resolved below.
            $diesel = $record->diesel;
            $polish = $record->polish;
            $serviceTypeId = $record->service_type_id;
            $charges = $record->charges;
            $discountType = $record->discount_type ?? 'amount';
            $discountValue = $record->discount_value ?? $record->discount;
            $discountAmount = $record->discount;
            $discountReason = $record->discount_reason;
            $collectedAmount = $record->collected_amount;
            $overtime = $record->overtime;
            $paymentModeId = $record->payment_mode_id;
            $paymentStatus = $record->payment_status;
            $currentUserId = $record->user_vehicle?->user_id;
            $registrationNumber = $record->user_vehicle?->registration_number;
            // These live directly on user_vehicles (per-registration, not the shared
            // vehicle/user catalog rows), so they don't depend on the vehicle relation either.
            $color = $record->user_vehicle?->color;
            $modelYear = $record->user_vehicle?->model_year;
            $cnicNumber = $record->user_vehicle?->cnic_number;
            $vehiclePicPath = $record->user_vehicle?->vehicle_pic;
            $userPicPath = $record->user_vehicle?->user_pic;
            $cnicPicPath = $record->user_vehicle?->cnic_pic;

            if (isset($record->user_vehicle?->vehicle)) {
                $vehicleBrandId = $record->user_vehicle?->vehicle?->brand?->id;
                $vehicleBrandName = $record->user_vehicle?->vehicle?->brand?->name;
                $vehicleId = $record->user_vehicle?->vehicle?->id;
                $vehicleName = $record->user_vehicle?->vehicle?->name;
                $vehicleCategoryId = $record->user_vehicle?->vehicle?->vehicle_category_id;
            }
        }
    @endphp
    <input type="hidden" id="current-user-id" name="current_user_id" value="{{ $currentUserId }}">
    <div class="col-12">
        <h6 class="form-section-title">Vehicle Details</h6>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="vehicle-registration-number">Vehicle Registration Number</label>
        <div class="input-group input-group-merge mb-1">
            <!-- Left Icon -->
            <span id="vehicle-registration-number2" class="input-group-text">
                <i class="bx bx-file"></i>
            </span>

            <!-- Input Field -->
            <input type="text" name="vehicle_registration_number"
                value="{{ old('vehicle_registration_number', isset($record) ? $registrationNumber : '') }}"
                class="form-control"
                id="vehicle-registration-number"
                placeholder="Enter Vehicle Registration Number"
                aria-label="va-123"
                aria-describedby="vehicle-registration-number2" required>

            <!-- Right Search Icon -->
            <a href="#" id="search-vehicle" class="input-group-text bg-primary">
                <i class="bx bx-search text-white search-button search-icon"></i>
                <i class="bx bx-loader text-dark search-button loader-icon d-none"></i>
            </a>
        </div>
        <small class="bg-danger text-white rounded p-1 pending-amount d-none">Pending Amount:</small>
    </div>
    <div class="mb-3 col-md-6 col-12 position-relative">
        <label class="form-label" for="vehicle-brand-name">Vehicle Brand Name</label>
        <div class="input-group input-group-merge">
            <span id="vehicle-brand-name2" class="input-group-text"><i class="bx bx-car"></i></span>
            <input type="text" name="vehicle_brand_name"
                value="{{ old('vehicle_brand_name', isset($record) ? $vehicleBrandName : '') }}"
                class="form-control hidden-input"
                id="vehicle-brand-name"
                placeholder="Enter Vehicle Brand Name"
                autocomplete="off" style="text-align: left;" required>
            <input type="hidden" id="vehicle-brand-id" name="vehicle_brand_id" value="{{ old('vehicle_brand_id', isset($record) ? $vehicleBrandId : '') }}">
        </div>

        <!-- Suggestions dropdown -->
        <ul id="vehicle-brand-suggestions"
            class="list-group position-absolute w-100"
            style="z-index:1000; max-height:200px; overflow-y:auto; background-color: white; display: none;">
        </ul>
    </div>
    <div class="mb-3 col-md-6 col-12 position-relative">
        <label class="form-label" for="vehicle-name">Vehicle Model Name</label>
        <div class="input-group input-group-merge">
            <span id="vehicle-name2" class="input-group-text"><i class="bx bx-car"></i></span>
            <input type="text" name="vehicle_name"
                value="{{ old('vehicle_name', isset($record) ? $vehicleName : '') }}"
                class="form-control hidden-input"
                id="vehicle-name"
                placeholder="Enter Vehicle Name"
                autocomplete="off" style="text-align: left;" required>
            <input type="hidden" id="vehicle-id" name="vehicle_id" value="{{ old('vehicle_id', isset($record) ? $vehicleId : '') }}">
        </div>

        <!-- Suggestions dropdown -->
        <ul id="vehicle-suggestions"
            class="list-group position-absolute w-100"
            style="z-index:1000; max-height:200px; overflow-y:auto; background-color: white; display: none;">
        </ul>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="vehicle-category">Vehicle Category</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-car"></i></span>
            <select name="vehicle_category_id" id="vehicle-category" class="form-control" required>
                <option value="" selected disabled>Select Vehicle Category</option>
                @foreach (($categories ?? \App\Models\VehicleCategory::orderBy('id')->get()) as $category)
                    <option value="{{$category->id}}" {{ old('vehicle_category_id', isset($record) ? $vehicleCategoryId : '') == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="color">Color</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-palette"></i></span>
            <input type="text" name="color" value="{{ old('color', isset($record) ? $color : '') }}" id="color" class="form-control" placeholder="Enter Vehicle Color">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="model-year">Model Year</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-calendar"></i></span>
            <input type="text" name="model_year" value="{{ old('model_year', isset($record) ? $modelYear : '') }}" id="model-year" class="form-control" placeholder="e.g. 2021" maxlength="8">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="vehicle-pic">Vehicle Photo</label>
        @if (isset($record) && $vehiclePicPath)
            <img src="{{ asset('storage/' . $vehiclePicPath) }}" class="existing-pic-preview" alt="Current vehicle photo">
        @endif
        <div class="input-group">
            <input type="file" name="vehicle_pic" id="vehicle-pic" class="form-control" accept="image/png,image/jpeg,image/jpg" capture="environment">
            <button type="button" class="btn btn-outline-primary camera-capture-btn" data-target-input="vehicle-pic" title="Take Photo">
                <i class="bx bx-camera"></i>
            </button>
        </div>
        <small class="text-muted d-none" id="plate-ocr-status"></small>
    </div>

    <div class="col-12">
        <h6 class="form-section-title">Pricing</h6>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="service_type">
            <div>
                Select Service Type
            </div>
            <div>
                <input type="checkbox" name="diesel" id="diesel-checkbox" class="mt-1" {{ old('diesel', isset($record) ? $diesel : 0) == 1 ? 'checked' : '' }}> Diesel
            </div>
            <div>
                <input type="checkbox" name="polish" id="polish-checkbox" class="mt-1" {{ old('polish', isset($record) ? $polish : 0) == 1 ? 'checked' : '' }}> Polish
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="service_type" id="service_type" class="form-control hidden-input" required>
                <option value="" selected disabled>Select Service Type</option>
                @foreach (($serviceTypes ?? \App\Models\ServiceType::get(['id','name'])) as $serviceType)
                    <option value="{{$serviceType->id}}" {{ old('service_type', isset($record) ? $serviceTypeId : '') == $serviceType->id ? 'selected' : '' }}>{{$serviceType->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="charges">
            <div>
                Charges <small class="text-muted">(auto-calculated)</small>
            </div>
            <div>
                <input type="checkbox" name="overtime" class="mt-1" {{ old('overtime', isset($record) ? $overtime : 0) == 1 ? 'checked' : '' }}> OverTime
            </div>
        </label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="number" name="charges" value="{{isset($record) ? $charges : 0}}" id="charges" class="form-control readonly-field" placeholder="Select service type / add-ons" aria-label="1000" aria-describedby="charges2" readonly>
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="discount-value-percent">Discount</label>
        @php
            $fixedDiscounts = [10, 20, 25, 30, 35, 40, 45, 50];
            $isPercentDiscount = isset($record) && $discountType === 'percent';
            $selectedDiscountPercent = ($isPercentDiscount && in_array((int) $discountValue, $fixedDiscounts))
                ? (int) $discountValue
                : 0;
        @endphp
        <div class="input-group input-group-merge">
            <select name="discount_type" id="discount-type" class="form-control hidden-input" style="max-width: 100px; flex: 0 0 100px;">
                <option value="amount" {{ old('discount_type', isset($record) ? $discountType : 'amount') == 'amount' ? 'selected' : '' }}>Rs.</option>
                <option value="percent" {{ old('discount_type', isset($record) ? $discountType : 'amount') == 'percent' ? 'selected' : '' }}>%</option>
            </select>
            <select name="discount_value" id="discount-value-percent" class="form-control hidden-input {{ old('discount_type', isset($record) ? $discountType : 'amount') == 'percent' ? '' : 'd-none' }}">
                <option value="0" {{ old('discount_value', isset($record) ? $selectedDiscountPercent : 0) == 0 ? 'selected' : '' }}>No Discount</option>
                @foreach ($fixedDiscounts as $percent)
                    <option value="{{ $percent }}" {{ old('discount_value', isset($record) ? $selectedDiscountPercent : 0) == $percent ? 'selected' : '' }}>{{ $percent }}%</option>
                @endforeach
            </select>
            <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', isset($record) && !$isPercentDiscount ? $discountValue : '') }}" id="discount-value-amount" class="form-control hidden-input {{ old('discount_type', isset($record) ? $discountType : 'amount') == 'percent' ? 'd-none' : '' }}" placeholder="Enter Discount Amount" aria-label="100">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="discount-amount">Discount Amount <small class="text-muted">(auto-calculated)</small></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="text" id="discount-amount" class="form-control readonly-field" readonly value="{{isset($record) ? $discountAmount : 0}}">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="discount-reason">Description <small>(Discount Reason / Complain)</small></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-file"></i></span>
            <input type="text" name="discount_reason" value="{{ old('discount_reason', isset($record) ? $discountReason : '') }}" id="discount-reason" class="form-control hidden-input" placeholder="Discount Reason or Complain" aria-label="Friend" aria-describedby="discount-reason2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="total-bill">Total Bill Amount <small class="text-muted">(auto-calculated)</small></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-receipt"></i></span>
            <input type="text" id="total-bill" class="form-control readonly-field" readonly value="{{isset($record) ? ($charges - $discountAmount) : 0}}">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="payment-type">
            <div>
                Payment Mode
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="payment_mode_id" id="payment_mode_id" class="form-control hidden-input" required>
                <option value="" selected disabled>Payment Mode</option>
                @foreach (\App\Models\PaymentMode::get() as $paymentMode)
                    <option value="{{$paymentMode->id}}" {{ old('payment_mode_id', isset($record) ? $paymentModeId : '') == $paymentMode->id ? 'selected' : '' }}>{{strtoupper(str_replace('_', ' ', $paymentMode->name))}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12">
        <h6 class="form-section-title">Customer Details</h6>
    </div>
    <div class="col-12 customer-info">
        <div class="row">
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="customer-name">Customer Name</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" name="customer_name" value="{{ old('customer_name', isset($record) ? $record->user_vehicle?->user?->name : '') }}" id="customer-name" class="form-control hidden-input" placeholder="Enter Customer Name" aria-label="john.doe" aria-describedby="customer-name2" required>
                </div>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label d-flex justify-content-between align-items-center" for="customer-phone">
                    <div>Customer Phone</div>
                    <div class="form-check d-none" id="allow-duplicate-phone-wrapper">
                        <input type="checkbox" class="form-check-input" id="allow-duplicate-phone" name="allow_duplicate_phone" value="1">
                        <label class="form-check-label" for="allow-duplicate-phone">Allow anyway</label>
                    </div>
                </label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone', isset($record) ? $record->user_vehicle?->user?->phone : '') }}" id="customer-phone" class="form-control hidden-input" placeholder="Enter 11-digit Customer Phone" inputmode="numeric" maxlength="11" aria-label="03001234567" aria-describedby="customer-phone2" required>
                    <span class="input-group-text bg-white d-none" id="phone-check-loader"><i class="bx bx-loader-alt bx-spin"></i></span>
                </div>
                <small class="text-danger d-none" id="phone-duplicate-warning"></small>
                <input type="hidden" name="customer_email" value="{{ old('customer_email', isset($record) ? $record->user_vehicle?->user?->email : '') }}" id="customer-email">
            </div>

            <div class="mb-3 col-md-6 col-12 position-relative">
                <label class="form-label" for="customer-address">Customer Address</label>
                <div class="input-group input-group-merge">
                    <span id="customer-address2" class="input-group-text"><i class="bx bx-car"></i></span>
                    <input type="text" name="customer_address"
                        value="{{ old('customer_address', isset($record) ? ($record->user_vehicle?->user?->user_address?->name ?? $record->user_vehicle?->user?->address) : '') }}"
                        class="form-control hidden-input"
                        id="customer-address"
                        placeholder="Enter Customer Address"
                        autocomplete="off" style="text-align: left;" required>
                    <input type="hidden" id="customer-address-id" name="customer_address_id" value="{{ old('customer_address_id', isset($record) ? $record->user_vehicle?->user?->user_address?->id : '') }}">
                </div>

                <!-- Suggestions dropdown -->
                <ul id="customer-address-suggestions"
                    class="list-group position-absolute w-100"
                    style="z-index:1000; max-height:200px; overflow-y:auto; background-color: white; display: none;">
                </ul>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="user-pic">Photo of Person</label>
                @if (isset($record) && $userPicPath)
                    <img src="{{ asset('storage/' . $userPicPath) }}" class="existing-pic-preview" alt="Current photo">
                @endif
                <div class="input-group">
                    <input type="file" name="user_pic" id="user-pic" class="form-control" accept="image/png,image/jpeg,image/jpg" capture="environment">
                    <button type="button" class="btn btn-outline-primary camera-capture-btn" data-target-input="user-pic" title="Take Photo">
                        <i class="bx bx-camera"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="cnic-number">CNIC Number</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-id-card"></i></span>
                    <input type="text" name="cnic_number" value="{{ old('cnic_number', isset($record) ? $cnicNumber : '') }}" id="cnic-number" class="form-control" placeholder="e.g. 12345-1234567-1" maxlength="20">
                </div>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="cnic-pic">CNIC Photo</label>
                @if (isset($record) && $cnicPicPath)
                    <img src="{{ asset('storage/' . $cnicPicPath) }}" class="existing-pic-preview" alt="Current CNIC photo">
                @endif
                <div class="input-group">
                    <input type="file" name="cnic_pic" id="cnic-pic" class="form-control" accept="image/png,image/jpeg,image/jpg" capture="environment">
                    <button type="button" class="btn btn-outline-primary camera-capture-btn" data-target-input="cnic-pic" title="Take Photo">
                        <i class="bx bx-camera"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h6 class="form-section-title">Date</h6>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="date">
            <div>
                Date
            </div>
        </label>
        <div class="input-group">
            <input type="date" name="date" id="date" value="{{ old('date', isset($record) ? \Carbon\Carbon::parse($record->created_at)->format('Y-m-d') : \Carbon\Carbon::now()->format('Y-m-d')) }}" class="form-control hidden-input" required>
        </div>
    </div>
</div>

<!-- Shared camera-capture modal, reused by the vehicle/person/CNIC "Take Photo" buttons -->
<div class="modal fade" id="camera-capture-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Take Photo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <video id="camera-video" autoplay playsinline muted style="width:100%; max-height:60vh; background:#000; border-radius:.375rem;"></video>
                <canvas id="camera-canvas" class="d-none" style="width:100%; max-height:60vh; border-radius:.375rem;"></canvas>
                <div class="alert alert-danger mt-2 d-none" id="camera-error"></div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-outline-secondary" id="camera-switch-btn"><i class="bx bx-refresh"></i> Switch Camera</button>
                <button type="button" class="btn btn-primary" id="camera-capture-shot-btn"><i class="bx bx-camera"></i> Capture</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="camera-retake-btn">Retake</button>
                <button type="button" class="btn btn-success d-none" id="camera-use-photo-btn">Use Photo</button>
            </div>
        </div>
    </div>
</div>

@section('_scripts')
<script>
    var dieselPrice = parseFloat("{{ (float) ($addonRates['diesel']->price ?? 0) }}");
    var polishPrice = parseFloat("{{ (float) ($addonRates['polish']->price ?? 0) }}");
    var rateMatrix = @json(($rateMatrix ?? collect())->groupBy('service_type_id')->map(function ($rows) {
        return $rows->keyBy('vehicle_category_id')->map(function ($row) {
            return (float) $row->price;
        });
    }));

   $(document).ready(function() {
        $('.hidden-input').attr('readonly', true);

        var storageBaseUrl = "{{ asset('storage') }}";

        function showExistingPic(inputSelector, path) {
            var $input = $(inputSelector);
            var $existing = $input.prev('img.existing-pic-preview');
            if (!path) {
                $existing.remove();
                return;
            }
            var src = storageBaseUrl + '/' + path;
            if ($existing.length) {
                $existing.attr('src', src);
            } else {
                $input.before('<img src="' + src + '" class="existing-pic-preview" alt="Current photo">');
            }
        }

        // Registration-number search reloads photo previews from whatever is
        // stored server-side for that record — but if staff already captured/
        // chose a photo locally (the file input already has a file queued) in
        // this session, that's not something the search response knows about
        // or is "linked" to, so it must never clear or replace it.
        function updatePicFromSearch(inputSelector, path) {
            var input = document.querySelector(inputSelector);
            if (input && input.files && input.files.length > 0) {
                return;
            }
            showExistingPic(inputSelector, path);
        }

        // ------------------------------------------------------------------
        // Auto-read the registration number off the Vehicle Photo using
        // Tesseract.js — free, open-source OCR that runs entirely client-side
        // (WASM in the browser), so it costs nothing and needs no server-side
        // OCR service or binary (this host's shared hosting can't run one
        // anyway). Loaded lazily from a CDN only once a vehicle photo is
        // actually supplied, since the library is a multi-MB download.
        // Accuracy is best-effort: it only fills the field if it isn't
        // already filled, and staff should always verify/correct the result
        // rather than trust it blindly — a whole-vehicle photo is a much
        // harder OCR target than a cropped plate close-up.
        // ------------------------------------------------------------------
        var tesseractLoadPromise = null;

        function loadTesseract() {
            if (window.Tesseract) {
                return Promise.resolve();
            }
            if (!tesseractLoadPromise) {
                tesseractLoadPromise = new Promise(function (resolve, reject) {
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js';
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            }
            return tesseractLoadPromise;
        }

        // A plain photo of the whole vehicle has the plate as a small part of a
        // much noisier scene (chrome trim, reflections, other badges/text), so
        // Tesseract's document-oriented defaults read it poorly. Configuring
        // the worker explicitly makes a real difference:
        //  - PSM 11 ("sparse text") looks for isolated text anywhere in the
        //    frame instead of assuming a structured page layout.
        //  - A character whitelist stops it from ever guessing symbols/lowercase
        //    letters that a plate would never contain, which cuts out a lot of
        //    noise picked up from the rest of the photo.
        // The worker is created once and reused for subsequent scans.
        var tesseractWorkerPromise = null;

        function getTesseractWorker() {
            if (!tesseractWorkerPromise) {
                tesseractWorkerPromise = loadTesseract()
                    .then(function () {
                        return Tesseract.createWorker('eng');
                    })
                    .then(function (worker) {
                        return worker.setParameters({
                            tessedit_pageseg_mode: '11', // PSM.SPARSE_TEXT
                            tessedit_char_whitelist: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789 -.'
                        }).then(function () {
                            return worker;
                        });
                    });
            }
            return tesseractWorkerPromise;
        }

        function extractPlateGuess(rawText) {
            // Some Pakistani plates use a decorative "." between the letters and
            // digits (e.g. "RIW . 1392") — drop it before matching so it doesn't
            // break the pattern.
            var text = (rawText || '').toUpperCase().replace(/\./g, ' ').replace(/[^A-Z0-9\s-]/g, ' ');
            var candidates = [];
            var m;

            var letterFirstRe = /([A-Z]{2,3})[\s-]*(\d{2,4})/g;
            while ((m = letterFirstRe.exec(text)) !== null) {
                candidates.push(m[1] + '-' + m[2]);
            }

            var digitFirstRe = /(\d{2,4})[\s-]*([A-Z]{2,3})/g;
            while ((m = digitFirstRe.exec(text)) !== null) {
                candidates.push(m[2] + '-' + m[1]);
            }

            if (!candidates.length) {
                return null;
            }

            // Prefer the longest combined letters+digits match — more specific,
            // less likely to be an incidental noise match elsewhere in the photo.
            candidates.sort(function (a, b) {
                return b.replace('-', '').length - a.replace('-', '').length;
            });
            return candidates[0];
        }

        function runPlateOcr(fileOrBlob) {
            var $status = $('#plate-ocr-status');
            var $regNumber = $('#vehicle-registration-number');

            // Never overwrite something staff already typed/confirmed.
            if ($regNumber.val()) {
                return;
            }

            $status.removeClass('d-none text-danger text-success').addClass('text-muted').text('Reading registration number from photo...');

            getTesseractWorker()
                .then(function (worker) {
                    return worker.recognize(fileOrBlob);
                })
                .then(function (result) {
                    var guess = extractPlateGuess(result?.data?.text);
                    if (guess && !$regNumber.val()) {
                        $regNumber.val(guess);
                        $status.removeClass('text-muted').addClass('text-success')
                            .text('Detected "' + guess + '" from photo — please verify.');
                    } else {
                        $status.removeClass('text-muted').addClass('text-danger')
                            .text('Could not read the plate clearly — please enter it manually.');
                    }
                })
                .catch(function () {
                    $status.removeClass('text-muted').addClass('text-danger')
                        .text('Plate scanning unavailable right now — please enter it manually.');
                });
        }

        $(document).on('change', '#vehicle-pic', function () {
            if (this.files && this.files[0]) {
                runPlateOcr(this.files[0]);
            }
        });

        // ------------------------------------------------------------------
        // In-page camera capture: lets staff take a photo directly (vehicle,
        // person, CNIC) instead of only picking an existing file. Falls back
        // silently to the plain file input (with its native `capture`
        // attribute) when getUserMedia isn't available — e.g. non-HTTPS
        // origins or browsers without camera support.
        // ------------------------------------------------------------------
        var cameraSupported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
        if (!cameraSupported) {
            $('.camera-capture-btn').addClass('d-none');
        } else {
            var cameraStream = null;
            var cameraTargetInputId = null;
            var cameraFacingMode = 'environment';

            function setLocalPreview(inputSelector, blobUrl) {
                var $input = $(inputSelector);
                var $existing = $input.prev('img.existing-pic-preview');
                if ($existing.length) {
                    $existing.attr('src', blobUrl);
                } else {
                    $input.before('<img src="' + blobUrl + '" class="existing-pic-preview" alt="Captured photo">');
                }
            }

            function stopCameraStream() {
                if (cameraStream) {
                    cameraStream.getTracks().forEach(function (track) { track.stop(); });
                    cameraStream = null;
                }
            }

            function resetCameraModalButtons() {
                $('#camera-video').removeClass('d-none');
                $('#camera-canvas').addClass('d-none');
                $('#camera-capture-shot-btn').removeClass('d-none');
                $('#camera-retake-btn, #camera-use-photo-btn').addClass('d-none');
            }

            function startCamera() {
                var video = document.getElementById('camera-video');
                $('#camera-error').addClass('d-none').text('');
                stopCameraStream();
                resetCameraModalButtons();
                navigator.mediaDevices.getUserMedia({ video: { facingMode: cameraFacingMode }, audio: false })
                    .then(function (stream) {
                        cameraStream = stream;
                        video.srcObject = stream;
                    })
                    .catch(function (err) {
                        $('#camera-error').removeClass('d-none').text('Could not access camera: ' + err.message);
                    });
            }

            $(document).on('click', '.camera-capture-btn', function () {
                cameraTargetInputId = $(this).data('target-input');
                cameraFacingMode = 'environment';
                bootstrap.Modal.getOrCreateInstance(document.getElementById('camera-capture-modal')).show();
                startCamera();
            });

            $('#camera-capture-modal').on('hidden.bs.modal', function () {
                stopCameraStream();
            });

            $(document).on('click', '#camera-switch-btn', function () {
                cameraFacingMode = cameraFacingMode === 'environment' ? 'user' : 'environment';
                startCamera();
            });

            $(document).on('click', '#camera-capture-shot-btn', function () {
                var video = document.getElementById('camera-video');
                var canvas = document.getElementById('camera-canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                $('#camera-video').addClass('d-none');
                $('#camera-canvas').removeClass('d-none');
                $('#camera-capture-shot-btn').addClass('d-none');
                $('#camera-retake-btn, #camera-use-photo-btn').removeClass('d-none');
            });

            $(document).on('click', '#camera-retake-btn', function () {
                startCamera();
            });

            $(document).on('click', '#camera-use-photo-btn', function () {
                var canvas = document.getElementById('camera-canvas');
                canvas.toBlob(function (blob) {
                    if (!blob || !cameraTargetInputId) {
                        return;
                    }
                    var file = new File([blob], 'photo-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);

                    var input = document.getElementById(cameraTargetInputId);
                    input.files = dataTransfer.files;

                    setLocalPreview('#' + cameraTargetInputId, URL.createObjectURL(blob));

                    if (cameraTargetInputId === 'vehicle-pic') {
                        runPlateOcr(blob);
                    }

                    stopCameraStream();
                    bootstrap.Modal.getOrCreateInstance(document.getElementById('camera-capture-modal')).hide();
                }, 'image/jpeg', 0.9);
            });
        }

        function clamp(value, min, max) {
            return Math.min(Math.max(value, min), max);
        }

        function computeCharges() {
            var serviceTypeId = $('#service_type').val();
            var categoryId = $('#vehicle-category').val();
            var price = 0;
            if (serviceTypeId && categoryId && rateMatrix[serviceTypeId] && rateMatrix[serviceTypeId][categoryId] !== undefined) {
                price = rateMatrix[serviceTypeId][categoryId];
            }
            if ($('#diesel-checkbox').is(':checked')) {
                price += dieselPrice;
            }
            if ($('#polish-checkbox').is(':checked')) {
                price += polishPrice;
            }
            $('#charges').val(price.toFixed(2));
            computeDiscount();
        }

        // Only one of the two discount_value inputs should ever submit: the select
        // (fixed percentages) when type=percent, or the free-text amount otherwise.
        // Toggling `disabled` (not just visibility) keeps the inactive one out of
        // the FormData entirely, so there's never a duplicate `discount_value` key.
        function toggleDiscountInputs() {
            var type = $('#discount-type').val();
            if (type === 'percent') {
                $('#discount-value-percent').removeClass('d-none').prop('disabled', false);
                $('#discount-value-amount').addClass('d-none').prop('disabled', true);
            } else {
                $('#discount-value-amount').removeClass('d-none').prop('disabled', false);
                $('#discount-value-percent').addClass('d-none').prop('disabled', true);
            }
        }

        function computeDiscount() {
            var charges = parseFloat($('#charges').val()) || 0;
            var type = $('#discount-type').val();
            var value = type === 'percent'
                ? (parseFloat($('#discount-value-percent').val()) || 0)
                : (parseFloat($('#discount-value-amount').val()) || 0);
            var discount = type === 'percent' ? (charges * value / 100) : value;
            discount = clamp(discount, 0, charges);
            $('#discount-amount').val(discount.toFixed(2));
            $('#total-bill').val((charges - discount).toFixed(2));
        }

        toggleDiscountInputs();
        computeCharges();

        function getForm() {
            return $('#service-form');
        }

        function clearFieldErrors() {
            getForm().find('.is-invalid').removeClass('is-invalid');
            $('#form-alert-container').empty();
        }

        function showFormErrors(errors, fallbackMessage) {
            var messages = [];
            if (errors) {
                $.each(errors, function(field, fieldMessages) {
                    messages.push(fieldMessages[0]);
                    getForm().find('[name="' + field + '"]').addClass('is-invalid');
                });
            }
            if (!messages.length) {
                messages.push(fallbackMessage || 'Something went wrong. Please try again.');
            }
            var html = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><ul class="mb-0">';
            messages.forEach(function(m) { html += '<li>' + m + '</li>'; });
            html += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
            $('#form-alert-container').html(html);
            $('html, body').animate({ scrollTop: 0 }, 200);
        }

        function showFormSuccess(message) {
            $('#form-alert-container').html(
                '<div class="alert alert-success alert-dismissible fade show" role="alert">' + message +
                '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>'
            );
            $('html, body').animate({ scrollTop: 0 }, 200);
        }

        $(document).on('submit', '#service-form', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            if ($submitBtn.prop('disabled')) {
                return;
            }
            var originalBtnText = $submitBtn.text();

            clearFieldErrors();
            $submitBtn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: new FormData($form[0]),
                processData: false,
                contentType: false,
                headers: { 'Accept': 'application/json' },
                success: function(response) {
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                    showFormSuccess(response.message || 'Saved successfully');

                    if ($form.data('mode') === 'create') {
                        $form[0].reset();
                        $('.hidden-input').attr('readonly', true);
                        $('#vehicle-brand-id, #vehicle-id, #customer-address-id').val('');
                        $('#current-user-id').val('');
                        $('#allow-duplicate-phone-wrapper').addClass('d-none');
                        $('#phone-duplicate-warning').addClass('d-none');
                        $('.existing-pic-preview').remove();
                        toggleDiscountInputs();
                        computeCharges();
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text(originalBtnText);
                    var response = xhr.responseJSON;
                    showFormErrors(response && response.errors, response && response.message);
                }
            });
        });

        $(document).on('change', '#service_type, #vehicle-category, #diesel-checkbox, #polish-checkbox', computeCharges);
        $(document).on('change', '#discount-type', function() {
            toggleDiscountInputs();
            computeDiscount();
        });
        $(document).on('change', '#discount-value-percent', computeDiscount);
        $(document).on('input change', '#discount-value-amount', computeDiscount);

        function getSubmitButton() {
            return $('#customer-phone').closest('form').find('button[type="submit"]');
        }

        function checkPhoneDuplicate() {
            var phone = $('#customer-phone').val().replace(/\D/g, '').slice(0, 11);
            $('#customer-phone').val(phone);

            if (phone.length !== 11) {
                $('#allow-duplicate-phone-wrapper').addClass('d-none');
                $('#phone-duplicate-warning').addClass('d-none');
                getSubmitButton().prop('disabled', false);
                return;
            }

            $('#phone-check-loader').removeClass('d-none');
            $.ajax({
                url: "{{ route('admin.services.check_phone') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone,
                    exclude_user_id: $('#current-user-id').val()
                },
                success: function(response) {
                    $('#phone-check-loader').addClass('d-none');
                    if (response.exists) {
                        $('#allow-duplicate-phone-wrapper').removeClass('d-none');
                        $('#allow-duplicate-phone').prop('checked', false);
                        $('#phone-duplicate-warning').removeClass('d-none')
                            .text('This phone number is already registered to "' + response.name + '". Check "Allow anyway" to proceed.');
                        getSubmitButton().prop('disabled', true);
                    } else {
                        $('#allow-duplicate-phone-wrapper').addClass('d-none');
                        $('#phone-duplicate-warning').addClass('d-none');
                        $('#allow-duplicate-phone').prop('checked', false);
                        getSubmitButton().prop('disabled', false);
                    }
                },
                error: function() {
                    $('#phone-check-loader').addClass('d-none');
                }
            });
        }

        $(document).on('keyup', '#customer-phone', checkPhoneDuplicate);
        $(document).on('change', '#allow-duplicate-phone', function() {
            getSubmitButton().prop('disabled', $('#phone-duplicate-warning').is(':visible') && !$(this).is(':checked'));
        });

        $(document).on('click', '#search-vehicle', function () {
            var vehicleRegistrationNumber = $('#vehicle-registration-number').val();

            if (vehicleRegistrationNumber != '') {
                $('.search-icon').addClass('d-none');
                $('.loader-icon').removeClass('d-none');

                $('#search-vehicle').removeClass('bg-primary');
                $('#search-vehicle').addClass('bg-warning');
                // Example: simulate AJAX request
                $.ajax({
                    url: "{{route('search_vehicle_by_number')}}", // your search endpoint
                    method: 'POST',
                    data: {
                        _token: "{{csrf_token()}}",
                        registration_number: $('#vehicle-registration-number').val()
                    },
                    success: function (response) {
                        if ((response) && (response.status == true)) {
                            $('#vehicle-brand-name').val(response.data.vehicle.brand.name);
                            $('#vehicle-name').val(response.data.vehicle.name);
                            if (response.pending_amount > 0) {
                                $('.pending-amount').removeClass('d-none');
                                $('.pending-amount').html('Pending Amount: '+response.pending_amount);
                            } else {
                                $('.pending-amount').addClass('d-none');
                                $('.pending-amount').html('Pending Amount: 0');
                            }

                            $('#vehicle-brand-id').val(response.data.vehicle.brand.id);
                            $('#vehicle-id').val(response.data.vehicle.id);
                            if (response.data.vehicle.vehicle_category_id) {
                                $('#vehicle-category').val(response.data.vehicle.vehicle_category_id);
                            }
                            computeCharges();

                            $('#customer-address').val(response.data.user.user_address ? response.data.user.user_address.name : (response.data.user.address || ''));
                            $('#customer-address-id').val(response.data.user.user_address ? response.data.user.user_address.id : '');

                            $('#customer-name').val(response.data.user.name);
                            $('#customer-phone').val(response.data.user.phone);
                            $('#current-user-id').val(response.data.user.id);
                            checkPhoneDuplicate();

                            $('#color').val(response.data.color || '');
                            $('#model-year').val(response.data.model_year || '');
                            $('#cnic-number').val(response.data.cnic_number || '');
                            updatePicFromSearch('#vehicle-pic', response.data.vehicle_pic);
                            updatePicFromSearch('#user-pic', response.data.user_pic);
                            updatePicFromSearch('#cnic-pic', response.data.cnic_pic);
                        }
                        $('.hidden-input').attr('readonly', false);
                        if ((response.status == false) && (response.msg == 'already_serviced')) {
                            // Just a heads-up (e.g. a legitimate second visit same day) —
                            // do NOT disable fields here: disabled inputs are dropped entirely
                            // from the FormData submission, which previously made a real
                            // resubmission fail with several unrelated "field is required"
                            // errors once this warning had fired.
                            alert(response.message);
                        }
                        $('.search-icon').removeClass('d-none');
                        $('.loader-icon').addClass('d-none');

                        $('#search-vehicle').addClass('bg-primary');
                        $('#search-vehicle').removeClass('bg-warning');
                    }
                    // error: function () {
                    //     // On error, you might also want to show it
                    //     $('.customer-info').removeClass('d-none');
                    // }
                });
            } else {
                $('.pending-amount').removeClass('d-none');
                $('.pending-amount').html('Pending Amount: 0');
            }
        });
        $(document).on("keyup", "#vehicle-registration-number", function() {
            $(this).val($(this).val().replace(/ /g, "-"));
        });
        $(document).on("blur", "#vehicle-registration-number", function() {
            if ($(this).val() != '') {
                $('#search-vehicle').trigger('click');
            }
        });

        $(document).on('keyup', '#vehicle-brand-name', function() {
            let query = $(this).val();

            if (query.length >= 3) { // start searching after 3 chars
                $.ajax({
                    url: "{{ route('search_vehicle_brand_by_name') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        q: query
                    },
                    success: function(response) {
                        let suggestions = $('#vehicle-brand-suggestions');
                        suggestions.empty();

                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                suggestions.append(`
                                    <li class="list-group-item brand-suggestion-item"
                                        style="cursor:pointer;"
                                        data-vehicle-brand-id="${item.id}">
                                        ${item.name}
                                    </li>
                                `);
                            });
                            suggestions.show();
                        } else {
                            suggestions.hide();
                        }
                    },
                    error: function(xhr) {
                        console.error("API error:", xhr.responseText);
                    }
                });
            } else {
                $('#vehicle-brand-suggestions').hide();
            }
        });
        // On click: fill input with selected name
        $(document).on('click', '.brand-suggestion-item', function() {
            $('#vehicle-brand-name').val($(this).text().trim());
            $('#vehicle-brand-id').val($(this).attr('data-vehicle-brand-id').trim());
            $('#vehicle-brand-suggestions').hide();

            $('#vehicle-name').val();
            $('#vehicle-id').val();
        });
        // Hide suggestions when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#vehicle-brand-name, #vehicle-brand-suggestions').length) {
                $('#vehicle-brand-suggestions').hide();
            }
        });

        // Address starts

        $(document).on('keyup', '#customer-address', function() {
            let query = $(this).val();
            var customerAddress = $('#customer-address-id').val();
            if (query.length >= 2) { // start searching after 3 chars
                $.ajax({
                    url: "{{ route('search_customer_address_by_name') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        q: query
                    },
                    success: function(response) {
                        let suggestions = $('#customer-address-suggestions');
                        suggestions.empty();

                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                suggestions.append(`
                                    <li class="list-group-item customer-address-suggestion-item"
                                        style="cursor:pointer;"
                                        data-customer-address-id="${item.id}">
                                        ${item.name}
                                    </li>
                                `);
                            });
                            suggestions.show();
                        } else {
                            suggestions.hide();
                        }
                    },
                    error: function(xhr) {
                        console.error("API error:", xhr.responseText);
                    }
                });
            } else {
                $('#customer-address-suggestions').hide();
            }
        });
        // On click: fill input with selected name
        $(document).on('click', '.customer-address-suggestion-item', function() {
            $('#customer-address').val($(this).text().trim());
            $('#customer-address-id').val($(this).attr('data-customer-address-id').trim());
            $('#customer-address-suggestions').hide();
        });
        // Hide suggestions when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#customer-address, #customer-address-suggestions').length) {
                $('#customer-address-suggestions').hide();
            }
        });

        // Address ends


        $(document).on('keyup', '#vehicle-name', function() {
            let query = $(this).val();
            var vehicleBrandId = $('#vehicle-brand-id').val();
            if (query.length >= 3) { // start searching after 3 chars
                $.ajax({
                    url: "{{ route('search_vehicle_by_name') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        q: query,
                        vehicle_brand_id: vehicleBrandId
                    },
                    success: function(response) {
                        let suggestions = $('#vehicle-suggestions');
                        suggestions.empty();

                        if (response.data.length > 0) {
                            response.data.forEach(function(item) {
                                suggestions.append(`
                                    <li class="list-group-item suggestion-item"
                                        style="cursor:pointer;"
                                        data-vehicle-id="${item.id}"
                                        data-vehicle-category-id="${item.vehicle_category_id || ''}">
                                        ${item.name}
                                    </li>
                                `);
                            });
                            suggestions.show();
                        } else {
                            suggestions.hide();
                        }
                    },
                    error: function(xhr) {
                        console.error("API error:", xhr.responseText);
                    }
                });
            } else {
                $('#vehicle-suggestions').hide();
            }
        });
        // On click: fill input with selected name
        $(document).on('click', '.suggestion-item', function() {
            $('#vehicle-name').val($(this).text().trim());
            $('#vehicle-id').val($(this).attr('data-vehicle-id').trim());
            var categoryId = $(this).attr('data-vehicle-category-id');
            if (categoryId) {
                $('#vehicle-category').val(categoryId);
                computeCharges();
            }
            $('#vehicle-suggestions').hide();
        });
        // Hide suggestions when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('#vehicle-name, #vehicle-suggestions').length) {
                $('#vehicle-suggestions').hide();
            }
        });

        // Run an initial duplicate check on edit (pre-filled phone), excluding the current owner.
        if ($('#customer-phone').val()) {
            checkPhoneDuplicate();
        }
   });
</script>
@endsection
