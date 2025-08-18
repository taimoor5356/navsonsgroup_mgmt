<div class="row">
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="user-name">User Name</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-user"></i></span>
            <input type="text" name="user_name" value="{{isset($record) ? $record->name : ''}}" id="user-name" class="form-control" placeholder="Enter User Name" aria-label="john.doe" aria-describedby="user-name2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="user-email">User Email</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-envelope"></i></span>
            <input type="email" name="user_email" value="{{isset($record) ? $record->email : ''}}" id="user-email" class="form-control" placeholder="Enter User Email" aria-label="test@test.com" aria-describedby="user-email2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="user-phone">User Phone</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-phone"></i></span>
            <input type="text" name="user_phone" value="{{isset($record) ? $record->phone : ''}}" id="user-phone" class="form-control" placeholder="Enter User Phone" aria-label="+923001234567" aria-describedby="user-phone2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="user-address">User Address</label>
        <div class="input-group input-group-merge">
            <span class="input-group-text"><i class="bx bx-trip"></i></span>
            <input type="text" name="user_address" value="{{isset($record) ? $record->address : ''}}" id="user-address" class="form-control" placeholder="Enter User Address" aria-label="ghouri town" aria-describedby="user-address2">
        </div>
    </div>
    <div class="mb-3 col-md-6 col-12">
        <label class="form-label" for="user-role">User Role</label>
        <select name="role_id" id="role-id" class="form-control">
            @if (!empty($roles))
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            @endif
        </select>
    </div>
</div>

@section('_scripts')
<script>
   $(document).ready(function() {
    
   }); 
</script>
@endsection