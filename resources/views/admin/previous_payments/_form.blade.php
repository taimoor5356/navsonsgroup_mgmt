<div class="mb-3">
    <label class="form-label" for="user_id">Select User</label>
    <div class="input-group input-group-merge">
        <select name="user_id" id="user_id" class="form-control">
            <option value="">Select User</option>
            @if(!empty($users))
            @foreach($users as $user)
            <option value="{{$user->id}}" {{isset($record) ? (($record->user_id == $user->id) ? 'selected' : '') : ''}}>{{$user->name}}</option>
            @endforeach
            @endif
        </select>
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="previous_amount">Enter Amount</label>
    <div class="input-group input-group-merge">
        <input type="number" value="{{isset($record) ? $record->previous_amount : ''}}" name="previous_amount" id="previous_amount" class="form-control" placeholder="Enter amount" aria-describedby="previous_amount">
    </div>
</div>
<div class="mb-3">
    <label class="form-label" for="date">Select Payment Date</label>
    <div class="input-group input-group-merge">
        <input type="date" name="date" id="date" class="form-control" aria-describedby="date" value="{{isset($record) ? $record->date : ''}}">
    </div>
</div>