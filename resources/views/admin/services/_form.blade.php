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
    </style>
@endsection
<div class="row">
    @php
        $diesel = 0;
        $polish = 0;
        $serviceTypeId = 0;
        $charges = 0;
        $discountAmount = 0;
        $discountReason = '';
        $collectedAmount = 0;
        $paymentModeId = 0;
        $vehicleName = '';
        $registrationNumber = '';
        if (isset($record)) {
            if (isset($record->vehicle)) {
                $vehicleName = $record->vehicle?->name;
                $registrationNumber = $record->vehicle?->registration_number;
                $diesel = $record->diesel;
                $polish = $record->polish;
                $serviceTypeId = $record->service_type_id;
                $charges = $record->charges;
                $discountAmount = $record->discount;
                $discountReason = $record->discount_reason;
                $collectedAmount = $record->collected_amount;
                $paymentModeId = $record->payment_mode_id;
                $paymentStatus = $record->payment_status;
            }
        }
    @endphp
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="vehicle-registration-number">Vehicle Registration Number</label>
        <div class="input-group input-group-merge">
            <!-- Left Icon -->
            <span id="vehicle-registration-number2" class="input-group-text">
                <i class="bx bx-file"></i>
            </span>

            <!-- Input Field -->
            <input type="text" name="vehicle_registration_number" 
                value="{{ isset($record) ? $registrationNumber : '' }}" 
                class="form-control" 
                id="vehicle-registration-number" 
                placeholder="Enter Vehicle Registration Number" 
                aria-label="va-123" 
                aria-describedby="vehicle-registration-number2">

            <!-- Right Search Icon -->
            <a href="#" id="search-vehicle" class="input-group-text bg-primary">
                <i class="bx bx-search text-white search-button search-icon"></i>
                <i class="bx bx-loader text-dark search-button loader-icon d-none"></i>
            </a>
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12 position-relative">
        <label class="form-label" for="brandInput">Enter brand name</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-car"></i></span>
            <input type="text" id="brandInput" class="form-control" placeholder="Enter brand">
        </div>

        <!-- Suggestions Dropdown -->
        <div id="brandList" 
            class="list-group position-absolute w-100 shadow-sm" 
            style="z-index: 1050; max-height: 200px; overflow-y: auto;">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
            <label for="">Enter brand name</label>
            
        <!-- <div class="input-group input-group-merge">
            <span id="vehicle-name2" class="input-group-text"><i class="bx bx-car"></i></span>
            <input type="text" name="vehicle_name" value="{{isset($record) ? $vehicleName : ''}}" class="form-control" id="vehicle-name" placeholder="Enter Vehicle Name" aria-label="John Doe" aria-describedby="vehicle-name2">
        </div> -->
    </div>
    
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="service_type">
            <div>
                Select Service Type
            </div>
            <div>
                <input type="checkbox" name="diesel" class="mt-1" {{ (isset($record) && $diesel == 1) ? 'checked' : '' }}> Diesel
            </div>
            <div>
                <input type="checkbox" name="polish" class="mt-1" {{ (isset($record) && $polish == 1) ? 'checked' : '' }}> Polish
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="service_type" id="service_type" class="form-control">
                <option value="" selected disabled>Select Service Type</option>
                @foreach (\App\Models\ServiceType::get() as $serviceType)
                    <option value="{{$serviceType->id}}" {{ (isset($record) && $serviceTypeId == $serviceType->id) ? 'selected' : '' }}>{{$serviceType->name}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="charges">
            <div>
                Charges
            </div>
        </label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="number" name="charges" value="{{isset($record) ? $charges : ''}}" id="charges" class="form-control" placeholder="Enter Service Charges" aria-label="1000" aria-describedby="charges2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="discount">Discount</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="number" name="discount" value="{{isset($record) ? $discountAmount : ''}}" id="discount" class="form-control" placeholder="Enter Discount Amount" aria-label="100" aria-describedby="discount2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="discount-reason">Description <small>(Discount Reason / Complain)</small></label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-file"></i></span>
            <input type="text" name="discount_reason" value="{{isset($record) ? $discountReason : ''}}" id="discount-reason" class="form-control" placeholder="Discount Reason or Complain" aria-label="Friend" aria-describedby="discount-reason2">
        </div>
    </div>
    <!-- <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="charges">
            <div>
                Amount Collected
            </div>
        </label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="number" name="collected_amount" value="{{isset($record) ? $collectedAmount : ''}}" id="collected-amount" class="form-control" placeholder="Enter Amount Collected/Transferred" aria-label="1000" aria-describedby="collected-amount2">
        </div>
    </div> -->
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="payment-type">
            <div>
                Payment Mode
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="payment_mode_id" id="payment_mode_id" class="form-control">
                <option value="" selected disabled>Payment Mode</option>
                @foreach (\App\Models\PaymentMode::get() as $paymentMode)
                    <option value="{{$paymentMode->id}}" {{ (isset($record) && $paymentModeId == $paymentMode->id) ? 'selected' : '' }}>{{strtoupper(str_replace('_', ' ', $paymentMode->name))}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-12 customer-info">
        <div class="row">
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="customer-name">Customer Name</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-user"></i></span>
                    <input type="text" name="customer_name" value="{{isset($record) ? $record->vehicle?->user?->name : ''}}" id="customer-name" class="form-control" placeholder="Enter Customer Name" aria-label="john.doe" aria-describedby="customer-name2">
                </div>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="customer-phone">Customer Phone</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-phone"></i></span>
                    <input type="text" name="customer_phone" value="{{isset($record) ? $record->vehicle?->user?->phone : ''}}" id="customer-phone" class="form-control" placeholder="Enter Customer Phone" aria-label="+923001234567" aria-describedby="customer-phone2">
                </div>
            </div>
            <div class="mb-3 col-md-6 col-12">
                <label class="form-label" for="customer-address">Customer Address</label>
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-trip"></i></span>
                    <input type="text" name="customer_address" value="{{isset($record) ? $record->vehicle?->user?->address : ''}}" id="customer-address" class="form-control" placeholder="Enter Customer Address" aria-label="ghouri town" aria-describedby="customer-address2">
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="mb-3 col-md-6 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="date">
            <div>
                Date
            </div>
        </label>
        <div class="input-group">
            <input type="date" name="date" id="date" class="form-control">
        </div>
    </div> -->
</div>

@section('_scripts')
<script>
   $(document).ready(function() {
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
                            $('#vehicle-name').val(response.data.name);
                            $('#customer-name').val(response.data.user.name);
                            $('#customer-phone').val(response.data.user.phone);
                            $('#customer-address').val(response.data.user.address);
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
            }
        });
        $(document).on("keyup", "#vehicle-registration-number", function() {
            $(this).val($(this).val().replace(/ /g, "-"));
        });


        $(function(){
            let selectedBrand = null;

            // Brand Autocomplete
            $('#brandInput').on('keyup', function(){
                let query = $(this).val();
                if(query.length > 0){
                    $.getJSON("{{ route('brands') }}", function(brands){
                        $('#brandList').empty();
                        brands.filter(b => b.toLowerCase().includes(query.toLowerCase()))
                            .forEach(b => {
                                $('#brandList').append(
                                    `<a href="#" class="list-group-item list-group-item-action brand-item">${b}</a>`
                                );
                            });
                    });
                } else {
                    $('#brandList').empty();
                }
            });

            // Select Brand
            $(document).on('click', '.brand-item', function(e){
                e.preventDefault();
                selectedBrand = $(this).text();
                $('#brandInput').val(selectedBrand);
                $('#brandList').empty();

                // Enable model input
                $('#modelInput').prop('disabled', false).val('');
            });

            // Model Autocomplete (after brand selected)
            $('#modelInput').on('keyup', function(){
                let query = $(this).val();
                if(selectedBrand && query.length > 0){
                    $.getJSON("{{ route('models') }}", { brand: selectedBrand }, function(models){
                        $('#modelList').empty();
                        models.filter(m => m.toLowerCase().includes(query.toLowerCase()))
                            .forEach(m => {
                                $('#modelList').append(
                                    `<a href="#" class="list-group-item list-group-item-action model-item">${m}</a>`
                                );
                            });
                    });
                } else {
                    $('#modelList').empty();
                }
            });

            // Select Model
            $(document).on('click', '.model-item', function(e){
                e.preventDefault();
                $('#modelInput').val($(this).text());
                $('#modelList').empty();
            });
        });

   }); 
</script>
@endsection