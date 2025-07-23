<div class="row">
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="sno">SNo</label>
        <div class="input-group input-group-merge">
            <input type="text" name="sno" value="{{ isset($record) ? $record->sno : '' }}" class="form-control" id="sno" placeholder="Enter SNo">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="user_id">User ID</label>
        <select name="user_id" class="form-control" id="user_id">
            <option value="">Select User</option>
            @if (!empty($users))
                @foreach($users as $user)
                    <option value="{{$user->id}}" {{ isset($record) && $record->user_id == $user->id?'selected' : '' }}>{{$user->name}}</option>
                @endforeach
            @endif
        </select>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="received_date">Received Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="received_date" value="{{ isset($record) ? $record->received_date : '' }}" class="form-control" id="received_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_account">Guarantor Account</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_account" value="{{ isset($record) ? $record->guarantor_account : '' }}" class="form-control" id="guarantor_account" placeholder="Enter Guarantor Account">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_name">Guarantor Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_name" value="{{ isset($record) ? $record->guarantor_name : '' }}" class="form-control" id="guarantor_name" placeholder="Enter Guarantor Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_address_line1">Guarantor Address Line 1</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_address_line1" value="{{ isset($record) ? $record->guarantor_address_line1 : '' }}" class="form-control" id="guarantor_address_line1" placeholder="Enter Address Line 1">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_address_line2">Guarantor Address Line 2</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_address_line2" value="{{ isset($record) ? $record->guarantor_address_line2 : '' }}" class="form-control" id="guarantor_address_line2" placeholder="Enter Address Line 2">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_address_line3">Guarantor Address Line 3</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_address_line3" value="{{ isset($record) ? $record->guarantor_address_line3 : '' }}" class="form-control" id="guarantor_address_line3" placeholder="Enter Address Line 3">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_city">Guarantor City</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_city" value="{{ isset($record) ? $record->guarantor_city : '' }}" class="form-control" id="guarantor_city" placeholder="Enter City">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_state">Guarantor State</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_state" value="{{ isset($record) ? $record->guarantor_state : '' }}" class="form-control" id="guarantor_state" placeholder="Enter State">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="guarantor_zip">Guarantor Zip</label>
        <div class="input-group input-group-merge">
            <input type="text" name="guarantor_zip" value="{{ isset($record) ? $record->guarantor_zip : '' }}" class="form-control" id="guarantor_zip" placeholder="Enter Zip">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="mrn">MRN</label>
        <div class="input-group input-group-merge">
            <input type="text" name="mrn" value="{{ isset($record) ? $record->mrn : '' }}" class="form-control" id="mrn" placeholder="Enter MRN">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="patient_name">Patient Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="patient_name" value="{{ isset($record) ? $record->patient_name : '' }}" class="form-control" id="patient_name" placeholder="Enter Patient Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="date_of_birth">Date of Birth</label>
        <div class="input-group input-group-merge">
            <input type="date" name="date_of_birth" value="{{ isset($record) ? $record->date_of_birth : '' }}" class="form-control" id="date_of_birth">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="department">Department</label>
        <div class="input-group input-group-merge">
            <input type="text" name="department" value="{{ isset($record) ? $record->department : '' }}" class="form-control" id="department" placeholder="Enter Department">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="pos">POS</label>
        <div class="input-group input-group-merge">
            <input type="text" name="pos" value="{{ isset($record) ? $record->pos : '' }}" class="form-control" id="pos" placeholder="Enter POS">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="pos_type">POS Type</label>
        <div class="input-group input-group-merge">
            <input type="text" name="pos_type" value="{{ isset($record) ? $record->pos_type : '' }}" class="form-control" id="pos_type" placeholder="Enter POS Type">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="service_provider">Service Provider</label>
        <div class="input-group input-group-merge">
            <input type="text" name="service_provider" value="{{ isset($record) ? $record->service_provider : '' }}" class="form-control" id="service_provider" placeholder="Enter Service Provider">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="provider_npi">Provider NPI</label>
        <div class="input-group input-group-merge">
            <input type="text" name="provider_npi" value="{{ isset($record) ? $record->provider_npi : '' }}" class="form-control" id="provider_npi" placeholder="Enter Provider NPI">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="date_of_service">Date of Service</label>
        <div class="input-group input-group-merge">
            <input type="date" name="date_of_service" value="{{ isset($record) ? $record->date_of_service : '' }}" class="form-control" id="date_of_service">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="diagnosis_codes">Diagnosis Codes</label>
        <div class="input-group input-group-merge">
            <input type="text" name="diagnosis_codes" value="{{ isset($record) ? $record->diagnosis_codes : '' }}" class="form-control" id="diagnosis_codes" placeholder="Enter Diagnosis Codes">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="procedure_code">Procedure Code</label>
        <div class="input-group input-group-merge">
            <input type="text" name="procedure_code" value="{{ isset($record) ? $record->procedure_code : '' }}" class="form-control" id="procedure_code" placeholder="Enter Procedure Code">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="procedure_name">Procedure Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="procedure_name" value="{{ isset($record) ? $record->procedure_name : '' }}" class="form-control" id="procedure_name" placeholder="Enter Procedure Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="rvu">RVU</label>
        <div class="input-group input-group-merge">
            <input type="text" name="rvu" value="{{ isset($record) ? $record->rvu : '' }}" class="form-control" id="rvu" placeholder="Enter RVU">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="charge_amount">Charge Amount</label>
        <div class="input-group input-group-merge">
            <input type="text" name="charge_amount" value="{{ isset($record) ? $record->charge_amount : '' }}" class="form-control" id="charge_amount" placeholder="Enter Charge Amount">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="procedure_qty">Procedure Quantity</label>
        <div class="input-group input-group-merge">
            <input type="text" name="procedure_qty" value="{{ isset($record) ? $record->procedure_qty : '' }}" class="form-control" id="procedure_qty" placeholder="Enter Procedure Quantity">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="modifiers">Modifiers</label>
        <div class="input-group input-group-merge">
            <input type="text" name="modifiers" value="{{ isset($record) ? $record->modifiers : '' }}" class="form-control" id="modifiers" placeholder="Enter Modifiers">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="payor_name">Payor Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="payor_name" value="{{ isset($record) ? $record->payor_name : '' }}" class="form-control" id="payor_name" placeholder="Enter Payer Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="plan_name">Plan Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="plan_name" value="{{ isset($record) ? $record->plan_name : '' }}" class="form-control" id="plan_name" placeholder="Enter Plan Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="subscriber_number">Subscriber Number</label>
        <div class="input-group input-group-merge">
            <input type="text" name="subscriber_number" value="{{ isset($record) ? $record->subscriber_number : '' }}" class="form-control" id="subscriber_number" placeholder="Enter Subscriber Number">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="subscriber_name">Subscriber Name</label>
        <div class="input-group input-group-merge">
            <input type="text" name="subscriber_name" value="{{ isset($record) ? $record->subscriber_name : '' }}" class="form-control" id="subscriber_name" placeholder="Enter Subscriber Name">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="subscriber_dob">Subscriber DOB</label>
        <div class="input-group input-group-merge">
            <input type="date" name="subscriber_dob" value="{{ isset($record) ? $record->subscriber_dob : '' }}" class="form-control" id="subscriber_dob">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="subscriber_ssn">Subscriber SSN</label>
        <div class="input-group input-group-merge">
            <input type="text" name="subscriber_ssn" value="{{ isset($record) ? $record->subscriber_ssn : '' }}" class="form-control" id="subscriber_ssn" placeholder="Enter Subscriber SSN">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="group_number">Group Number</label>
        <div class="input-group input-group-merge">
            <input type="text" name="group_number" value="{{ isset($record) ? $record->group_number : '' }}" class="form-control" id="group_number" placeholder="Enter Group Number">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_address">Coverage Address</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_address" value="{{ isset($record) ? $record->coverage_address : '' }}" class="form-control" id="coverage_address" placeholder="Enter Coverage Address">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_city">Coverage City</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_city" value="{{ isset($record) ? $record->coverage_city : '' }}" class="form-control" id="coverage_city" placeholder="Enter Coverage City">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_state">Coverage State</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_state" value="{{ isset($record) ? $record->coverage_state : '' }}" class="form-control" id="coverage_state" placeholder="Enter Coverage State">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_zip">Coverage Zip</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_zip" value="{{ isset($record) ? $record->coverage_zip : '' }}" class="form-control" id="coverage_zip" placeholder="Enter Coverage Zip">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_phone1">Coverage Phone 1</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_phone1" value="{{ isset($record) ? $record->coverage_phone1 : '' }}" class="form-control" id="coverage_phone1" placeholder="Enter Coverage Phone 1">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="coverage_phone2">Coverage Phone 2</label>
        <div class="input-group input-group-merge">
            <input type="text" name="coverage_phone2" value="{{ isset($record) ? $record->coverage_phone2 : '' }}" class="form-control" id="coverage_phone2" placeholder="Enter Coverage Phone 2">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="submission_date">Submission Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="submission_date" value="{{ isset($record) ? $record->submission_date : '' }}" class="form-control" id="submission_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="claim_status">Claim Status</label>
        <select name="" class="form-control" id="">
            <option value="">Select Claim Status</option>
            <option value="Paid" {{isset($record) ? ($record->claim_status == 'Paid' ? 'selected' : '') : ''}}>Paid</option>
            <option value="Rebilled" {{isset($record) ? ($record->claim_status == 'Rebilled' ? 'selected' : '') : ''}}>Rebilled</option>
            <option value="In Process" {{isset($record) ? ($record->claim_status == 'In Process' ? 'selected' : '') : ''}}>In Process</option>
            <option value="Info Required" {{isset($record) ? ($record->claim_status == 'Info Required' ? 'selected' : '') : ''}}>Info Required</option>
            <option value="Patient Responsibility" {{isset($record) ? ($record->claim_status == 'Patient Responsibility' ? 'selected' : '') : ''}}>Patient Responsibility</option>
            <option value="Partially Paid" {{isset($record) ? ($record->claim_status == 'Partially Paid' ? 'selected' : '') : ''}}>Partially Paid</option>
            <option value="Dropped" {{isset($record) ? ($record->claim_status == 'Dropped' ? 'selected' : '') : ''}}>Dropped</option>
            <option value="Duplicate" {{isset($record) ? ($record->claim_status == 'Duplicate' ? 'selected' : '') : ''}}>Duplicate</option>
            <option value="Denied" {{isset($record) ? ($record->claim_status == 'Denied' ? 'selected' : '') : ''}}>Denied</option>
        </select>
        <!-- <div class="input-group input-group-merge">
            <input type="text" name="claim_status" value="{{ isset($record) ? $record->claim_status : '' }}" class="form-control" id="claim_status" placeholder="Enter Claim Status">
        </div> -->
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_received_date">Primary Ins. Received Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="primary_received_date" value="{{ isset($record) ? $record->primary_received_date : '' }}" class="form-control" id="primary_received_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_amount">Primary Amount</label>
        <div class="input-group input-group-merge">
            <input type="text" name="primary_amount" value="{{ isset($record) ? $record->primary_amount : '' }}" class="form-control" id="primary_amount" placeholder="Enter Primary Amount">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_payment_no">Primary Payment No</label>
        <div class="input-group input-group-merge">
            <input type="text" name="primary_payment_no" value="{{ isset($record) ? $record->primary_payment_no : '' }}" class="form-control" id="primary_payment_no" placeholder="Enter Primary Payment No">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_ins_payment_date">Primary Ins. Payment Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="primary_ins_payment_date" value="{{ isset($record) ? $record->primary_ins_payment_date : '' }}" class="form-control" id="primary_ins_payment_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_ins_payment_cleared">Primary Ins. Payment Cleared</label>
        <div class="input-group input-group-merge">
            <input type="date" name="primary_ins_payment_cleared" value="{{ isset($record) ? $record->primary_ins_payment_cleared : '' }}" class="form-control" id="primary_ins_payment_cleared">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="primary_payment_type">Primary Payment Type</label>
        <div class="input-group input-group-merge">
            <input type="text" name="primary_payment_type" value="{{ isset($record) ? $record->primary_payment_type : '' }}" class="form-control" id="primary_payment_type" placeholder="Enter Primary Payment Type">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="ar_days">AR Days</label>
        <div class="input-group input-group-merge">
            <input type="text" name="ar_days" value="{{ isset($record) ? $record->ar_days : '' }}" class="form-control" id="ar_days" placeholder="Enter AR Days">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_payer_status">Secondary Payer Status</label>
        <div class="input-group input-group-merge">
            <input type="text" name="secondary_payer_status" value="{{ isset($record) ? $record->secondary_payer_status : '' }}" class="form-control" id="secondary_payer_status" placeholder="Enter Secondary Payer Status">
        </div>
    </div>
    <!-- <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_ins_received_date">Secondary Ins. Received Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="secondary_ins_received_date" value="{{ isset($record) ? $record->secondary_ins_received_date : '' }}" class="form-control" id="secondary_ins_received_date">
        </div>
    </div> -->
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_amount">Secondary Amount</label>
        <div class="input-group input-group-merge">
            <input type="text" name="secondary_amount" value="{{ isset($record) ? $record->secondary_amount : '' }}" class="form-control" id="secondary_amount" placeholder="Enter Secondary Amount">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_payment_no">Secondary Payment No</label>
        <div class="input-group input-group-merge">
            <input type="text" name="secondary_payment_no" value="{{ isset($record) ? $record->secondary_payment_no : '' }}" class="form-control" id="secondary_payment_no" placeholder="Enter Secondary Payment No">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_payment_date">Secondary Payment Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="secondary_payment_date" value="{{ isset($record) ? $record->secondary_payment_date : '' }}" class="form-control" id="secondary_payment_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_payment_cleared">Secondary Payment Cleared</label>
        <div class="input-group input-group-merge">
            <input type="date" name="secondary_payment_cleared" value="{{ isset($record) ? $record->secondary_payment_cleared : '' }}" class="form-control" id="secondary_payment_cleared">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="secondary_payment_type">Secondary Payment Type</label>
        <div class="input-group input-group-merge">
            <input type="text" name="secondary_payment_type" value="{{ isset($record) ? $record->secondary_payment_type : '' }}" class="form-control" id="secondary_payment_type" placeholder="Enter Secondary Payment Type">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="selfpay_status">Self-Pay Status</label>
        <div class="input-group input-group-merge">
            <input type="text" name="selfpay_status" value="{{ isset($record) ? $record->selfpay_status : '' }}" class="form-control" id="selfpay_status" placeholder="Enter Self-Pay Status">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="selfpay_amount">Self-Pay Amount</label>
        <div class="input-group input-group-merge">
            <input type="text" name="selfpay_amount" value="{{ isset($record) ? $record->selfpay_amount : '' }}" class="form-control" id="selfpay_amount" placeholder="Enter Self-Pay Amount">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="selfpay_payment_no">Self-Pay Payment No</label>
        <div class="input-group input-group-merge">
            <input type="text" name="selfpay_payment_no" value="{{ isset($record) ? $record->selfpay_payment_no : '' }}" class="form-control" id="selfpay_payment_no" placeholder="Enter Self-Pay Payment No">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="selfpay_payment_date">Self-Pay Payment Date</label>
        <div class="input-group input-group-merge">
            <input type="date" name="selfpay_payment_date" value="{{ isset($record) ? $record->selfpay_payment_date : '' }}" class="form-control" id="selfpay_payment_date">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="selfpay_payment_type">Self-Pay Payment Type</label>
        <div class="input-group input-group-merge">
            <input type="text" name="selfpay_payment_type" value="{{ isset($record) ? $record->selfpay_payment_type : '' }}" class="form-control" id="selfpay_payment_type" placeholder="Enter Self-Pay Payment Type">
        </div>
    </div>
    <div class="mb-3 col-lg-3 col-md-6 col-12">
        <label class="form-label" for="claim_comments">Claim Comments</label>
        <div class="input-group input-group-merge">
            <input type="text" name="claim_comments" value="{{ isset($record) ? $record->claim_comments : '' }}" class="form-control" id="claim_comments" placeholder="Enter Claim Comments">
        </div>
    </div>
</div>