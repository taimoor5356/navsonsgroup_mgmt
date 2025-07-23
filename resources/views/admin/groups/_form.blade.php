<div class="mb-3">
    <label class="form-label" for="basic-icon-default-fullname">Name</label>
    <div class="input-group input-group-merge">
        <span id="basic-icon-default-fullname2" class="input-group-text"><i class="bx bx-user"></i></span>
        <input type="text" value="{{isset($record) ? $record->name : ''}}" name="name" class="form-control" id="basic-icon-default-fullname" placeholder="Enter name" aria-label="name" aria-describedby="basic-icon-default-fullname2">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="basic-icon-default-address">Address</label>
    <div class="input-group input-group-merge">
        <span class="input-group-text"><i class="bx bx-map"></i></span>
        <input type="text" value="{{isset($record) ? $record->address : ''}}" name="address" id="basic-icon-default-address" class="form-control" placeholder="Enter address" aria-label="address" aria-describedby="basic-icon-default-address2">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="phone">Phone</label>
    <div class="input-group input-group-merge">
        <span id="phone2" class="input-group-text"><i class="bx bx-phone"></i></span>
        <input type="number" value="{{isset($record) ? $record->phone : ''}}" name="phone" id="phone" class="form-control phone-mask" placeholder="Enter phone" aria-label="658 799 8941" aria-describedby="phone2">
    </div>
</div>