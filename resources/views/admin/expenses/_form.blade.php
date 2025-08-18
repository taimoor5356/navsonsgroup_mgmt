<div class="row">
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label d-flex justify-content-between align-items-center" for="expense-type">
            <div>
                Expense Type
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="expense_type_id" id="expense_type_id" class="form-control">
                <option value="" selected disabled>Expense Type</option>
                @foreach (\App\Models\ExpenseType::get() as $expenseType)
                    <option value="{{$expenseType->id}}">{{strtoupper(str_replace('_', ' ', $expenseType->name))}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="name">Name</label>
        <div class="input-group input-group-merge">
            <span id="name2" class="input-group-text"><i class="bx bx-money"></i></span>
            <input type="text" value="{{isset($record) ? $record->name : ''}}" name="name" class="form-control" id="name" placeholder="Enter name" aria-label="name" aria-describedby="name2">
        </div>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="user_id">Select User</label>
        <select name="user_id" id="user-id" class="form-control">
            @foreach (\App\Models\User::where('user_type', 5)->get() as $user)
                <option value="" selected disabled>Select User</option>
                <option value="{{$user->id}}">{{$user->name}}</option>
            @endforeach
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
        <label class="form-label d-flex justify-content-between align-items-center" for="payment-mode">
            <div>
                Payment Mode
            </div>
        </label>
        <div class="input-group input-group-merge">
            <select name="payment_mode_id" id="payment_mode_id" class="form-control">
                <option value="" selected disabled>Payment Mode</option>
                @foreach (\App\Models\PaymentMode::get() as $paymentMode)
                    <option value="{{$paymentMode->id}}">{{strtoupper(str_replace('_', ' ', $paymentMode->name))}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="mb-3 col-md-4 col-12">
        <label class="form-label" for="date">Date</label>
        <div class="input-group input-group-merge">
            <input type="datetime-local" value="{{isset($record) ? $record->date : ''}}" name="date" class="form-control" id="date" placeholder="Enter date" aria-label="description" aria-describedby="description2">
        </div>
    </div>
</div>

@section('_scripts')
<script>

</script>
@endsection