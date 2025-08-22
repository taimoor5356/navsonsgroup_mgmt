<div class="row">
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="user_id">Select User</label>
        <select name="user_id" id="user-id" class="form-control">
            <option value="" selected disabled>Select User</option>
            @foreach (\App\Models\User::where('user_type', 5)->get() as $user)
                <option value="{{$user->id}}" @isset($record){{$user->id == $record->user_id ? 'selected' : ''}}@endisset>{{$user->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="vehicle_id">Select Vehicle</label>
        <select name="vehicle_id" id="user-id" class="form-control">
            <option value="" selected disabled>Select Vehicle</option>
            @if (!empty($vehicles))
            @foreach ($vehicles as $vehicle)
                <option value="{{$vehicle->vehicle_id}}" @isset($record){{$vehicle->vehicle_id == $record->vehicle_id ? 'selected' : ''}}@endisset>{{$vehicle->vehicle?->registration_number}} ({{$vehicle->vehicle?->name}})</option>
            @endforeach
            @endif
        </select>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="amount">Amount</label>
        <div class="input-group input-group-merge">
            <span id="amount2" class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="number" value="{{isset($record) ? $record->amount : ''}}" name="amount" class="form-control" id="amount" placeholder="Enter amount" aria-label="amount" aria-describedby="amount2">
        </div>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="description">Description</label>
        <div class="input-group input-group-merge">
            <span id="description2" class="input-group-text"><i class="bx bx-pencil"></i></span>
            <input type="text" value="{{isset($record) ? $record->description : ''}}" name="description" class="form-control" id="description" placeholder="Enter description" aria-label="description" aria-describedby="description2">
        </div>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="date">Date</label>
        <div class="input-group input-group-merge">
            <input type="datetime-local" value="{{isset($record) ? $record->created_at : ''}}" name="date" class="form-control" id="date" placeholder="Enter date" aria-label="description" aria-describedby="description2">
        </div>
    </div>
</div>

@section('_scripts')
<script>

</script>
@endsection