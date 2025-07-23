@isset($record)
<div class="mb-3">
    <label class="form-label" for="basic-icon-default-fullname">Provider NPI</label>
    <div class="">
        @if(isset($record) && !empty($record->provider_npi))<span class="fw-bold text-white bg-dark rounded p-1">{{$record->provider_npi}}</span>@else NIL @endif
    </div>
</div>
@endisset
<div class="mb-3">
    <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
    <div class="input-group input-group-merge">
        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-user"></i></span>
        <input type="text" name="name" value="{{isset($record) ? $record->name : ''}}" class="form-control" id="basic-icon-default-fullname" placeholder="Enter name" aria-label="John Doe" aria-describedby="basic-icon-default-fullname2">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="basic-icon-default-email">Email</label>
    <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="bx bx-envelope"></i></span>
        <input type="text" name="email" value="{{isset($record) ? $record->email : ''}}" id="basic-icon-default-email" class="form-control" placeholder="Enter email" aria-label="john.doe" aria-describedby="basic-icon-default-email2">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="role_id">Roles</label>
    <div class="input-group input-group-merge">
        <select name="role_id" id="role_id" class="form-control">
            <option value="">Select Role</option>
            @if(!empty($roles))
            @foreach($roles as $role)
            <option value="{{$role->id}}" {{isset($record) ? (($record->user_type == $role->id) ? 'selected' : '') : ''}}>{{$role->name}}</option>
            @endforeach
            @endif
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="input-group input-group-merge">
        <span id="password2" class="input-group-text"><i class="bx bx-hide"></i></span>
        <input type="password" name="password" id="password" class="form-control phone-mask" placeholder="Enter password" aria-label="658 799 8941" aria-describedby="password2">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="group_id">Select Groups</label>
    <div class="input-group input-group-merge">
        @php $userGroupIds = isset($record) ? $record->groups->pluck('group_id')->toArray() : []; @endphp
        <select name="group_id[]" id="group_id" class="form-control select2 w-100" multiple style="width: 100%;">
            @if(!empty($groups))
            @foreach($groups as $group)
            <option value="{{ $group->id }}" @if(in_array($group->id, $userGroupIds)) selected @endif>
                {{ $group->name }}
            </option>
            @endforeach
            @endif
        </select>
    </div>
</div>
<div class="mb-3">
@php
    // Determine if manual input is used and set values accordingly
    $isManualNPI = isset($record) && $record->manual_provider_npi;
    $providerNpi = isset($record) ? $record->provider_npi : '';
@endphp

<label class="form-label d-flex justify-content-between" for="provider_npi">
    <div>Select Provider NPI</div>
    <div>
    <input type="checkbox" name="manual_provider_npi" id="input-npi-number" {{ $isManualNPI ? 'checked' : '' }}>
    &nbsp;
    Manual NPI
    </div>
</label>
@isset($record)
<div class="input-group input-group-merge" id="select-input-provider-npi">
    <select name="provider_npi" id="provider_npi" class="form-control select2" {{ $isManualNPI ? 'disabled' : '' }} style="width: 100%;">
        <option value="">Select NPI</option>
        @if(!empty($billings))
        @foreach($billings as $billing)
        <option value="{{ $billing->provider_npi }}" {{ $billing->provider_npi == $providerNpi ? 'selected' : '' }}>
            {{ $billing->provider_npi }}
        </option>
        @endforeach
        @endif
    </select>
</div>

<div class="input-group input-group-merge" id="input-input-provider-npi">
    <input type="text" value="{{ $isManualNPI ? $providerNpi : '' }}" name="provider_npi" class="form-control" placeholder="Enter NPI" {{ $isManualNPI ? '' : 'disabled' }}>
</div>
@else
<div class="input-group input-group-merge" id="select-input-provider-npi">
    <select name="provider_npi" id="provider_npi" class="form-control select2">
        <option value="">Select NPI</option>
        @if(!empty($billings))
        @foreach($billings as $billing)
        <option value="{{ $billing->provider_npi }}" {{ $billing->provider_npi == $providerNpi ? 'selected' : '' }}>
            {{ $billing->provider_npi }}
        </option>
        @endforeach
        @endif
    </select>
</div>

<div class="input-group input-group-merge" id="input-input-provider-npi">
    <input type="text" value="" name="provider_npi" class="form-control" disabled placeholder="Enter NPI">
</div>
@endisset
</div>