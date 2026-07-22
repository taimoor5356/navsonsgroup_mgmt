@extends('layout.app')
@section('content')

<div class="container-fluid flex-grow-1 container-p-y">
    @include('_messages')
    <div class="row">
        <div class="col-12">
            <div class="breadcrumb-list">
                <h4 class="fw-bold py-3 mb-4"><span class="text-dark fw-light">{{$header_title}}</span></h4>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Service Rates by Car Category</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Service Type</th>
                            @foreach ($categories as $category)
                                <th style="width: 140px;">{{ $category->name }}</th>
                            @endforeach
                            @if (Auth::user()->hasRole(['admin', 'manager']))
                            <th style="width: 90px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($serviceTypes as $serviceType)
                        <tr class="rate-row" data-service-type-id="{{ $serviceType->id }}">
                            <td>{{ ucfirst($serviceType->name) }}</td>
                            @foreach ($categories as $category)
                                <td>
                                    <input type="number" step="0.01" min="0"
                                        class="form-control rate-input"
                                        data-vehicle-category-id="{{ $category->id }}"
                                        value="{{ $rates[$serviceType->id][$category->id]->price ?? 0 }}"
                                        @if (!Auth::user()->hasRole(['admin', 'manager'])) disabled @endif>
                                </td>
                            @endforeach
                            @if (Auth::user()->hasRole(['admin', 'manager']))
                            <td>
                                <button type="button" class="btn btn-primary btn-sm save-category-rates">Save</button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ count($categories) + 2 }}">No service types found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Add-on Rates (Diesel / Polish)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Add-on</th>
                            <th style="width: 220px;">Price</th>
                            @if (Auth::user()->hasRole(['admin', 'manager']))
                            <th style="width: 100px;">Action</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($addonRates as $addonRate)
                        <tr>
                            <td>{{ $addonRate->label }}</td>
                            <td>
                                <input type="number" step="0.01" min="0"
                                    class="form-control addon-rate-input"
                                    data-id="{{ $addonRate->id }}"
                                    value="{{ $addonRate->price }}"
                                    @if (!Auth::user()->hasRole(['admin', 'manager'])) disabled @endif>
                            </td>
                            @if (Auth::user()->hasRole(['admin', 'manager']))
                            <td>
                                <button type="button" class="btn btn-primary btn-sm save-addon-rate">Save</button>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3">No add-on rates found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@section('_scripts')
<script>
    $(document).ready(function () {
        $(document).on('click', '.save-category-rates', function () {
            var $btn = $(this);
            var $row = $btn.closest('tr');
            var prices = {};

            $row.find('.rate-input').each(function () {
                prices[$(this).data('vehicle-category-id')] = $(this).val();
            });

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('admin.services.types.update_price') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    model: 'service_category_rates',
                    service_type_id: $row.data('service-type-id'),
                    prices: prices
                },
                success: function () {
                    $btn.text('Saved');
                    setTimeout(function () {
                        $btn.prop('disabled', false).text('Save');
                    }, 1200);
                },
                error: function () {
                    $btn.prop('disabled', false).text('Save');
                    alert('Could not save these rates. Please check the values and try again.');
                }
            });
        });

        $(document).on('click', '.save-addon-rate', function () {
            var $btn = $(this);
            var $input = $btn.closest('tr').find('.addon-rate-input');

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: "{{ route('admin.services.types.update_price') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    model: 'addon',
                    id: $input.data('id'),
                    price: $input.val()
                },
                success: function () {
                    $btn.text('Saved');
                    setTimeout(function () {
                        $btn.prop('disabled', false).text('Save');
                    }, 1200);
                },
                error: function () {
                    $btn.prop('disabled', false).text('Save');
                    alert('Could not save the rate. Please check the value and try again.');
                }
            });
        });
    });
</script>
@endsection
