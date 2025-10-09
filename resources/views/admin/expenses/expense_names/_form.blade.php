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
                    <option value="{{$expenseType->id}}" @isset($record){{$expenseType->id == $record->expense_type_id ? 'selected' : ''}}@endisset>{{strtoupper(str_replace('_', ' ', $expenseType->name))}}</option>
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
</div>

@section('_scripts')
<script>

</script>
@endsection