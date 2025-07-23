@if (!empty(session('success')))
    <div class="alert alert-success alert-message-badge" role="alert">
        {{session('success')}}
    </div>
@endif
@if (!empty(session('error')))
    <div class="alert alert-danger alert-message-badge" role="alert">
        {{session('error')}}
    </div>
@endif
@if (!empty(session('payment-error')))
    <div class="alert alert-danger alert-message-badge" role="alert">
        {{session('payment-error')}}
    </div>
@endif
@if (!empty(session('warning')))
    <div class="alert alert-warning alert-message-badge" role="alert">
        {{session('warning')}}
    </div>
@endif
@if (!empty(session('info')))
    <div class="alert alert-info alert-message-badge" role="alert">
        {{session('info')}}
    </div>
@endif
@if (!empty(session('secondary')))
    <div class="alert alert-secondary alert-message-badge" role="alert">
        {{session('secondary')}}
    </div>
@endif
@if (!empty(session('primary')))
    <div class="alert alert-primary alert-message-badge" role="alert">
        {{session('primary')}}
    </div>
@endif